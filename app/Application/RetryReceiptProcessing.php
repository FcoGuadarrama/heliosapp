<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingStatus;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RetryReceiptProcessing
{
    public function __invoke(ReceiptProcessingRun $run, User $actor): void
    {
        DB::transaction(function () use ($run, $actor): void {
            SolarCase::query()->lockForUpdate()->findOrFail($run->solar_case_id);
            $locked = ReceiptProcessingRun::query()->lockForUpdate()->findOrFail($run->id);
            if (! $actor->can('process', $locked->solarCase)) {
                throw new AuthorizationException;
            }
            if (! in_array($locked->status, [ProcessingStatus::Blocked, ProcessingStatus::Failed], true)) {
                throw ValidationException::withMessages(['processing' => 'Solo se puede reintentar un procesamiento bloqueado o fallido.']);
            }
            $locked->update(['status' => ProcessingStatus::Queued, 'error_code' => null, 'finished_at' => null, 'dispatched_at' => null]);
            $locked->solarCase->update(['processing_status' => ProcessingStatus::Queued, 'blocker_code' => null, 'attention_required_at' => null]);
            SolarCaseEvent::create([
                'solar_case_id' => $locked->solar_case_id,
                'created_by' => $actor->id,
                'event_key' => 'retry:'.$locked->id.':'.$locked->attempts,
                'event' => 'processing_retried',
                'created_at' => now(),
            ]);
            app(QueueReceiptProcessing::class)($locked->intake);
        });
    }
}
