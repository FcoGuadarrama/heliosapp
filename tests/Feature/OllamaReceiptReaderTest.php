<?php

namespace Tests\Feature;

use App\Application\ReadReceiptFile;
use App\Application\StoreReceiptIntake;
use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\ReadingAssessment;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use App\Infrastructure\Receipts\ReceiptImages;
use App\Infrastructure\Receipts\ReceiptText;
use App\Models\SolarCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OllamaReceiptReaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('receipts');
        Http::preventStrayRequests();
        config(['receipt_reader.enabled' => true, 'receipt_reader.model' => 'qwen3-vl:8b',
            'intake.privacy_version' => 'test', 'intake.privacy_text' => 'Test']);
    }

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6T+kAAAAASUVORK5CYII=');
    }

    private function source(): ReceiptSource
    {
        Storage::disk('receipts')->put('test.png', $this->png());

        return new ReceiptSource(42, 'test.png', 'image/png');
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return json_decode(file_get_contents(resource_path('fixtures/receipts/complete.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @param array<string, mixed>|null $payload */
    private function reply(?array $payload = null, string $reason = 'stop'): void
    {
        Http::fake(['*/api/chat' => Http::response(['model' => 'qwen3-vl:8b', 'done' => true,
            'done_reason' => $reason, 'message' => ['content' => json_encode($payload ?? $this->payload())]])]);
    }

    public function test_real_reader_attaches_image_bytes_and_sets_trusted_provenance(): void
    {
        $this->reply();
        $reading = app(FileReceiptReader::class)->read($this->source());
        $payload = json_decode($reading->payloadJson, true);

        $this->assertSame('receipt-intake:42', $payload['document']['source_reference']);
        $this->assertSame('image', $payload['document']['source_type']);
        $this->assertSame('ollama', $payload['document']['extractor']['identifier']);
        $this->assertSame('0000123456789', $payload['receipt']['receipt_id']);
        $this->assertStringContainsString('mock-fixture', $reading->rawResponse);
        Http::assertSent(fn (Request $request): bool => $request['stream'] === false
            && $request['messages'][1]['images'] === [base64_encode($this->png())]
            && ! str_contains(json_encode($request->data()), 'test.png'));
    }

    public function test_reader_disabled_does_not_send_any_data(): void
    {
        config(['receipt_reader.enabled' => false]);
        try {
            app(FileReceiptReader::class)->read($this->source());
            $this->fail('Expected disabled reader.');
        } catch (ReceiptReadingFailure $failure) {
            $this->assertSame('reader_disabled', $failure->reason);
            Http::assertNothingSent();
        }
    }

    public function test_service_errors_never_expose_response_content(): void
    {
        Http::fake(['*' => Http::response('private provider response', 500)]);
        try {
            app(FileReceiptReader::class)->read($this->source());
            $this->fail('Expected service failure.');
        } catch (ReceiptReadingFailure $failure) {
            $this->assertSame('reader_service_error', $failure->reason);
            $this->assertStringNotContainsString('private', $failure->getMessage());
        }
    }

    public function test_connection_error_is_technical_and_does_not_create_readings(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->expectExceptionMessage('reader_unreachable');
        app(FileReceiptReader::class)->read($this->source());
    }

    public function test_truncated_generation_is_not_an_unreadable_receipt(): void
    {
        $this->reply(reason: 'length');
        $this->expectExceptionMessage('reader_incomplete_response');
        app(FileReceiptReader::class)->read($this->source());
    }

    public function test_malformed_model_json_is_technical_failure(): void
    {
        Http::fake(['*' => Http::response(['done' => true, 'done_reason' => 'stop', 'message' => ['content' => '{broken']])]);
        $this->expectExceptionMessage('reader_invalid_json');
        app(FileReceiptReader::class)->read($this->source());
    }

    public function test_untrusted_nested_types_do_not_reach_dtos(): void
    {
        $payload = $this->payload();
        $payload['current_period']['consumption_kwh'] = '520';
        $this->expectExceptionMessage('reader_invalid_shape');
        app(ReadingAssessment::class)->validate(json_encode($payload));
    }

    public function test_mime_mismatch_blocks_before_inference(): void
    {
        $source = $this->source();
        $this->expectExceptionMessage('unsupported_document');
        app(ReceiptImages::class)->prepare(new ReceiptSource(42, $source->privatePath, 'application/pdf'));
    }

    public function test_pdf_pages_are_rendered_into_real_images(): void
    {
        Storage::disk('receipts')->put('test.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-readable.pdf')));
        $images = app(ReceiptImages::class)->prepare(new ReceiptSource(42, 'test.pdf', 'application/pdf'));
        $this->assertCount(1, $images);
        $this->assertSame('image/png', getimagesizefromstring(base64_decode($images[0]))['mime']);
        $this->assertStringNotContainsString('%PDF', base64_decode($images[0]));
    }

    public function test_pdf_text_layer_is_attached_with_images_as_untrusted_document_data(): void
    {
        $this->reply();
        Storage::disk('receipts')->put('test.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-readable.pdf')));
        app(FileReceiptReader::class)->read(new ReceiptSource(42, 'test.pdf', 'application/pdf'));

        Http::assertSent(fn (Request $request): bool => str_contains($request['messages'][1]['content'], 'Consumo: 520 kWh')
            && str_contains($request['messages'][1]['content'], 'Página 1:')
            && count($request['messages'][1]['images']) === 1);
    }

    public function test_blank_pdf_does_not_supply_unusable_text_to_the_model(): void
    {
        Storage::disk('receipts')->put('blank.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-unreadable.pdf')));
        $this->assertNull(app(ReceiptText::class)->extract(new ReceiptSource(42, 'blank.pdf', 'application/pdf')));
    }

    public function test_pdf_limit_rejects_instead_of_silently_dropping_pages(): void
    {
        Storage::disk('receipts')->put('test.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-readable.pdf')));
        config(['receipt_reader.max_pages' => 0]);
        $this->expectExceptionMessage('pdf_page_limit');
        app(ReceiptImages::class)->prepare(new ReceiptSource(42, 'test.pdf', 'application/pdf'));
    }

    public function test_nonlocal_endpoint_is_rejected_before_sending_private_images(): void
    {
        config(['receipt_reader.url' => 'https://example.test']);
        $this->expectExceptionMessage('reader_requires_loopback_tunnel');
        app(FileReceiptReader::class)->read($this->source());
    }

    public function test_valid_extraction_is_persisted_once_and_requires_human_review(): void
    {
        $this->reply();
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'],
            UploadedFile::fake()->createWithContent('test.png', $this->png()), null, null);
        $saved = app(ReadReceiptFile::class)($intake);
        $again = app(ReadReceiptFile::class)($intake);

        $this->assertSame($saved->id, $again->id);
        $this->assertSame('needs_review', $saved->status);
        $this->assertSame('validated', $saved->validation_result['status']);
        $this->assertSame('needs_review', $intake->fresh()->status);
        $this->assertDatabaseCount('receipt_readings', 1);
        $this->assertSame('draft', SolarCase::sole()->publication_status);
        $this->assertArrayNotHasKey('raw_response', $saved->toArray());
        Http::assertSentCount(1);
    }

    public function test_model_evidence_without_source_verification_does_not_auto_validate(): void
    {
        $payload = $this->payload();
        foreach (['service.tariff_code', 'current_period.period_start', 'current_period.period_end'] as $field) {
            $payload['extraction']['evidence'][] = ['field' => $field, 'page' => 1, 'text' => data_get($payload, $field)];
        }
        $this->reply($payload);
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'],
            UploadedFile::fake()->createWithContent('test.png', $this->png()), null, null);
        $saved = app(ReadReceiptFile::class)($intake);
        $this->assertSame('needs_review', $saved->status);
        $this->assertSame(60, $saved->normalization['current_period_days']);
        $this->assertSame('22', $saved->normalization['supply_state_code']);
        $this->assertSame('draft', SolarCase::sole()->publication_status);
    }

    public function test_audit_failure_rolls_back_reading_and_preserves_private_source(): void
    {
        $this->reply();
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'],
            UploadedFile::fake()->createWithContent('test.png', $this->png()), null, null);
        DB::unprepared("CREATE TRIGGER fail_read_audit BEFORE INSERT ON solar_case_events WHEN NEW.event = 'receipt_read' BEGIN SELECT RAISE(ABORT, 'synthetic audit failure'); END");
        try {
            app(ReadReceiptFile::class)($intake);
            $this->fail('Expected audit failure.');
        } catch (QueryException) {
            $this->assertDatabaseCount('receipt_readings', 0);
            $this->assertNull($intake->fresh()->raw_payload);
            $this->assertSame('stored', $intake->fresh()->status);
            Storage::disk('receipts')->assertExists($intake->file_path);
        }
    }

    public function test_probe_checks_expected_fields_without_saving_extractions_or_prospects(): void
    {
        $payload = $this->payload();
        $payload['service']['holder_name'] = null;
        $payload['service']['phases'] = null;
        $this->reply($payload);
        $this->artisan('helios:receipts:probe', ['file' => resource_path('fixtures/receipts/synthetic-readable.pdf'),
            '--expected' => resource_path('fixtures/receipts/synthetic-readable.expected.json')])
            ->expectsOutputToContain('Coincidencias: 15/15')->assertExitCode(0);
        $this->assertDatabaseCount('receipt_readings', 0);
        $this->assertDatabaseCount('clients', 0);
        $this->assertSame([], Storage::disk('receipts')->allFiles());
    }

    public function test_optional_missing_fields_do_not_bypass_source_verification(): void
    {
        $payload = $this->payload();
        $payload['service']['holder_name'] = null;
        $payload['extraction']['status'] = 'partial';
        $payload['extraction']['missing_fields'] = ['service.holder_name'];
        $payload['extraction']['warnings'] = [['code' => 'NOT_PRINTED', 'field' => 'service.holder_name', 'message' => 'No impreso.']];
        foreach (['service.tariff_code', 'current_period.period_start', 'current_period.period_end'] as $field) {
            $payload['extraction']['evidence'][] = ['field' => $field, 'page' => 1, 'text' => data_get($payload, $field)];
        }
        $this->reply($payload);
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'],
            UploadedFile::fake()->createWithContent('test.png', $this->png()), null, null);
        $saved = app(ReadReceiptFile::class)($intake);
        $this->assertSame('needs_review', $saved->status);
        $this->assertSame('partial', json_decode($saved->payload_json, true)['extraction']['status']);
    }

    public function test_failed_extraction_is_unreadable(): void
    {
        $this->assertExtractionStatus('failed', 'unreadable');
    }

    public function test_missing_printed_fields_require_review_without_claiming_unreadability(): void
    {
        $this->assertExtractionStatus('partial', 'needs_review');
    }

    private function assertExtractionStatus(string $extraction, string $expected): void
    {
        $payload = $this->payload();
        $payload['extraction']['status'] = $extraction;
        $payload['current_period']['consumption_kwh'] = null;
        $this->reply($payload);
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic'],
            UploadedFile::fake()->createWithContent('test.png', $this->png()), null, null);
        $this->assertSame($expected, app(ReadReceiptFile::class)($intake)->status);
        $this->assertDatabaseCount('receipt_readings', 1);
    }
}
