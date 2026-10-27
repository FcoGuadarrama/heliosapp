<?php

namespace Tests\Feature;

use App\Application\ProcessReceiptRun;
use App\Application\QueueReceiptProcessing;
use App\Application\ReadReceiptFile;
use App\Application\ReceiveReceipt;
use App\Application\ReviewReceiptReading;
use App\Application\StoreReceiptIntake;
use App\Domain\Hsp\HspLookup;
use App\Domain\Hsp\HspProvider;
use App\Domain\Hsp\HspValue;
use App\Domain\Intake\PublicFormToken;
use App\Domain\Processing\ProcessingStatus;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\ReceiptReading;
use App\Http\Resources\ReceiptAnalysis;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\ReceiptReview;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AutomaticReceiptAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('receipts');
        config(['receipt_reader.enabled' => true, 'queue.default' => 'database',
            'queue.connections.database.retry_after' => 960,
            'intake.enabled' => true, 'intake.privacy_version' => 'test', 'intake.privacy_text' => 'Pruebas']);
        $this->withoutVite();
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->assignRole('owner');

        return $actor;
    }

    private function file(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('synthetic.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-readable.pdf')));
    }

    private function intake(): ReceiptIntake
    {
        return app(StoreReceiptIntake::class)(['name' => 'Synthetic prospect', 'email' => 'synthetic@example.test'], $this->file(), null, null);
    }

    private function reader(bool $verified = true, ?array $history = null): void
    {
        $payload = json_decode(file_get_contents(resource_path('fixtures/receipts/complete.json')), true);
        if ($history !== null) {
            $payload['consumption_history'] = $history;
        }
        foreach (['service.tariff_code', 'current_period.period_start', 'current_period.period_end'] as $field) {
            $payload['extraction']['evidence'][] = ['field' => $field, 'page' => 1, 'text' => data_get($payload, $field)];
        }
        $json = json_encode($payload);
        $this->mock(FileReceiptReader::class)->shouldReceive('read')->once()->andReturn(new ReceiptReading($json, $json, 'test', 1, 'test', $verified));
    }

    private function hsp(): void
    {
        $value = new HspValue('22', 'Querétaro', 5.0, [], 'test-catalog', 'test-method',
            ['provider' => 'NASA'], now()->toIso8601String(), now()->addDays(30)->toIso8601String());
        $this->mock(HspProvider::class)->shouldReceive('forState')->with('22')->andReturn(new HspLookup($value));
    }

    public function test_crm_upload_queues_real_analysis_without_running_in_http_request(): void
    {
        $this->actingAs($this->actor())->post('/intakes', ['name' => 'Synthetic prospect', 'email' => 'synthetic@example.test',
            'privacy_consent' => 1, 'privacy_version' => 'test', 'receipt' => $this->file()])->assertRedirect();

        $this->assertSame(ProcessingStatus::Queued, SolarCase::sole()->processing_status);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('receipt_readings', 0);
    }

    public function test_synchronous_queue_is_rejected_without_losing_the_uploaded_file(): void
    {
        config(['queue.default' => 'sync']);
        $intake = app(ReceiveReceipt::class)(['name' => 'Synthetic'], $this->file(), null, null);

        $this->assertSame('queue_configuration_invalid', SolarCase::sole()->blocker_code);
        $this->assertDatabaseCount('receipt_readings', 0);
        Storage::disk('receipts')->assertExists($intake->file_path);
    }

    public function test_queue_outage_preserves_file_and_exposes_recoverable_failure(): void
    {
        $dispatcher = Bus::getFacadeRoot();
        Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('synthetic private transport error'));
        try {
            $intake = app(ReceiveReceipt::class)(['name' => 'Synthetic'], $this->file(), null, null);
        } finally {
            Bus::swap($dispatcher);
        }

        $this->assertSame('queue_dispatch_failed', SolarCase::sole()->blocker_code);
        $this->assertSame(ProcessingStatus::Failed, ReceiptProcessingRun::sole()->status);
        Storage::disk('receipts')->assertExists($intake->file_path);
    }

    public function test_unverified_model_data_requires_review_and_never_creates_a_calculation(): void
    {
        $this->reader(false);
        $run = app(QueueReceiptProcessing::class)($this->intake());
        app(ProcessReceiptRun::class)($run->id);

        $this->assertSame('receipt_needs_review', SolarCase::sole()->blocker_code);
        $this->assertDatabaseCount('solar_calculations', 0);
    }

    public function test_verified_reading_blocks_until_supply_state_is_confirmed(): void
    {
        $this->reader();
        $run = app(QueueReceiptProcessing::class)($this->intake());
        app(ProcessReceiptRun::class)($run->id);

        $this->assertSame('supply_state_required', SolarCase::sole()->blocker_code);
        $this->assertDatabaseCount('solar_calculations', 0);
    }

    public function test_confirmed_reading_calculates_with_hsp_snapshot_and_no_assumed_panel_power(): void
    {
        $this->reader();
        $this->hsp();
        $intake = $this->intake();
        $reading = app(ReadReceiptFile::class)($intake);
        $actor = $this->actor();
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Datos cotejados contra el recibo original.', 0);
        $run = app(QueueReceiptProcessing::class)($intake);
        app(ProcessReceiptRun::class)($run->id);

        $calculation = SolarCalculation::sole();
        $this->assertSame(ProcessingStatus::Completed, SolarCase::sole()->processing_status);
        $this->assertNull($calculation->result['moduleCount']);
        $this->assertNull($calculation->inputs['module_power_w']);
        $this->assertSame('test-catalog', $calculation->inputs['hsp']['catalogVersion']);
        $this->assertEqualsWithDelta((1450 / 180) / (0.77 * 5), $calculation->result['requiredKwp'], 0.000001);
        $this->assertSame('verified_periods', $calculation->result['consumptionSource']);
        $this->assertSame(180, $calculation->inputs['annual_selection']['actual_days']);
        $this->assertSame('all-unique-verifiable-periods-v2', $calculation->inputs['history_policy']);
        $this->assertEquals(1450, $calculation->inputs['consumption_average']['total_kwh']);
        $this->assertSame(180, $calculation->inputs['consumption_average']['covered_days']);
        $this->assertSame('draft', SolarCase::sole()->publication_status);
        app(ProcessReceiptRun::class)($run->id);
        $this->assertDatabaseCount('solar_calculations', 1);
    }

    public function test_all_eleven_historic_periods_reach_the_calculation_without_being_divided_by_365(): void
    {
        $end = CarbonImmutable::parse('2026-07-15');
        $history = [];
        for ($index = 0; $index < 11; $index++) {
            $start = $end->subDays(60);
            $history[] = ['period_start' => $start->toDateString(), 'period_end' => $end->toDateString(), 'consumption_kwh' => 600];
            $end = $start;
        }
        $this->reader(history: $history);
        $this->hsp();
        $intake = $this->intake();
        $reading = app(ReadReceiptFile::class)($intake);
        app(ReviewReceiptReading::class)($reading, $this->actor(), $reading->payload_json, 'Datos cotejados contra el recibo original.', 0);
        $run = app(QueueReceiptProcessing::class)($intake);
        app(ProcessReceiptRun::class)($run->id);

        $calculation = SolarCalculation::sole();
        $this->assertSame(ProcessingStatus::Completed, SolarCase::sole()->processing_status);
        $this->assertSame(720, $calculation->inputs['annual_selection']['actual_days']);
        $this->assertCount(12, $calculation->inputs['annual_selection']['kwh']);
        $this->assertEqualsWithDelta(7120 / 720, $calculation->result['dailyConsumptionKwh'], 0.000000001);
        $this->assertEqualsWithDelta((7120 / 720) / (0.77 * 5), $calculation->result['requiredKwp'], 0.000000001);
    }

    public function test_analysis_json_is_authorized_and_does_not_expose_raw_response_or_private_path(): void
    {
        $this->reader();
        $intake = $this->intake();
        app(ReadReceiptFile::class)($intake);
        $this->getJson('/intakes/'.$intake->id.'/analysis')->assertUnauthorized();
        $response = $this->actingAs($this->actor())->getJson('/intakes/'.$intake->id.'/analysis')->assertOk();

        $this->assertStringNotContainsString($intake->file_path, $response->getContent());
        $this->assertStringNotContainsString('raw_response', $response->getContent());
        $this->assertSame(0, $response->json('revision'));
    }

    public function test_review_endpoint_records_acceptance_and_resumes_processing(): void
    {
        $this->reader(false);
        $intake = $this->intake();
        $run = app(QueueReceiptProcessing::class)($intake);
        app(ProcessReceiptRun::class)($run->id);
        $actor = $this->actor();
        $form = app(ReceiptAnalysis::class)->forAdvisor($intake, $actor)['form'];
        $this->actingAs($actor)->from('/clients/'.$intake->client_id)->post('/intakes/'.$intake->id.'/review', [
            ...$form, 'state_code' => '22', 'reason' => 'Datos cotejados con el recibo original.', 'revision' => 0, 'confirmed' => true,
        ])->assertRedirect('/clients/'.$intake->client_id);

        $this->assertDatabaseCount('receipt_reviews', 1);
        $this->assertSame('state_confirmed', SolarCase::sole()->location_status);
        $this->assertSame(ProcessingStatus::Queued, $run->fresh()->status);
        $this->assertSame(1, ReceiptReview::sole()->revision);
    }

    public function test_inactive_operator_cannot_start_analysis(): void
    {
        $intake = $this->intake();
        $actor = $this->actor();
        $actor->update(['is_active' => false]);
        $this->actingAs($actor)->post('/intakes/'.$intake->id.'/analyze')->assertForbidden();
        $this->assertDatabaseCount('receipt_processing_runs', 0);
    }

    public function test_real_analysis_cannot_be_replaced_with_a_synthetic_fixture(): void
    {
        $intake = $this->intake();
        app(QueueReceiptProcessing::class)($intake);
        $this->actingAs($this->actor())->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => 'complete'])
            ->assertSessionHasErrors('fixture_identifier');
        $this->assertNull($intake->fresh()->raw_payload);
        $this->assertNull($intake->fresh()->fixture_identifier);
    }

    public function test_public_upload_queues_without_using_the_authenticated_crm_identity(): void
    {
        $token = app(PublicFormToken::class)->issue();
        $this->travel(3)->seconds();
        $this->actingAs($this->actor())->post('/embed/cfe', ['form_token' => $token,
            'name' => 'Synthetic public lead', 'email' => 'public@example.test', 'privacy_consent' => 1,
            'privacy_version' => 'test', 'receipt' => $this->file()])->assertStatus(303);
        $this->assertNull(ReceiptIntake::sole()->created_by);
        $this->assertSame('embed', ReceiptIntake::sole()->source);
        $this->assertSame(ProcessingStatus::Queued, SolarCase::sole()->processing_status);
        $this->assertDatabaseCount('jobs', 1);
    }
}
