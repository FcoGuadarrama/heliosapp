<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Hsp\AreaWeightedHsp;
use App\Domain\Hsp\ClimatologySource;
use App\Domain\Hsp\InvalidHspData;
use App\Infrastructure\Hsp\StateGrid;
use App\Models\HspCatalogEntry;
use App\Models\HspCatalogPublication;
use App\Models\HspCatalogSource;
use App\Models\HspCatalogVersion;
use App\Models\HspRefreshAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class RefreshHspCatalog
{
    public function __construct(private ClimatologySource $source, private AreaWeightedHsp $calculator) {}

    public function __invoke(): HspCatalogVersion
    {
        $lock = Cache::lock('hsp-catalog-refresh', 3600);
        if (! $lock->get()) {
            throw new RuntimeException('hsp_refresh_already_running');
        }
        try {
            $version = HspCatalogVersion::create(['reference' => (string) Str::uuid()]);
            try {
                return $this->build($version);
            } catch (Throwable $exception) {
                $code = $exception instanceof InvalidHspData ? $exception->getMessage() : 'hsp_source_or_storage_unavailable';
                $version->update(['status' => 'failed', 'error_code' => $code]);
                HspRefreshAlert::create(['hsp_catalog_version_id' => $version->id, 'code' => $code, 'created_at' => now()]);
                Log::warning('HSP catalog refresh failed.', ['reference' => $version->reference, 'code' => $code]);
                throw $exception;
            }
        } finally {
            $lock->release();
        }
    }

    private function build(HspCatalogVersion $version): HspCatalogVersion
    {
        $maxAge = (int) config('hsp.max_age_days');
        if ($maxAge < 1 || $maxAge > 365 || (int) config('hsp.http_timeout_seconds') > 60 || (int) config('hsp.http_timeout_seconds') < 1) {
            throw new InvalidHspData('invalid_refresh_policy');
        }
        $grid = new StateGrid;
        $version->update(['methodology' => $grid->methodology, 'sampling_sha256' => $grid->hash, 'sampling_manifest' => $grid->raw]);
        $cells = [];
        $metadata = null;
        foreach ($grid->tiles as $tile) {
            $sample = $this->source->fetch($tile);
            if ($metadata !== null && $metadata !== $sample->metadata) {
                throw new InvalidHspData('nasa_inconsistent_period_or_source');
            }
            $metadata = $sample->metadata;
            if (array_intersect_key($cells, $sample->cells) !== []) {
                throw new InvalidHspData('nasa_overlapping_tiles');
            }
            $cells += $sample->cells;
            HspCatalogSource::create([
                'hsp_catalog_version_id' => $version->id, 'tile' => $tile->key(),
                'query' => $tile->query(), 'raw_response' => $sample->raw,
                'sha256' => hash('sha256', $sample->raw), 'retrieved_at' => now(),
            ]);
        }
        if ($metadata === null) {
            throw new InvalidHspData('nasa_empty_catalog');
        }

        return DB::transaction(function () use ($grid, $cells, $metadata, $version, $maxAge): HspCatalogVersion {
            $current = HspCatalogPublication::find(1);
            if ($current !== null) {
                $previous = HspCatalogVersion::findOrFail($current->hsp_catalog_version_id);
                if ($metadata['end'] < $previous->source_metadata['end']) {
                    throw new InvalidHspData('nasa_climatology_regressed');
                }
            }
            foreach ($grid->states as $state) {
                $values = $this->calculator->calculate($state, $cells);
                $annual = $values['ANN'];
                unset($values['ANN']);
                HspCatalogEntry::create([
                    'hsp_catalog_version_id' => $version->id, 'state_code' => $state->code,
                    'state_name' => $state->name, 'annual_hsp' => $annual,
                    'monthly_hsp' => $values, 'weights' => $state->weights,
                ]);
            }
            $version->update([
                'status' => 'published', 'source_metadata' => [...$metadata, 'parameter' => 'ALLSKY_SFC_SW_DWN', 'community' => 'RE'],
                'published_at' => now(), 'expires_at' => now()->addDays($maxAge),
            ]);
            HspCatalogPublication::updateOrCreate(['id' => 1], ['hsp_catalog_version_id' => $version->id]);

            return $version->refresh();
        });
    }
}
