<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingStatus;
use App\Jobs\ProcessReceipt;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class QueueReceiptProcessing
{
    public function __invoke(ReceiptIntake $intake): ReceiptProcessingRun
    {
        return DB::transaction(function () use ($intake): ReceiptProcessingRun {
            $locked = ReceiptIntake::query()->lockForUpdate()->findOrFail($intake->id);
            if ($locked->solar_case_id === null) {
                throw ValidationException::withMessages(['receipt' => 'El recibo histórico necesita vincularse a un expediente.']);
            }
            $case = SolarCase::query()->lockForUpdate()->findOrFail($locked->solar_case_id);
            $existing = ReceiptProcessingRun::where('receipt_intake_id', $locked->id)->first();
            if ($existing !== null) {
                $this->dispatchAfterCommit($existing);

                return $existing;
            }
            $run = ReceiptProcessingRun::create([
                'solar_case_id' => $case->id,
                'receipt_intake_id' => $locked->id,
                'status' => ProcessingStatus::Queued,
            ]);
            $case->update(['processing_status' => ProcessingStatus::Queued]);
            SolarCaseEvent::create([
                'solar_case_id' => $case->id,
                'event_key' => 'queued:'.$run->id,
                'event' => 'processing_queued',
                'created_at' => now(),
            ]);
            $this->dispatchAfterCommit($run);

            return $run;
        });
    }

    private function dispatchAfterCommit(ReceiptProcessingRun $run): void
    {
        if ($run->status !== ProcessingStatus::Queued || $run->dispatched_at !== null) {
            return;
        }
        DB::afterCommit(function () use ($run): void {
            Bus::dispatch((new ProcessReceipt($run->id))->afterCommit());
            $run->update(['dispatched_at' => now()]);
        });
    }
}
