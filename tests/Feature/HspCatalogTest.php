<?php

namespace Tests\Feature;

use App\Application\RefreshHspCatalog;
use App\Domain\Hsp\ClimatologySource;
use App\Domain\Hsp\GridTile;
use App\Domain\Hsp\HspProvider;
use App\Domain\Hsp\InvalidHspData;
use App\Domain\Hsp\NasaClimatologyParser;
use App\Infrastructure\Hsp\StateGrid;
use App\Models\HspCatalogEntry;
use App\Models\HspCatalogPublication;
use App\Models\HspCatalogSource;
use App\Models\HspCatalogVersion;
use App\Models\HspRefreshAlert;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class HspCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->freezeTime();
        $this->fakeNasa();
    }

    private function fakeNasa(float $value = 5.0, ?callable $mutate = null): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(function (Request $request) use ($value, $mutate) {
            $data = json_decode(file_get_contents(base_path('tests/Fixtures/Hsp/nasa-region-2026-10-23.json')), true);
            $data['features'] = [];
            for ($x = (int) $request['longitude-min']; $x < (int) $request['longitude-max']; $x++) {
                for ($y = (int) $request['latitude-min']; $y < (int) $request['latitude-max']; $y++) {
                    $data['features'][] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [$x + 0.5, $y + 0.5, 0]],
                        'properties' => ['parameter' => ['ALLSKY_SFC_SW_DWN' => array_fill_keys(NasaClimatologyParser::PERIODS, $value)]],
                    ];
                }
            }

            return Http::response($mutate === null ? $data : $mutate($data));
        });
    }

    public function test_manifest_covers_all_states_and_conserves_area(): void
    {
        $grid = new StateGrid;
        $this->assertCount(32, $grid->states);
        $this->assertCount(23, $grid->tiles);
        $this->assertSame(array_map(fn (int $i): string => sprintf('%02d', $i), range(1, 32)), array_map(fn ($s) => $s->code, $grid->states));
        foreach ($grid->states as $state) {
            $this->assertEqualsWithDelta(1.0, array_sum($state->weights), 0.00000001);
        }
        $this->assertGreaterThan(1, count($grid->states[8]->weights));
    }

    public function test_publishes_32_entries_with_raw_evidence_and_snapshot(): void
    {
        $version = app(RefreshHspCatalog::class)();
        $this->assertSame('published', $version->status);
        $this->assertDatabaseCount('hsp_catalog_entries', 32);
        $this->assertDatabaseCount('hsp_catalog_sources', 23);
        $this->assertSame($version->id, HspCatalogPublication::findOrFail(1)->hsp_catalog_version_id);
        $this->assertSame('20010101', $version->source_metadata['start']);
        foreach (HspCatalogSource::all() as $source) {
            $this->assertSame(hash('sha256', $source->raw_response), $source->sha256);
        }
        $lookup = app(HspProvider::class)->forState('09');
        $this->assertNull($lookup->unavailableReason);
        $this->assertSame(5.0, $lookup->value->annual);
        $this->assertCount(12, $lookup->value->monthly);
        $this->assertSame($version->reference, $lookup->value->catalogVersion);
        $this->assertSame('h/day', $lookup->value->snapshot()['unit']);
        Http::assertSentCount(23);
    }

    public function test_lookup_is_local_and_never_queries_nasa(): void
    {
        app(RefreshHspCatalog::class)();
        Http::fake();
        $lookup = app(HspProvider::class)->forState('22');
        $this->assertNotNull($lookup->value);
        Http::assertNothingSent();
    }

    public function test_missing_and_unknown_state_never_supply_a_default(): void
    {
        $this->assertSame('catalog_unavailable', app(HspProvider::class)->forState('22')->unavailableReason);
        foreach (['00', '33', '9', 'Querétaro', ''] as $code) {
            $lookup = app(HspProvider::class)->forState($code);
            $this->assertSame('unknown_state', $lookup->unavailableReason);
            $this->assertNull($lookup->value);
        }
        Http::assertNothingSent();
    }

    public function test_failed_refresh_keeps_previous_catalog_and_expiration(): void
    {
        app(RefreshHspCatalog::class)();
        $before = app(HspProvider::class)->forState('22')->value->snapshot();
        $this->travel(31)->days();
        $this->mock(ClimatologySource::class, fn ($m) => $m->shouldReceive('fetch')->once()->andThrow(new RuntimeException('synthetic outage')));
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected source failure.');
        } catch (RuntimeException) {
            $this->assertSame($before, app(HspProvider::class)->forState('22')->value->snapshot());
        }
        $this->assertDatabaseCount('hsp_catalog_entries', 32);
        $this->assertDatabaseCount('hsp_refresh_alerts', 1);
        $this->assertSame('failed', HspCatalogVersion::latest('id')->first()->status);
        $this->artisan('helios:hsp:status', ['state' => '22'])->expectsOutputToContain('Último aviso interno')->assertSuccessful();
    }

    public function test_expiry_blocks_new_calculations_without_erasing_catalog(): void
    {
        app(RefreshHspCatalog::class)();
        $this->travel(90)->days();
        $lookup = app(HspProvider::class)->forState('22');
        $this->assertNull($lookup->value);
        $this->assertSame('catalog_expired', $lookup->unavailableReason);
        $this->assertDatabaseCount('hsp_catalog_entries', 32);
        $this->artisan('helios:hsp:status')->expectsOutputToContain('Catálogo vencido')->assertFailed();
    }

    public function test_new_version_does_not_rewrite_previous_values_or_snapshots(): void
    {
        $first = app(RefreshHspCatalog::class)();
        $snapshot = app(HspProvider::class)->forState('22')->value->snapshot();
        $this->fakeNasa(6.0);
        $this->travel(1)->day();
        $second = app(RefreshHspCatalog::class)();

        $this->assertNotSame($first->reference, $second->reference);
        $this->assertSame(5.0, (float) HspCatalogEntry::where('hsp_catalog_version_id', $first->id)->first()->annual_hsp);
        $this->assertSame(6.0, app(HspProvider::class)->forState('22')->value->annual);
        $this->assertSame(5.0, $snapshot['annual']);
        $this->assertDatabaseCount('hsp_catalog_entries', 64);
    }

    public function test_database_failure_cannot_publish_partial_entries(): void
    {
        $first = app(RefreshHspCatalog::class)();
        DB::statement("CREATE TRIGGER fail_last_hsp BEFORE INSERT ON hsp_catalog_entries WHEN NEW.state_code = '32' BEGIN SELECT RAISE(ABORT, 'synthetic failure'); END");
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected storage failure.');
        } catch (QueryException) {
            $this->assertSame($first->id, HspCatalogPublication::findOrFail(1)->hsp_catalog_version_id);
            $this->assertDatabaseCount('hsp_catalog_entries', 32);
            $this->assertDatabaseCount('hsp_refresh_alerts', 1);
        }
    }

    public function test_overlapping_refresh_does_not_start_another_update(): void
    {
        $lock = Cache::lock('hsp-catalog-refresh', 3600);
        $this->assertTrue($lock->get());
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected refresh lock.');
        } catch (RuntimeException $exception) {
            $this->assertSame('hsp_refresh_already_running', $exception->getMessage());
        } finally {
            $lock->release();
        }
        $this->assertDatabaseCount('hsp_catalog_versions', 0);
        Http::assertNothingSent();
    }

    public function test_invalid_refresh_policy_produces_an_internal_alert(): void
    {
        config(['hsp.max_age_days' => 0]);
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected invalid policy.');
        } catch (InvalidHspData) {
            $this->assertSame('invalid_refresh_policy', HspRefreshAlert::sole()->code);
        }
        $this->assertDatabaseCount('hsp_catalog_entries', 0);
        Http::assertNothingSent();
    }

    public function test_status_command_exposes_absence_instead_of_success(): void
    {
        $this->artisan('helios:hsp:status')->expectsOutputToContain('No hay catálogo publicado')->assertFailed();
    }

    public function test_missing_cell_does_not_publish_or_replace_previous_catalog(): void
    {
        $first = app(RefreshHspCatalog::class)();
        $missing = array_key_first((new StateGrid)->states[0]->weights);
        $this->fakeNasa(6.0, function (array $data) use ($missing): array {
            $data['features'] = array_values(array_filter($data['features'], function (array $feature) use ($missing): bool {
                [$x, $y] = $feature['geometry']['coordinates'];

                return $x.':'.$y !== $missing;
            }));

            return $data;
        });
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected incomplete coverage.');
        } catch (InvalidHspData) {
            $this->assertSame($first->id, HspCatalogPublication::findOrFail(1)->hsp_catalog_version_id);
            $this->assertDatabaseCount('hsp_catalog_entries', 32);
            $this->assertSame('missing_cell_or_invalid_weight', HspRefreshAlert::sole()->code);
        }
    }

    public function test_mixed_periods_are_rejected_before_publication(): void
    {
        $calls = 0;
        $this->fakeNasa(5.0, function (array $data) use (&$calls): array {
            if (++$calls === 2) {
                $data['header']['end'] = '20241231';
            }

            return $data;
        });
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected mixed climatology failure.');
        } catch (InvalidHspData) {
            $this->assertDatabaseCount('hsp_catalog_publications', 0);
            $this->assertDatabaseCount('hsp_catalog_entries', 0);
            $this->assertSame('nasa_inconsistent_period_or_source', HspRefreshAlert::sole()->code);
        }
    }

    public function test_nasa_rate_limit_is_retried_without_faking_a_value(): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        $raw = file_get_contents(base_path('tests/Fixtures/Hsp/nasa-region-2026-10-23.json'));
        Http::fakeSequence()->push('Rate limited', 429)->push($raw);
        $sample = app(ClimatologySource::class)->fetch(new GridTile(-101, 20));

        $this->assertSame(5.8886, $sample->cells['-100.5:20.5']['ANN']);
        Http::assertSentCount(2);
    }

    public function test_older_climatology_cannot_replace_current_catalog(): void
    {
        $first = app(RefreshHspCatalog::class)();
        $this->fakeNasa(4.0, function (array $data): array {
            $data['header']['end'] = '20241231';

            return $data;
        });
        try {
            app(RefreshHspCatalog::class)();
            $this->fail('Expected regressing climatology failure.');
        } catch (InvalidHspData) {
            $this->assertSame($first->id, HspCatalogPublication::findOrFail(1)->hsp_catalog_version_id);
            $this->assertSame('nasa_climatology_regressed', HspRefreshAlert::sole()->code);
        }
    }

    public function test_refresh_command_reports_failure_and_exposes_internal_alert(): void
    {
        config(['hsp.max_age_days' => 0]);
        $this->artisan('helios:hsp:refresh')->expectsOutputToContain('No se publicó')->assertFailed();
        $this->assertSame('invalid_refresh_policy', HspRefreshAlert::sole()->code);
    }
}
