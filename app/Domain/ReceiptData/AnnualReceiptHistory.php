<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class AnnualReceiptHistory
{
    public const VERSION = 'all-unique-verifiable-periods-v2';

    /** @return array{kwh: list<float>, indices: list<int>, excluded_indices: list<int>, current_included: bool, actual_days: int, daily_kwh: float, annual_kwh: float, reason: string} */
    public function select(ReceiptPayload $payload): array
    {
        $rows = [];
        $excluded = [];
        $indices = [];
        $currentIncluded = false;
        foreach ([-1 => $payload->currentPeriod, ...$payload->consumptionHistory] as $index => $period) {
            if ($period->periodStart === null || $period->periodEnd === null || $period->consumptionKwh === null) {
                $excluded[] = $index;

                continue;
            }
            $start = $this->date($period->periodStart);
            $end = $this->date($period->periodEnd);
            $kwh = (float) $period->consumptionKwh;
            $days = (int) $start->diffInDays($end, false);
            if ($days <= 0 || ! is_finite($kwh) || $kwh < 0 || ($period->days !== null && $period->days != $days)) {
                throw ValidationException::withMessages(['history' => 'Revisa las fechas, días y consumos del recibo.']);
            }
            $key = $period->periodStart.':'.$period->periodEnd;
            if (isset($rows[$key])) {
                if ($rows[$key]['kwh'] !== $kwh) {
                    throw ValidationException::withMessages(['history' => 'El mismo periodo contiene consumos contradictorios.']);
                }

                continue;
            }
            $rows[$key] = ['start' => $period->periodStart, 'end' => $period->periodEnd, 'kwh' => $kwh, 'days' => $days];
            if ($index === -1) {
                $currentIncluded = true;
            } else {
                $indices[] = $index;
            }
        }
        $ordered = array_values($rows);
        usort($ordered, fn (array $left, array $right): int => strcmp($left['start'], $right['start']));
        $previousEnd = null;
        foreach ($ordered as $row) {
            if ($previousEnd !== null && $row['start'] < $previousEnd) {
                throw ValidationException::withMessages(['history' => 'Los periodos de consumo se traslapan.']);
            }
            $previousEnd = $row['end'];
        }
        $days = array_sum(array_column($ordered, 'days'));
        $total = array_sum(array_column($ordered, 'kwh'));
        if ($days <= 0 || ! is_finite($total) || $total <= 0) {
            throw ValidationException::withMessages(['history' => 'Se necesitan consumo positivo y días verificables para estimar.']);
        }
        $daily = $total / $days;

        return ['kwh' => array_column($ordered, 'kwh'), 'indices' => $indices,
            'excluded_indices' => $excluded, 'current_included' => $currentIncluded,
            'actual_days' => $days, 'daily_kwh' => $daily, 'annual_kwh' => $daily * 365,
            'reason' => $excluded === [] ? 'all_available_periods' : 'partial_available_periods'];
    }

    private function date(string $value): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw ValidationException::withMessages(['history' => 'Revisa las fechas del recibo.']);
        }
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === null || $date->format('Y-m-d') !== $value) {
            throw ValidationException::withMessages(['history' => 'Revisa las fechas del recibo.']);
        }

        return $date;
    }
}
