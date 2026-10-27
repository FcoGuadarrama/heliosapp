<?php

namespace Tests\Feature;

use App\Application\ReadAndSizeReceipt;
use App\Application\ReadReceiptFile;
use App\Application\ReviewReceiptReading;
use App\Application\StoreReceiptIntake;
use App\Domain\Hsp\HspLookup;
use App\Domain\Hsp\HspProvider;
use App\Domain\Hsp\HspValue;
use App\Domain\Processing\ProcessingStatus;
use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use App\Infrastructure\Receipts\ReceiptSourceExtractor;
use App\Models\SolarCalculation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SourceReceiptReaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_text_becomes_reviewable_json_without_a_generative_model(): void
    {
        Http::preventStrayRequests();
        Storage::fake('receipts');
        config(['receipt_reader.enabled' => true, 'receipt_reader.driver' => 'source',
            'receipt_reader.ocr_binary' => '', 'intake.privacy_version' => 'test', 'intake.privacy_text' => 'Test']);
        $file = UploadedFile::fake()->createWithContent('source.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-cfe-source.pdf')));
        $intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic', 'email' => 'synthetic@example.test'], $file, null, null);
        $reading = app(ReadReceiptFile::class)($intake);
        $payload = json_decode($reading->payload_json, true);
        $this->assertSame('needs_review', $reading->status);
        $this->assertSame('pdftotext', $intake->fresh()->reader_identifier);
        $this->assertSame(520, $payload['current_period']['consumption_kwh']);
        $this->assertSame('2026-07-15', $payload['current_period']['period_start']);
        $this->assertSame(480, $payload['consumption_history'][0]['consumption_kwh']);
        $this->assertNull($payload['service']['phases']);
        $this->assertNull($payload['current_period']['days']);
        $this->assertNull($payload['service']['service_address']['state']);
        $this->assertFalse($reading->validation_result['source_verified']);
        $this->assertSame('SOURCE_REVIEW_REQUIRED', $payload['extraction']['warnings'][0]['code']);
        $this->assertDatabaseCount('solar_calculations', 0);
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('owner');
        $payload['service']['service_address']['state'] = 'Querétaro';
        app(ReviewReceiptReading::class)($reading, $actor, json_encode($payload, JSON_THROW_ON_ERROR), 'Cotejo sintético completo', 0);
        $hsp = new HspValue('22', 'Querétaro', 5.0, [], 'synthetic', 'test', ['provider' => 'test'],
            now()->toIso8601String(), now()->addDay()->toIso8601String());
        $this->mock(HspProvider::class)->shouldReceive('forState')->with('22')->andReturn(new HspLookup($hsp));
        $outcome = app(ReadAndSizeReceipt::class)->process(new ReceiptSource($intake->id, $intake->file_path, 'application/pdf'));
        $this->assertSame(ProcessingStatus::Completed, $outcome->status);
        $this->assertSame(1, SolarCalculation::sole()->review_revision);
        $this->assertNull(SolarCalculation::sole()->result['moduleCount']);
        Http::assertNothingSent();
    }

    public function test_scanned_or_unrecognized_pdf_requires_explicit_ocr_configuration(): void
    {
        config(['receipt_reader.ocr_binary' => '']);
        $this->expectException(ReceiptReadingFailure::class);
        $this->expectExceptionMessage('source_ocr_unavailable');
        app(ReceiptSourceExtractor::class)->extract(resource_path('fixtures/receipts/synthetic-unreadable.pdf'));
    }

    public function test_export_does_not_overwrite_an_existing_file(): void
    {
        $output = tempnam(sys_get_temp_dir(), 'helios-export-');
        file_put_contents($output, 'keep');
        try {
            $this->artisan('helios:receipts:extract', ['file' => resource_path('fixtures/receipts/synthetic-cfe-source.pdf'), '--output' => $output])->assertFailed();
            $this->assertSame('keep', file_get_contents($output));
        } finally {
            unlink($output);
        }
    }
}
