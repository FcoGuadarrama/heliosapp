<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * Un periodo del historial de consumo impreso en el recibo
 * (`consumption_history[]`).
 */
final readonly class HistoryPeriodData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $periodStart = null,
        public ?string $periodEnd = null,
        public int|float|null $days = null,
        public int|float|null $consumptionKwh = null,
        public int|float|null $amountMxn = null,
        public ?string $source = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['period_start', 'period_end', 'days', 'consumption_kwh', 'amount_mxn', 'source'];

        return new self(
            periodStart: isset($raw['period_start']) ? (string) $raw['period_start'] : null,
            periodEnd: isset($raw['period_end']) ? (string) $raw['period_end'] : null,
            days: $raw['days'] ?? null,
            consumptionKwh: $raw['consumption_kwh'] ?? null,
            amountMxn: $raw['amount_mxn'] ?? null,
            source: isset($raw['source']) ? (string) $raw['source'] : null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'days' => $this->days,
            'consumption_kwh' => $this->consumptionKwh,
            'amount_mxn' => $this->amountMxn,
            'source' => $this->source,
        ], $this->extra);
    }
}
