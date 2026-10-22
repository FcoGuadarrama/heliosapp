<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * Un concepto de facturación de `current_period.concepts`.
 */
final readonly class ConceptData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $code = null,
        public ?string $description = null,
        public int|float|null $amountMxn = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['code', 'description', 'amount_mxn'];

        return new self(
            code: isset($raw['code']) ? (string) $raw['code'] : null,
            description: isset($raw['description']) ? (string) $raw['description'] : null,
            amountMxn: $raw['amount_mxn'] ?? null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'code' => $this->code,
            'description' => $this->description,
            'amount_mxn' => $this->amountMxn,
        ], $this->extra);
    }
}
