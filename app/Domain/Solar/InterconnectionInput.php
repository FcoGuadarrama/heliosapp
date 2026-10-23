<?php

declare(strict_types=1);

namespace App\Domain\Solar;

use InvalidArgumentException;

final readonly class InterconnectionInput
{
    /** @param list<float> $historyKwh */
    public function __construct(
        public float $currentConsumptionKwh,
        public int $periodDays,
        public float $hsp,
        public float $modulePowerW,
        public array $historyKwh = [],
        public ?int $modulesPerMicroinverter = null,
    ) {
        if ($periodDays <= 0 || ($modulesPerMicroinverter !== null && $modulesPerMicroinverter <= 0)) {
            throw new InvalidArgumentException('Los días y módulos por microinversor deben ser positivos.');
        }

        foreach ([$currentConsumptionKwh, ...$historyKwh] as $consumption) {
            if (! is_finite($consumption) || $consumption < 0) {
                throw new InvalidArgumentException('El consumo debe ser finito y no negativo.');
            }
        }

        foreach ([$hsp, $modulePowerW] as $value) {
            if (! is_finite($value) || $value <= 0) {
                throw new InvalidArgumentException('HSP y potencia del módulo deben ser finitos y positivos.');
            }
        }
    }
}
