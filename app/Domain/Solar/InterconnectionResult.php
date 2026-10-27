<?php

declare(strict_types=1);

namespace App\Domain\Solar;

final readonly class InterconnectionResult
{
    public function __construct(
        public string $motorVersion,
        public string $consumptionSource,
        public float $historyDays,
        public float $dailyConsumptionKwh,
        public float $requiredKwp,
        public int $minimumInverterW,
        public ?int $moduleCount,
        public ?float $selectedKwp,
        public ?int $microinverterCount,
    ) {}

    /** @return array<string, string|float|int|null> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
