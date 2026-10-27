<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\ReceiptReading;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class SourceReceiptReader implements FileReceiptReader
{
    public const VERSION = 'cfe-source-v1';

    public function __construct(private ReceiptSourceExtractor $extractor, private ReceiptSourceCandidates $candidates) {}

    public function read(ReceiptSource $source): ReceiptReading
    {
        if (! config('receipt_reader.enabled')) {
            throw new ReceiptReadingFailure('reader_disabled');
        }
        $document = $this->extractor->extract(Storage::disk('receipts')->path($source->privatePath));
        $facts = $this->candidates->parse($document);
        $payload = [
            'schema_version' => '1.0',
            'document' => ['source_type' => $source->mimeType === 'application/pdf' ? 'pdf' : 'image', 'source_reference' => 'receipt-intake:'.$source->intakeId,
                'page_count' => count($document['pages']), 'extracted_at' => now()->toIso8601String(),
                'extractor' => ['identifier' => $document['engine'], 'version' => self::VERSION]],
            'receipt' => ['receipt_id' => null, 'issue_date' => null, 'due_date' => null, 'total_amount_mxn' => null],
            'service' => ['service_number' => null, 'tariff_code' => null, 'tariff_description' => null,
                'voltage_level' => null, 'billing_cycle' => null, 'holder_name' => null, 'phases' => null,
                'contracted_demand_kw' => null, 'service_address' => ['street' => null, 'neighborhood' => null,
                    'municipality' => null, 'state' => null, 'postal_code' => null]],
            'current_period' => ['period_start' => null, 'period_end' => null, 'days' => null,
                'consumption_kwh' => null, 'amount_mxn' => null, 'demand_kw' => null,
                'consumption_by_tier' => [], 'concepts' => []],
            'consumption_history' => $facts['history'] ?? [],
        ];
        foreach ($facts['fields'] as $field => $value) {
            Arr::set($payload, $field, $value);
        }
        $critical = ['service.tariff_code', 'current_period.period_start', 'current_period.period_end', 'current_period.consumption_kwh'];
        $payload['extraction'] = [
            'status' => $facts['fields'] === [] ? 'failed' : 'partial',
            'confidence' => new \stdClass,
            'missing_fields' => array_values(array_filter($critical, fn (string $field): bool => Arr::get($payload, $field) === null)),
            'warnings' => [['code' => 'SOURCE_REVIEW_REQUIRED', 'field' => 'document',
                'message' => 'Extracción asistida. Coteja consumo, fechas, tarifa e histórico con el PDF y confirma la ubicación antes de calcular.']],
            'evidence' => $facts['evidence'],
        ];

        return new ReceiptReading(json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $document['engine'],
            count($document['pages']), self::VERSION, false);
    }
}
