<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * Un escalón (o periodo horario) de `current_period.consumption_by_tier`.
 */
final readonly class TierData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $tier = null,
        public int|float|null $kwh = null,
        public int|float|null $unitPriceMxn = null,
        public int|float|null $amountMxn = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['tier', 'kwh', 'unit_price_mxn', 'amount_mxn'];

        return new self(
            tier: isset($raw['tier']) ? (string) $raw['tier'] : null,
            kwh: $raw['kwh'] ?? null,
            unitPriceMxn: $raw['unit_price_mxn'] ?? null,
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
            'tier' => $this->tier,
            'kwh' => $this->kwh,
            'unit_price_mxn' => $this->unitPriceMxn,
            'amount_mxn' => $this->amountMxn,
        ], $this->extra);
    }
}
