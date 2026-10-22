<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `extraction` — metadatos de la lectura (contrato §2).
 *
 * `status` es, junto con `schema_version`, `document.source_type`,
 * `document.extracted_at` y `document.extractor`, estructuralmente
 * obligatorio.
 */
final readonly class ExtractionData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $confidence  campo => confianza 0..1 (tipo no garantizado: viene de JSON no confiable)
     * @param  list<string>  $missingFields
     * @param  list<WarningData>  $warnings
     * @param  list<EvidenceData>  $evidence
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $status = null,
        public array $confidence = [],
        public array $missingFields = [],
        public array $warnings = [],
        public array $evidence = [],
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['status', 'confidence', 'missing_fields', 'warnings', 'evidence'];

        $warnings = array_map(
            fn (mixed $warning): WarningData => WarningData::fromArray(is_array($warning) ? $warning : null),
            is_array($raw['warnings'] ?? null) ? $raw['warnings'] : [],
        );

        $evidence = array_map(
            fn (mixed $item): EvidenceData => EvidenceData::fromArray(is_array($item) ? $item : null),
            is_array($raw['evidence'] ?? null) ? $raw['evidence'] : [],
        );

        $missingFields = is_array($raw['missing_fields'] ?? null)
            ? array_map(strval(...), $raw['missing_fields'])
            : [];

        $confidence = is_array($raw['confidence'] ?? null) ? $raw['confidence'] : [];

        return new self(
            status: isset($raw['status']) ? (string) $raw['status'] : null,
            confidence: $confidence,
            missingFields: array_values($missingFields),
            warnings: array_values($warnings),
            evidence: array_values($evidence),
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'status' => $this->status,
            'confidence' => $this->confidence,
            'missing_fields' => $this->missingFields,
            'warnings' => array_map(fn (WarningData $warning): array => $warning->toArray(), $this->warnings),
            'evidence' => array_map(fn (EvidenceData $item): array => $item->toArray(), $this->evidence),
        ], $this->extra);
    }
}
