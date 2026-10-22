<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `document` — origen del dato (contrato §2).
 *
 * `sourceType`, `extractedAt` y `extractor` son, junto con `schema_version`
 * y `extraction.status`, los únicos campos estructuralmente obligatorios.
 */
final readonly class DocumentData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $sourceType = null,
        public ?string $sourceReference = null,
        public int|float|null $pageCount = null,
        public ?string $extractedAt = null,
        public ExtractorData $extractor = new ExtractorData,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['source_type', 'source_reference', 'page_count', 'extracted_at', 'extractor'];

        return new self(
            sourceType: isset($raw['source_type']) ? (string) $raw['source_type'] : null,
            sourceReference: isset($raw['source_reference']) ? (string) $raw['source_reference'] : null,
            pageCount: $raw['page_count'] ?? null,
            extractedAt: isset($raw['extracted_at']) ? (string) $raw['extracted_at'] : null,
            extractor: ExtractorData::fromArray(is_array($raw['extractor'] ?? null) ? $raw['extractor'] : null),
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'source_type' => $this->sourceType,
            'source_reference' => $this->sourceReference,
            'page_count' => $this->pageCount,
            'extracted_at' => $this->extractedAt,
            'extractor' => $this->extractor->toArray(),
        ], $this->extra);
    }
}
