<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * Evidencia que vincula una cifra extraída con su ubicación en el
 * documento origen (`extraction.evidence[]`), contrato §1.3.
 */
final readonly class EvidenceData
{
    use HasExtraFields;

    /**
     * @param  array<int, mixed>|null  $bbox
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $field = null,
        public int|float|null $page = null,
        public ?array $bbox = null,
        public ?string $text = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['field', 'page', 'bbox', 'text'];

        return new self(
            field: isset($raw['field']) ? (string) $raw['field'] : null,
            page: $raw['page'] ?? null,
            bbox: isset($raw['bbox']) && is_array($raw['bbox']) ? $raw['bbox'] : null,
            text: isset($raw['text']) ? (string) $raw['text'] : null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'field' => $this->field,
            'page' => $this->page,
            'bbox' => $this->bbox,
            'text' => $this->text,
        ], $this->extra);
    }
}
