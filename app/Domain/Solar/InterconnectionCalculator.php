<?php

declare(strict_types=1);

namespace App\Domain\Solar;

use InvalidArgumentException;

final class InterconnectionCalculator
{
    public const VERSION = 'syscom-interconnection-v1';

    public const PERFORMANCE_RATIO = 0.77;

    public const DC_AC_RATIO = 1.2;

    public function calculate(InterconnectionInput $input): InterconnectionResult
    {
        $historyDays = count($input->historyKwh) * $input->periodDays;
        if ($historyDays > 400) {
            $historyDays = 365;
        }

        $useHistory = $historyDays >= 330;
        $dailyConsumption = $useHistory
            ? array_sum($input->historyKwh) / $historyDays
            : $input->currentConsumptionKwh / $input->periodDays;

        if (! is_finite($dailyConsumption) || $dailyConsumption <= 0) {
            throw new InvalidArgumentException('Se necesita un consumo diario positivo para dimensionar.');
        }

        $requiredKwp = $dailyConsumption / (self::PERFORMANCE_RATIO * $input->hsp);
        $moduleCount = $this->ceilQuantity($requiredKwp * 1000 / $input->modulePowerW);

        return new InterconnectionResult(
            motorVersion: self::VERSION,
            consumptionSource: $useHistory ? 'history' : 'current_period',
            historyDays: (float) $historyDays,
            dailyConsumptionKwh: $dailyConsumption,
            requiredKwp: $requiredKwp,
            minimumInverterW: $this->ceilQuantity($requiredKwp * 1000 / self::DC_AC_RATIO),
            moduleCount: $moduleCount,
            selectedKwp: $moduleCount * $input->modulePowerW / 1000,
            microinverterCount: $input->modulesPerMicroinverter === null
                ? null
                : $this->ceilQuantity($moduleCount / $input->modulesPerMicroinverter),
        );
    }

    private function ceilQuantity(float $value): int
    {
        if (! is_finite($value) || $value <= 0 || $value >= PHP_INT_MAX) {
            throw new InvalidArgumentException('El dimensionamiento excede el rango numérico admitido.');
        }

        return (int) ceil($value);
    }
}
