<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class NormalizeReceipt
{
    public const VERSION = 'receipt-normalization-v1';

    /** @return array<string, mixed> */
    public function normalize(ReceiptPayload $payload): array
    {
        $current = $payload->currentPeriod;
        $days = $this->days($current->periodStart, $current->periodEnd);
        $history = [];
        $seen = [];
        foreach ($payload->consumptionHistory as $index => $period) {
            $key = $period->periodStart.':'.$period->periodEnd;
            if (isset($seen[$key]) && $period->periodStart !== null && $period->periodEnd !== null) {
                throw ValidationException::withMessages(['receipt' => 'El histórico contiene periodos duplicados.']);
            }
            $seen[$key] = true;
            $actualDays = $this->days($period->periodStart, $period->periodEnd);
            if ($period->periodEnd !== null && CarbonImmutable::parse($period->periodEnd)->isFuture()) {
                throw ValidationException::withMessages(['receipt' => 'El histórico contiene un periodo futuro.']);
            }
            $history[] = ['source_index' => $index, ...$period->toArray(), 'actual_days' => $actualDays];
        }

        return [
            'version' => self::VERSION,
            'current_consumption_kwh' => $current->consumptionKwh,
            'current_period_days' => $days,
            'current_declared_days' => $current->days,
            'current_daily_kwh' => $days === null || $current->consumptionKwh === null ? null : $current->consumptionKwh / $days,
            'history' => $history,
            'history_actual_days' => array_sum(array_column($history, 'actual_days')),
            'history_has_incomplete_periods' => count(array_filter($history,
                fn (array $period): bool => $period['actual_days'] === null || $period['consumption_kwh'] === null)) > 0,
            'history_includes_current' => isset($seen[$current->periodStart.':'.$current->periodEnd]),
            'supply_state_text' => $payload->service->serviceAddress->state,
            'location_confirmed' => false,
        ];
    }

    private function days(?string $start, ?string $end): ?int
    {
        if ($start === null || $end === null) {
            return null;
        }
        $days = (int) CarbonImmutable::parse($start)->diffInDays(CarbonImmutable::parse($end), false);
        if ($days <= 0) {
            throw ValidationException::withMessages(['receipt' => 'El periodo debe tener una duración positiva.']);
        }

        return $days;
    }
}
