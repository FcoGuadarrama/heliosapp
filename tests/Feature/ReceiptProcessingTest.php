<?php

namespace Tests\Feature;

use App\Application\ProcessReceiptRun;
use App\Application\QueueReceiptProcessing;
use App\Application\RegisterReceiptCase;
use App\Application\RetryReceiptProcessing;
use App\Application\StoreReceiptIntake;
use App\Domain\Processing\ProcessingOutcome;
use App\Domain\Processing\ProcessingStatus;
use App\Domain\Processing\ReceiptProcessor;
use App\Http\Resources\SolarCaseStatus;
use App\Jobs\ProcessReceipt;
use App\Models\Client;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ReceiptProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('receipts');
        config(['queue.default' => 'database', 'intake.privacy_version' => 'test-v1', 'intake.privacy_text' => 'Aviso de prueba.']);
    }

    private function actor(string $role = 'sales'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->refresh();
    }

    private function intake(?User $actor = null): ReceiptIntake
    {
        $client = $actor === null ? null : Client::factory()->create();

        return app(StoreReceiptIntake::class)(
            ['name' => 'Synthetic prospect', 'email' => 'synthetic@example.test'],
            UploadedFile::fake()->create('receipt.pdf', 1, 'application/pdf'),
            $client,
            $actor,
        );
    }

    public function test_upload_creates_case_and_audit_without_claiming_processing(): void
    {
        $intake = $this->intake();
        $case = SolarCase::sole();

        $this->assertSame($case->id, $intake->solar_case_id);
        $this->assertSame($intake->client_id, $case->client_id);
        $this->assertNull($case->responsible_user_id);
        $this->assertSame(ProcessingStatus::Received, $case->processing_status);
        $this->assertSame('stored', $intake->status);
        $this->assertSame('receipt_received', $case->events()->sole()->event);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_registering_same_receipt_keeps_responsible_and_audit(): void
    {
        $actor = $this->actor();
        $intake = $this->intake($actor);
        app(RegisterReceiptCase::class)($intake, null);

        $this->assertDatabaseCount('solar_cases', 1);
        $this->assertDatabaseCount('solar_case_events', 1);
        $this->assertSame($actor->id, SolarCase::sole()->responsible_user_id);
    }

    public function test_run_is_enqueued_once_after_transaction_commit(): void
    {
        $intake = $this->intake();
        DB::beginTransaction();
        $run = app(QueueReceiptProcessing::class)($intake);
        $this->assertDatabaseCount('jobs', 0);
        DB::commit();
        $again = app(QueueReceiptProcessing::class)($intake);

        $this->assertSame($run->id, $again->id);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('receipt_processing_runs', 1);
        $this->assertSame(ProcessingStatus::Queued, SolarCase::sole()->processing_status);
        $this->assertSame(0, $run->fresh()->attempts);
    }

    public function test_rollback_removes_run_and_does_not_enqueue(): void
    {
        $intake = $this->intake();
        DB::beginTransaction();
        app(QueueReceiptProcessing::class)($intake);
        DB::rollBack();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('receipt_processing_runs', 0);
        $this->assertSame(ProcessingStatus::Received, SolarCase::sole()->processing_status);
    }

    public function test_failed_enqueue_can_be_recovered_without_creating_a_second_run(): void
    {
        $intake = $this->intake();
        $dispatcher = Bus::getFacadeRoot();
        Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('synthetic queue outage'));
        try {
            app(QueueReceiptProcessing::class)($intake);
            $this->fail('Expected queue failure.');
        } catch (RuntimeException) {
            $this->assertNull(ReceiptProcessingRun::sole()->dispatched_at);
        } finally {
            Bus::swap($dispatcher);
        }
        app(QueueReceiptProcessing::class)($intake);

        $this->assertDatabaseCount('receipt_processing_runs', 1);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertNotNull(ReceiptProcessingRun::sole()->dispatched_at);
    }

    public function test_missing_reader_blocks_without_altering_import_or_generating_results(): void
    {
        $intake = $this->intake($this->actor());
        $run = app(QueueReceiptProcessing::class)($intake);
        app(ProcessReceiptRun::class)($run->id);
        app(ProcessReceiptRun::class)($run->id);

        $case = SolarCase::sole();
        $this->assertSame(ProcessingStatus::Blocked, $case->processing_status);
        $this->assertSame('receipt_reader_unavailable', $case->blocker_code);
        $this->assertNotNull($case->attention_required_at);
        $this->assertSame(1, $run->fresh()->attempts);
        $this->assertSame('stored', $intake->fresh()->status);
        $this->assertNull($intake->fresh()->raw_payload);
        $this->assertSame('pending', $case->calculation_status);
        $this->assertSame('draft', $case->publication_status);
        $this->assertSame('not_requested', $case->delivery_status);
        $this->assertDatabaseCount('receipt_intakes', 1);
    }

    public function test_synthetic_processor_is_explicit_and_does_not_publish(): void
    {
        $this->mock(ReceiptProcessor::class, function ($mock): void {
            $mock->shouldReceive('process')->once()->andReturn(new ProcessingOutcome(ProcessingStatus::Completed, simulated: true));
        });
        $run = app(QueueReceiptProcessing::class)($this->intake());
        app(ProcessReceiptRun::class)($run->id);

        $case = SolarCase::sole();
        $this->assertSame(ProcessingStatus::Completed, $case->processing_status);
        $this->assertTrue($case->simulated);
        $this->assertSame('draft', $case->publication_status);
        $this->assertNull(app(SolarCaseStatus::class)->customer($case)['result']);
    }

    public function test_technical_failure_retries_without_spending_uploads_or_storing_exception_details(): void
    {
        $this->mock(ReceiptProcessor::class, function ($mock): void {
            $mock->shouldReceive('process')->once()->andThrow(new RuntimeException('synthetic private exception'));
        });
        $run = app(QueueReceiptProcessing::class)($this->intake());
        try {
            app(ProcessReceiptRun::class)($run->id);
            $this->fail('Expected processing exception.');
        } catch (RuntimeException) {
            $this->assertSame(ProcessingStatus::Queued, $run->fresh()->status);
            $this->assertSame('processing_error', $run->fresh()->error_code);
        }
        $job = new ProcessReceipt($run->id);
        $job->failed(new RuntimeException('synthetic private exception'));
        $job->failed(null);

        $this->assertSame(ProcessingStatus::Failed, $run->fresh()->status);
        $this->assertDatabaseCount('receipt_intakes', 1);
        $this->assertSame(1, SolarCase::sole()->events()->where('event', 'processing_failed')->count());
        $this->assertStringNotContainsString('synthetic private exception', SolarCase::sole()->events()->get()->toJson());
    }

    public function test_authorized_retry_reuses_run_and_receipt(): void
    {
        $actor = $this->actor();
        $run = app(QueueReceiptProcessing::class)($this->intake($actor));
        app(ProcessReceiptRun::class)($run->id);
        app(RetryReceiptProcessing::class)($run->fresh(), $actor);
        app(ProcessReceiptRun::class)($run->id);

        $this->assertSame(2, $run->fresh()->attempts);
        $this->assertDatabaseCount('receipt_intakes', 1);
        $this->assertDatabaseCount('solar_cases', 1);
        $this->assertDatabaseCount('receipt_processing_runs', 1);
        $this->assertSame(1, SolarCase::sole()->events()->where('event', 'processing_retried')->count());
    }

    public function test_viewer_cannot_request_retry(): void
    {
        $run = app(QueueReceiptProcessing::class)($this->intake());
        app(ProcessReceiptRun::class)($run->id);
        $this->expectException(AuthorizationException::class);
        app(RetryReceiptProcessing::class)($run, $this->actor('viewer'));
    }

    public function test_retry_cannot_be_requested_twice_while_queued(): void
    {
        $actor = $this->actor();
        $run = app(QueueReceiptProcessing::class)($this->intake($actor));
        app(ProcessReceiptRun::class)($run->id);
        app(RetryReceiptProcessing::class)($run, $actor);
        $this->expectException(ValidationException::class);
        app(RetryReceiptProcessing::class)($run, $actor);
    }

    public function test_queue_middleware_prevents_overlapping_execution(): void
    {
        $run = app(QueueReceiptProcessing::class)($this->intake());
        $job = new ProcessReceipt($run->id);
        $middleware = $job->middleware()[0];
        $lock = Cache::lock($middleware->getLockKey($job), 120);
        $this->assertTrue($lock->get());
        try {
            $middleware->handle($job, fn () => $job->handle(app(ProcessReceiptRun::class)));
            $this->assertSame(0, $run->fresh()->attempts);
        } finally {
            $lock->release();
        }
        $middleware->handle($job, fn () => $job->handle(app(ProcessReceiptRun::class)));
        $this->assertSame(1, $run->fresh()->attempts);
    }

    public function test_public_projection_never_contains_contact_path_blocker_or_purchase_cost(): void
    {
        $intake = $this->intake();
        $case = SolarCase::sole();
        $case->setAttribute('purchase_cost', 123456);
        $case->setAttribute('raw_payload', 'synthetic private payload');
        $data = app(SolarCaseStatus::class)->customer($case);

        $this->assertSame($case->public_id, $data['reference']);
        foreach (['purchase_cost', 'client_id', 'file_path', 'raw_payload', 'blocker_code', 'responsible_user_id'] as $field) {
            $this->assertArrayNotHasKey($field, $data);
        }
        $this->assertStringNotContainsString($intake->file_path, json_encode($data, JSON_THROW_ON_ERROR));
        $this->assertSame('unavailable', $data['sale_price']['status']);
        $this->assertNull($data['sale_price']['amount_minor']);
    }

    public function test_advisor_projection_requires_active_operator_permissions(): void
    {
        $this->intake();
        $case = SolarCase::sole();
        $actor = $this->actor();
        $data = app(SolarCaseStatus::class)->advisor($case, $actor);
        $this->assertSame('unavailable', $data['purchase_cost']['status']);
        $actor->update(['is_active' => false]);
        $this->expectException(AuthorizationException::class);
        app(SolarCaseStatus::class)->advisor($case, $actor);
    }

    public function test_deleted_prospect_blocks_processing_and_access(): void
    {
        $actor = $this->actor();
        $intake = $this->intake($actor);
        $run = app(QueueReceiptProcessing::class)($intake);
        $intake->client->delete();
        app(ProcessReceiptRun::class)($run->id);

        $case = SolarCase::sole();
        $this->assertSame('prospect_unavailable', $case->blocker_code);
        $this->assertFalse($actor->can('view', $case));
        $this->assertFalse($actor->can('process', $case));
    }

    public function test_database_worker_processes_committed_job_and_removes_queue_entry(): void
    {
        app(QueueReceiptProcessing::class)($this->intake());
        $this->assertDatabaseCount('jobs', 1);
        Artisan::call('queue:work', ['--once' => true, '--queue' => 'receipts', '--sleep' => 0, '--tries' => 3]);

        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(ProcessingStatus::Blocked, ReceiptProcessingRun::sole()->status);
        $this->assertSame('receipt_reader_unavailable', SolarCase::sole()->blocker_code);
    }

    public function test_audit_failure_rolls_back_intake_and_removes_private_file(): void
    {
        DB::statement("CREATE TRIGGER fail_case_event BEFORE INSERT ON solar_case_events BEGIN SELECT RAISE(ABORT, 'synthetic failure'); END");
        try {
            $this->intake();
            $this->fail('Expected isolated audit failure.');
        } catch (QueryException) {
            $this->assertDatabaseCount('clients', 0);
            $this->assertDatabaseCount('receipt_intakes', 0);
            $this->assertDatabaseCount('solar_cases', 0);
            $this->assertCount(0, Storage::disk('receipts')->allFiles());
        }
    }

    public function test_migration_roundtrip_preserves_legacy_receipts_without_automatic_backfill(): void
    {
        $intake = $this->intake();
        $path = $intake->file_path;
        $migration = require database_path('migrations/2026_10_23_000001_create_solar_cases.php');
        $migration->down();
        $migration->up();

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('receipt_intakes', 1);
        $this->assertDatabaseCount('solar_cases', 0);
        $this->assertNull($intake->fresh()->solar_case_id);
        $this->assertSame('stored', $intake->fresh()->status);
        Storage::disk('receipts')->assertExists($path);
    }
}
