<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

final class AreaWeightedHsp
{
    /** @param array<string, array<string, float>> $cells
     * @return array<string, float>
     */
    public function calculate(StateArea $state, array $cells): array
    {
        if ($state->weights === [] || abs(array_sum($state->weights) - 1) > 0.00000001) {
            throw new InvalidHspData('invalid_area_weights');
        }
        $result = array_fill_keys(NasaClimatologyParser::PERIODS, 0.0);
        foreach ($state->weights as $key => $weight) {
            if (! is_finite($weight) || $weight <= 0 || $weight > 1 || ! isset($cells[$key])) {
                throw new InvalidHspData('missing_cell_or_invalid_weight');
            }
            foreach ($result as $period => $total) {
                $value = $cells[$key][$period] ?? null;
                if ($value === null || ! is_finite($value) || $value < 0 || $value > 24) {
                    throw new InvalidHspData('invalid_cell_value');
                }
                $result[$period] = $total + $weight * $value;
            }
        }
        if ($result['ANN'] <= 0) {
            throw new InvalidHspData('invalid_annual_hsp');
        }

        return array_map(fn (float $value): float => round($value, 6), $result);
    }
}
