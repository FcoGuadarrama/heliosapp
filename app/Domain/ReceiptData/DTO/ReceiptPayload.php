<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * El payload completo del JSON de recibo CFE v1.0 (contrato en
 * docs/cfe-receipt-schema.md §2).
 *
 * `fromArray()` es tolerante: cualquier campo ausente queda en null, nunca
 * lanza una excepción por datos incompletos. `toArray()` reconstruye el
 * payload original, incluidos los campos desconocidos no contemplados por
 * el esquema (principio §1.4: "nada se descarta en silencio").
 */
final readonly class ReceiptPayload
{
    use HasExtraFields;

    /**
     * @param  list<HistoryPeriodData>  $consumptionHistory
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $schemaVersion = null,
        public DocumentData $document = new DocumentData,
        public ReceiptInfoData $receipt = new ReceiptInfoData,
        public ServiceData $service = new ServiceData,
        public CurrentPeriodData $currentPeriod = new CurrentPeriodData,
        public array $consumptionHistory = [],
        public ExtractionData $extraction = new ExtractionData,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function fromArray(array $raw): self
    {
        $known = [
            'schema_version', 'document', 'receipt', 'service',
            'current_period', 'consumption_history', 'extraction',
        ];

        $history = array_map(
            fn (mixed $period): HistoryPeriodData => HistoryPeriodData::fromArray(is_array($period) ? $period : null),
            is_array($raw['consumption_history'] ?? null) ? $raw['consumption_history'] : [],
        );

        return new self(
            schemaVersion: isset($raw['schema_version']) ? (string) $raw['schema_version'] : null,
            document: DocumentData::fromArray(is_array($raw['document'] ?? null) ? $raw['document'] : null),
            receipt: ReceiptInfoData::fromArray(is_array($raw['receipt'] ?? null) ? $raw['receipt'] : null),
            service: ServiceData::fromArray(is_array($raw['service'] ?? null) ? $raw['service'] : null),
            currentPeriod: CurrentPeriodData::fromArray(is_array($raw['current_period'] ?? null) ? $raw['current_period'] : null),
            consumptionHistory: array_values($history),
            extraction: ExtractionData::fromArray(is_array($raw['extraction'] ?? null) ? $raw['extraction'] : null),
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'schema_version' => $this->schemaVersion,
            'document' => $this->document->toArray(),
            'receipt' => $this->receipt->toArray(),
            'service' => $this->service->toArray(),
            'current_period' => $this->currentPeriod->toArray(),
            'consumption_history' => array_map(
                fn (HistoryPeriodData $period): array => $period->toArray(),
                $this->consumptionHistory,
            ),
            'extraction' => $this->extraction->toArray(),
        ], $this->extra);
    }
}
