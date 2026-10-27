<?php

declare(strict_types=1);

namespace App\Domain\Solar;

use InvalidArgumentException;

final readonly class ConsumptionAverage
{
    public function __construct(public float $totalKwh, public int $coveredDays)
    {
        if (! is_finite($totalKwh) || $totalKwh <= 0 || $coveredDays <= 0) {
            throw new InvalidArgumentException('Se necesitan consumo positivo y días verificables.');
        }
    }

    public function dailyKwh(): float
    {
        return $this->totalKwh / $this->coveredDays;
    }
}
