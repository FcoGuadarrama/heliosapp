<?php

namespace Tests\Feature;

use App\Application\ReadReceiptFile;
use App\Application\ReviewReceiptReading;
use App\Application\StoreReceiptIntake;
use App\Application\StoreSecondReceipt;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\ReceiptReading as Reading;
use App\Infrastructure\Receipts\SupplyStateResolver;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\SolarCase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReceiptReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('receipts');
        config(['intake.privacy_version' => 'test', 'intake.privacy_text' => 'Test']);
    }

    private function actor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        return $user->refresh();
    }

    private function file(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('test.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6T+kAAAAASUVORK5CYII='));
    }

    private function reading(string $status = 'complete'): ReceiptReading
    {
        $payload = json_decode(file_get_contents(resource_path('fixtures/receipts/complete.json')), true);
        $payload['extraction']['status'] = $status;
        $json = json_encode($payload);
        $this->mock(FileReceiptReader::class)->shouldReceive('read')->once()->andReturn(
            new Reading($json, $json, 'synthetic', 1, 'test'));
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'], $this->file(), null, null);

        return app(ReadReceiptFile::class)($intake);
    }

    public function test_human_acceptance_preserves_original_and_records_separate_normalization(): void
    {
        $reading = $this->reading();
        $original = $reading->payload_json;
        $payload = json_decode($original, true);
        $payload['current_period']['consumption_kwh'] = 600;
        $review = app(ReviewReceiptReading::class)($reading, $this->actor(), json_encode($payload), 'Consumo cotejado con el documento original.', 0);

        $this->assertSame(1, $review->revision);
        $this->assertSame(60, $review->normalization['current_period_days']);
        $this->assertSame(10, $review->normalization['current_daily_kwh']);
        $this->assertSame(120, $review->normalization['history_actual_days']);
        $this->assertSame('22', $review->normalization['supply_state_code']);
        $this->assertFalse($review->normalization['location_confirmed']);
        $this->assertSame($original, $reading->fresh()->payload_json);
        $this->assertSame($original, ReceiptIntake::sole()->raw_payload);
        $this->assertSame('accepted', ReceiptIntake::sole()->status);
        $this->assertArrayNotHasKey('payload_json', $review->toArray());
        $this->assertSame('draft', SolarCase::sole()->publication_status);
    }

    public function test_revisions_are_append_only_and_stale_edits_are_rejected(): void
    {
        $reading = $this->reading();
        $actor = $this->actor();
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Primera comprobación del documento.', 0);
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Segunda comprobación del documento.', 1);
        $this->assertDatabaseCount('receipt_reviews', 2);
        $this->expectException(ValidationException::class);
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Una edición desactualizada.', 0);
    }

    public function test_user_without_permissions_cannot_accept(): void
    {
        $reading = $this->reading();
        $this->expectException(AuthorizationException::class);
        app(ReviewReceiptReading::class)($reading, User::factory()->create(), $reading->payload_json, 'Documento comprobado manualmente.', 0);
    }

    public function test_inactive_actor_cannot_accept(): void
    {
        $reading = $this->reading();
        $actor = $this->actor();
        $actor->update(['is_active' => false]);
        $this->expectException(AuthorizationException::class);
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Documento comprobado manualmente.', 0);
    }

    public function test_missing_critical_data_cannot_be_accepted_by_overriding_warnings(): void
    {
        $reading = $this->reading();
        $payload = json_decode($reading->payload_json, true);
        $payload['current_period']['period_start'] = null;
        $this->expectException(ValidationException::class);
        app(ReviewReceiptReading::class)($reading, $this->actor(), json_encode($payload), 'Documento comprobado manualmente.', 0);
    }

    public function test_review_cannot_replace_document_provenance(): void
    {
        $reading = $this->reading();
        $payload = json_decode($reading->payload_json, true);
        $payload['document']['source_reference'] = 'another-document';
        $this->expectException(ValidationException::class);
        app(ReviewReceiptReading::class)($reading, $this->actor(), json_encode($payload), 'Documento comprobado manualmente.', 0);
    }

    public function test_duplicate_history_is_not_silently_counted_twice(): void
    {
        $reading = $this->reading();
        $payload = json_decode($reading->payload_json, true);
        $payload['consumption_history'][] = $payload['consumption_history'][0];
        $this->expectException(ValidationException::class);
        app(ReviewReceiptReading::class)($reading, $this->actor(), json_encode($payload), 'Documento comprobado manualmente.', 0);
    }

    public function test_second_upload_preserves_case_lead_and_original_and_replays_identically(): void
    {
        $this->reading('failed');
        $first = ReceiptIntake::sole();
        $actor = $this->actor();
        $second = app(StoreSecondReceipt::class)($first, $this->file(), $actor);
        $again = app(StoreSecondReceipt::class)($first, $this->file(), $actor);

        $this->assertSame($second->id, $again->id);
        $this->assertSame($first->solar_case_id, $second->solar_case_id);
        $this->assertSame($first->client_id, $second->client_id);
        $this->assertSame('unreadable', $first->fresh()->status);
        $this->assertSame('stored', $second->status);
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('solar_cases', 1);
        $this->assertCount(2, Storage::disk('receipts')->allFiles());
    }

    public function test_technical_failure_does_not_authorize_or_spend_second_upload(): void
    {
        $first = app(StoreReceiptIntake::class)(['name' => 'Synthetic'], $this->file(), null, null);
        try {
            app(StoreSecondReceipt::class)($first, $this->file(), $this->actor());
            $this->fail('Expected replacement rejection.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('receipt_intakes', 1);
            $this->assertDatabaseCount('receipt_readings', 0);
            $this->assertCount(1, Storage::disk('receipts')->allFiles());
        }
    }

    public function test_third_upload_is_denied_even_after_two_unreadable_documents(): void
    {
        $this->reading('failed');
        $first = ReceiptIntake::sole();
        $actor = $this->actor();
        $second = app(StoreSecondReceipt::class)($first, $this->file(), $actor);
        $second->update(['status' => 'unreadable']);
        $sample = $this->file();
        $different = UploadedFile::fake()->createWithContent('other.png', file_get_contents($sample->getRealPath()).'different');
        $this->expectException(ValidationException::class);
        app(StoreSecondReceipt::class)($first, $different, $actor);
    }

    public function test_superseded_receipt_cannot_be_accepted(): void
    {
        $reading = $this->reading('failed');
        $actor = $this->actor();
        app(StoreSecondReceipt::class)(ReceiptIntake::sole(), $this->file(), $actor);
        $this->expectException(ValidationException::class);
        app(ReviewReceiptReading::class)($reading, $actor, $reading->payload_json, 'Documento comprobado manualmente.', 0);
    }

    public function test_state_resolution_uses_only_exact_names_without_guessing(): void
    {
        $resolver = app(SupplyStateResolver::class);
        $this->assertSame('22', $resolver->code('  QUERETARO '));
        $this->assertNull($resolver->code('Qro.'));
        $this->assertNull($resolver->code(null));
    }
}
