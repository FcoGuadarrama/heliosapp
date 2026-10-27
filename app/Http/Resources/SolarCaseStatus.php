<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Processing\ProcessingStatus;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class SolarCaseStatus
{
    /** @return array<string, mixed> */
    public function customer(SolarCase $case): array
    {
        return [
            'schema_version' => '1.0',
            'reference' => $case->public_id,
            'processing' => $case->processing_status->value,
            'simulated' => $case->simulated,
            'next_action' => match ($case->processing_status) {
                ProcessingStatus::Blocked, ProcessingStatus::Failed => 'advisor_review',
                ProcessingStatus::Received => 'processing_not_started',
                ProcessingStatus::Completed => 'none',
                ProcessingStatus::Queued, ProcessingStatus::Running => 'wait',
            },
            'result' => $case->processing_status === ProcessingStatus::Completed && ! $case->simulated
                ? SolarCalculation::where('solar_case_id', $case->id)->latest('id')->first()?->result : null,
            'sale_price' => ['status' => 'unavailable', 'reason' => 'pricing_not_configured', 'amount_minor' => null],
        ];
    }

    /** @return array<string, mixed> */
    public function advisor(SolarCase $case, User $actor): array
    {
        if (! $actor->can('process', $case)) {
            throw new AuthorizationException;
        }

        return [...$this->customer($case),
            'blocker_code' => $case->blocker_code,
            'purchase_cost' => ['status' => 'unavailable', 'reason' => 'supplier_not_connected', 'amount_minor' => null],
        ];
    }
}
