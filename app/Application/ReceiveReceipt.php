<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingStatus;
use App\Models\Client;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ReceiveReceipt
{
    public function __construct(private StoreReceiptIntake $store, private QueueReceiptProcessing $queue) {}

    /** @param array<string, mixed> $data */
    public function __invoke(array $data, UploadedFile $file, ?Client $client, ?User $actor): ReceiptIntake
    {
        $intake = ($this->store)($data, $file, $client, $actor);
        if (config('receipt_reader.enabled')) {
            $this->enqueue($intake);
        }

        return $intake;
    }

    public function enqueue(ReceiptIntake $intake, ?User $actor = null): void
    {
        $connection = config('queue.default');
        if (! in_array($connection, ['database', 'redis', 'beanstalkd'], true)
            || config('queue.connections.'.$connection.'.retry_after') <= 900) {
            $this->recordFailure($intake, 'queue_configuration_invalid');

            return;
        }
        try {
            $run = ReceiptProcessingRun::where('receipt_intake_id', $intake->id)->first();
            if ($run !== null && $actor !== null && in_array($run->status, [ProcessingStatus::Blocked, ProcessingStatus::Failed], true)) {
                app(RetryReceiptProcessing::class)($run, $actor);
            } else {
                ($this->queue)($intake);
            }
        } catch (Throwable) {
            $this->recordFailure($intake, 'queue_dispatch_failed');
        }
    }

    private function recordFailure(ReceiptIntake $intake, string $reason): void
    {
        DB::transaction(function () use ($intake, $reason): void {
            SolarCase::query()->lockForUpdate()->findOrFail($intake->solar_case_id);
            $run = ReceiptProcessingRun::where('receipt_intake_id', $intake->id)->lockForUpdate()->first();
            if ($run !== null) {
                $run->update(['status' => ProcessingStatus::Failed, 'error_code' => $reason, 'finished_at' => now()]);
            }
            SolarCase::whereKey($intake->solar_case_id)->update(['processing_status' => ProcessingStatus::Failed,
                'blocker_code' => $reason, 'attention_required_at' => now()]);
            SolarCaseEvent::firstOrCreate(['event_key' => 'dispatch-failed:'.$intake->id.':'.($run->attempts ?? 0).':'.$reason], [
                'solar_case_id' => $intake->solar_case_id,
                'event' => 'queue_dispatch_failed', 'reason_code' => $reason, 'created_at' => now()]);
        });
    }
}
