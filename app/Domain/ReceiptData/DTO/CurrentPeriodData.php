<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `current_period` — periodo facturado del recibo (contrato §2).
 *
 * `period_start`, `period_end` y `consumption_kwh` son campos críticos
 * (contrato §3): su ausencia no es un error de esquema, pero bloquea un
 * diagnóstico energético y lleva el import a `needs_review`.
 */
final readonly class CurrentPeriodData
{
    use HasExtraFields;

    /**
     * @param  list<TierData>  $consumptionByTier
     * @param  list<ConceptData>  $concepts
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $periodStart = null,
        public ?string $periodEnd = null,
        public int|float|null $days = null,
        public int|float|null $consumptionKwh = null,
        public array $consumptionByTier = [],
        public int|float|null $demandKw = null,
        public int|float|null $amountMxn = null,
        public array $concepts = [],
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = [
            'period_start', 'period_end', 'days', 'consumption_kwh',
            'consumption_by_tier', 'demand_kw', 'amount_mxn', 'concepts',
        ];

        $tiers = array_map(
            fn (mixed $tier): TierData => TierData::fromArray(is_array($tier) ? $tier : null),
            is_array($raw['consumption_by_tier'] ?? null) ? $raw['consumption_by_tier'] : [],
        );

        $concepts = array_map(
            fn (mixed $concept): ConceptData => ConceptData::fromArray(is_array($concept) ? $concept : null),
            is_array($raw['concepts'] ?? null) ? $raw['concepts'] : [],
        );

        return new self(
            periodStart: isset($raw['period_start']) ? (string) $raw['period_start'] : null,
            periodEnd: isset($raw['period_end']) ? (string) $raw['period_end'] : null,
            days: $raw['days'] ?? null,
            consumptionKwh: $raw['consumption_kwh'] ?? null,
            consumptionByTier: array_values($tiers),
            demandKw: $raw['demand_kw'] ?? null,
            amountMxn: $raw['amount_mxn'] ?? null,
            concepts: array_values($concepts),
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
            'consumption_by_tier' => array_map(fn (TierData $tier): array => $tier->toArray(), $this->consumptionByTier),
            'demand_kw' => $this->demandKw,
            'amount_mxn' => $this->amountMxn,
            'concepts' => array_map(fn (ConceptData $concept): array => $concept->toArray(), $this->concepts),
        ], $this->extra);
    }
}
