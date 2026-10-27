<?php

namespace App\Jobs;

use App\Application\ProcessReceiptRun;
use App\Domain\Processing\ProcessingStatus;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessReceipt implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 840;

    public bool $failOnTimeout = true;

    public function __construct(public int $runId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30];
    }

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('receipt-run:'.$this->runId))->releaseAfter(10)->expireAfter(900)];
    }

    public function handle(ProcessReceiptRun $process): void
    {
        $process($this->runId);
    }

    public function failed(?Throwable $exception): void
    {
        DB::transaction(function (): void {
            $source = ReceiptProcessingRun::find($this->runId);
            if ($source === null) {
                return;
            }
            SolarCase::query()->lockForUpdate()->findOrFail($source->solar_case_id);
            $run = ReceiptProcessingRun::query()->lockForUpdate()->find($this->runId);
            if ($run === null || ! in_array($run->status, [ProcessingStatus::Queued, ProcessingStatus::Running], true)) {
                return;
            }
            $run->update(['status' => ProcessingStatus::Failed, 'error_code' => 'processing_failed', 'finished_at' => now()]);
            $run->solarCase->update(['processing_status' => ProcessingStatus::Failed, 'blocker_code' => 'processing_failed', 'attention_required_at' => now()]);
            SolarCaseEvent::firstOrCreate(['event_key' => 'failed:'.$run->id.':'.$run->attempts], [
                'solar_case_id' => $run->solar_case_id,
                'event' => 'processing_failed',
                'reason_code' => 'processing_failed',
                'created_at' => now(),
            ]);
        });
    }
}
