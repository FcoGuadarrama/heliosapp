<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use App\Models\EquipmentQuoteDraft;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\ReceiptReading;
use App\Models\ReceiptReview;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class ReceiptAnalysis
{
    /** @return array<string, mixed> */
    public function forAdvisor(ReceiptIntake $intake, User $actor): array
    {
        if (! $actor->is_active || ! $actor->can('view', $intake)) {
            throw new AuthorizationException;
        }
        $case = SolarCase::find($intake->solar_case_id);
        $run = ReceiptProcessingRun::where('receipt_intake_id', $intake->id)->first();
        $reading = ReceiptReading::where('receipt_intake_id', $intake->id)->first();
        $review = $reading === null ? null : ReceiptReview::where('receipt_reading_id', $reading->id)->latest('revision')->first();
        $calculation = $reading === null ? null : SolarCalculation::where('receipt_reading_id', $reading->id)
            ->where('review_revision', $review->revision ?? 0)->latest('id')->first();
        $json = $review->payload_json ?? $reading?->payload_json;
        $payload = $json === null ? null : ReceiptPayload::fromArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
        $normalized = $review->normalization ?? $reading?->normalization;
        $validation = $review->validation_result ?? $reading?->validation_result;
        $uploadCount = $case === null ? 1 : ReceiptIntake::where('solar_case_id', $case->id)->count();

        return [
            'processing' => $run?->status->value ?? 'received',
            'reason' => $run->error_code ?? $case?->blocker_code,
            'can_process' => $case !== null && $actor->can('process', $case),
            'revision' => $review->revision ?? 0,
            'has_reading' => $reading !== null,
            'upload_count' => $uploadCount,
            'can_replace' => $intake->status === 'unreadable' && $uploadCount === 1 && $case !== null && $actor->can('process', $case),
            'source_verified' => $reading?->validation_result['source_verified'] ?? false,
            'result' => $calculation?->result,
            'calculation_id' => $calculation?->id,
            'quote_draft' => $calculation === null ? null : EquipmentQuoteDraft::where('solar_case_id', $case?->id)
                ->where('solar_calculation_id', $calculation->id)->latest('version')->first(['id', 'version', 'snapshot', 'created_at']),
            'hsp' => $calculation?->inputs['hsp'] ?? null,
            'validation' => ['errors' => $validation['errors'] ?? [], 'warnings' => $validation['warnings'] ?? [],
                'extraction_warnings' => $payload?->extraction->warnings ?? []],
            'form' => $payload === null ? null : [
                'tariff_code' => $payload->service->tariffCode,
                'phases' => $payload->service->phases,
                'days' => $payload->currentPeriod->days,
                'consumption_kwh' => $payload->currentPeriod->consumptionKwh,
                'period_start' => $payload->currentPeriod->periodStart,
                'period_end' => $payload->currentPeriod->periodEnd,
                'state_code' => $normalized['supply_state_code'] ?? '',
                'history' => array_map(fn ($period): array => $period->toArray(), $payload->consumptionHistory),
                'concepts' => array_map(fn ($concept): array => $concept->toArray(), $payload->currentPeriod->concepts),
                'period_amount_mxn' => $payload->currentPeriod->amountMxn,
                'total_amount_mxn' => $payload->receipt->totalAmountMxn,
            ],
        ];
    }
}
