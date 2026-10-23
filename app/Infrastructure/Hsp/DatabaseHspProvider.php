<?php

declare(strict_types=1);

namespace App\Infrastructure\Hsp;

use App\Domain\Hsp\HspLookup;
use App\Domain\Hsp\HspProvider;
use App\Domain\Hsp\HspValue;
use App\Models\HspCatalogEntry;
use App\Models\HspCatalogPublication;
use App\Models\HspCatalogVersion;

final class DatabaseHspProvider implements HspProvider
{
    public function forState(string $stateCode): HspLookup
    {
        if (! preg_match('/^(0[1-9]|[12][0-9]|3[0-2])$/D', $stateCode)) {
            return new HspLookup(null, 'unknown_state');
        }
        $publication = HspCatalogPublication::find(1);
        $version = $publication === null ? null : HspCatalogVersion::find($publication->hsp_catalog_version_id);
        if ($version === null || $version->status !== 'published' || $version->published_at === null || $version->expires_at === null) {
            return new HspLookup(null, 'catalog_unavailable');
        }
        if ($version->expires_at->lessThanOrEqualTo(now())) {
            return new HspLookup(null, 'catalog_expired');
        }
        $entry = HspCatalogEntry::where('hsp_catalog_version_id', $version->id)->where('state_code', $stateCode)->first();
        if ($entry === null) {
            return new HspLookup(null, 'state_unavailable');
        }

        return new HspLookup(new HspValue(
            $entry->state_code, $entry->state_name, (float) $entry->annual_hsp,
            $entry->monthly_hsp, $version->reference, $version->methodology,
            $version->source_metadata, $version->published_at->toIso8601String(), $version->expires_at->toIso8601String(),
        ));
    }
}
