<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingBlocker;
use App\Domain\Processing\ProcessingOutcome;
use App\Domain\Processing\ProcessingStatus;
use App\Domain\Processing\ReceiptProcessor;
use App\Domain\Processing\ReceiptSource;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCaseEvent;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProcessReceiptRun
{
    public function __construct(private ReceiptProcessor $processor) {}

    public function __invoke(int $runId): void
    {
        $run = $this->start($runId);
        if ($run === null) {
            return;
        }
        try {
            $intake = $run->intake;
            $outcome = $intake->client->trashed()
                ? new ProcessingOutcome(ProcessingStatus::Blocked, ProcessingBlocker::ProspectUnavailable)
                : $this->processor->process(new ReceiptSource($intake->id, $intake->file_path, $intake->mime_type));
        } catch (Throwable $exception) {
            $this->record($run, new ProcessingOutcome(ProcessingStatus::Blocked, ProcessingBlocker::ProcessingError), retry: true);
            throw $exception;
        }
        $this->record($run, $outcome);
    }

    private function start(int $runId): ?ReceiptProcessingRun
    {
        return DB::transaction(function () use ($runId): ?ReceiptProcessingRun {
            $run = ReceiptProcessingRun::query()->lockForUpdate()->findOrFail($runId);
            if (! in_array($run->status, [ProcessingStatus::Queued, ProcessingStatus::Running], true)) {
                return null;
            }
            $run->update([
                'status' => ProcessingStatus::Running,
                'attempts' => $run->attempts + 1,
                'started_at' => now(),
                'finished_at' => null,
                'error_code' => null,
            ]);
            $run->solarCase->update(['processing_status' => ProcessingStatus::Running]);
            $this->event($run, 'processing_started');

            return $run;
        });
    }

    private function record(ReceiptProcessingRun $run, ProcessingOutcome $outcome, bool $retry = false): void
    {
        DB::transaction(function () use ($run, $outcome, $retry): void {
            $locked = ReceiptProcessingRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($locked->status !== ProcessingStatus::Running || $locked->attempts !== $run->attempts) {
                return;
            }
            $status = $retry ? ProcessingStatus::Queued : $outcome->status;
            $locked->update(['status' => $status, 'error_code' => $outcome->blockerCode?->value, 'finished_at' => $retry ? null : now()]);
            $locked->solarCase->update([
                'processing_status' => $status,
                'blocker_code' => $outcome->blockerCode?->value,
                'simulated' => $outcome->simulated,
                'attention_required_at' => $status === ProcessingStatus::Blocked ? now() : null,
            ]);
            $this->event($locked, $retry ? 'processing_retry_scheduled' : 'processing_'.$status->value, $outcome->blockerCode?->value);
        });
    }

    private function event(ReceiptProcessingRun $run, string $event, ?string $reason = null): void
    {
        SolarCaseEvent::firstOrCreate(['event_key' => $run->id.':'.$run->attempts.':'.$event], [
            'solar_case_id' => $run->solar_case_id,
            'event' => $event,
            'reason_code' => $reason,
            'created_at' => now(),
        ]);
    }
}
