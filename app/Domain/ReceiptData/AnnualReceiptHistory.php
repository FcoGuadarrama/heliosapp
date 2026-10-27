<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class AnnualReceiptHistory
{
    public const VERSION = 'latest-contiguous-330-400-days-v1';

    /** @return array{kwh: list<float>, indices: list<int>, actual_days: int, reason: string} */
    public function select(ReceiptPayload $payload): array
    {
        $rows = [];
        foreach ($payload->consumptionHistory as $index => $period) {
            if ($period->periodStart === null || $period->periodEnd === null || $period->consumptionKwh === null) {
                throw ValidationException::withMessages(['history' => 'Revisa las fechas y consumos del histórico.']);
            }
            if ($period->periodEnd <= $payload->currentPeriod->periodStart) {
                $rows[] = ['index' => $index, 'period' => $period];
            }
        }
        usort($rows, fn (array $a, array $b): int => strcmp($b['period']->periodEnd, $a['period']->periodEnd));
        $kwh = [];
        $indices = [];
        $days = 0;
        $nextStart = $payload->currentPeriod->periodStart;
        foreach ($rows as $row) {
            $period = $row['period'];
            $length = (int) CarbonImmutable::parse($period->periodStart)->diffInDays(CarbonImmutable::parse($period->periodEnd), false);
            $gap = CarbonImmutable::parse($period->periodEnd)->diffInDays(CarbonImmutable::parse($nextStart), false);
            if ($length <= 0 || $gap < 0 || $gap > config('helios.receipts.history_gap_max_days')) {
                throw ValidationException::withMessages(['history' => 'El histórico anual debe ser consecutivo y sin traslapes.']);
            }
            if ($days + $length > 400) {
                break;
            }
            $days += $length;
            $kwh[] = (float) $period->consumptionKwh;
            $indices[] = $row['index'];
            $nextStart = $period->periodStart;
            if ($days >= 330) {
                break;
            }
        }

        return ['kwh' => $days >= 330 ? $kwh : [], 'indices' => $indices, 'actual_days' => $days,
            'reason' => $days >= 330 ? 'annual_history' : 'insufficient_history_use_current'];
    }
}
