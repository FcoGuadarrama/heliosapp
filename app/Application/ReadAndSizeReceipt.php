<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Hsp\HspProvider;
use App\Domain\Processing\ProcessingBlocker;
use App\Domain\Processing\ProcessingOutcome;
use App\Domain\Processing\ProcessingStatus;
use App\Domain\Processing\ReceiptProcessor;
use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\AnnualReceiptHistory;
use App\Domain\ReceiptData\DTO\ReceiptPayload;
use App\Domain\ReceiptData\NormalizeReceipt;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use App\Domain\Solar\InterconnectionCalculator;
use App\Domain\Solar\InterconnectionInput;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReview;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReadAndSizeReceipt implements ReceiptProcessor
{
    public function __construct(private ReadReceiptFile $read, private HspProvider $hsp,
        private InterconnectionCalculator $calculator, private AnnualReceiptHistory $annualHistory) {}

    public function process(ReceiptSource $source): ProcessingOutcome
    {
        if (! config('receipt_reader.enabled')) {
            return $this->blocked(ProcessingBlocker::ReaderUnavailable);
        }
        $intake = ReceiptIntake::findOrFail($source->intakeId);
        try {
            $reading = ($this->read)($intake);
        } catch (ReceiptReadingFailure $failure) {
            return $this->blocked(match ($failure->reason) {
                'reader_disabled', 'reader_unreachable', 'reader_timeout', 'reader_service_error' => ProcessingBlocker::ReaderUnavailable,
                default => ProcessingBlocker::ReadingFailed,
            });
        }
        $review = ReceiptReview::where('receipt_reading_id', $reading->id)->latest('revision')->first();
        if ($review === null && $reading->status !== 'validated') {
            return $this->blocked($reading->status === 'unreadable' ? ProcessingBlocker::UnreadableReceipt : ProcessingBlocker::ReceiptNeedsReview);
        }
        $normalized = $review->normalization ?? $reading->normalization;
        $payload = ReceiptPayload::fromArray(json_decode($review->payload_json ?? $reading->payload_json, true, flags: JSON_THROW_ON_ERROR));
        $state = $normalized['supply_state_code'] ?? null;
        if (! is_string($state) || SolarCase::findOrFail($intake->solar_case_id)->location_status !== 'state_confirmed') {
            return $this->blocked(ProcessingBlocker::LocationRequired);
        }
        $lookup = $this->hsp->forState($state);
        if ($lookup->value === null) {
            return $this->blocked(ProcessingBlocker::HspUnavailable);
        }
        $days = $normalized['current_period_days'] ?? null;
        if (! is_int($days) || $days <= 0 || $payload->currentPeriod->consumptionKwh === null || $payload->currentPeriod->consumptionKwh <= 0) {
            return $this->blocked(ProcessingBlocker::ReceiptNeedsReview);
        }
        try {
            $history = $this->annualHistory->select($payload);
        } catch (ValidationException) {
            return $this->blocked(ProcessingBlocker::ReceiptNeedsReview);
        }
        if ($history['reason'] === 'annual_history' && count($history['kwh']) * $days < 330) {
            return $this->blocked(ProcessingBlocker::ReceiptNeedsReview);
        }
        $input = new InterconnectionInput((float) $payload->currentPeriod->consumptionKwh, $days, $lookup->value->annual, null, $history['kwh']);
        $result = $this->calculator->calculate($input);
        DB::transaction(function () use ($intake, $reading, $review, $normalized, $lookup, $input, $result, $history): void {
            SolarCase::query()->lockForUpdate()->findOrFail($intake->solar_case_id);
            SolarCalculation::firstOrCreate(['receipt_reading_id' => $reading->id, 'review_revision' => $review->revision ?? 0], [
                'solar_case_id' => $intake->solar_case_id,
                'inputs' => ['current_kwh' => $input->currentConsumptionKwh, 'period_days' => $input->periodDays,
                    'history_kwh' => $input->historyKwh, 'module_power_w' => null, 'normalization_version' => NormalizeReceipt::VERSION,
                    'history_actual_days' => $normalized['history_actual_days'] ?? null,
                    'history_policy' => AnnualReceiptHistory::VERSION, 'annual_selection' => $history,
                    'hsp' => $lookup->value->snapshot()],
                'result' => $result->toArray(),
            ]);
            $intake->update(['status' => 'analyzed']);
            SolarCase::whereKey($intake->solar_case_id)->update(['calculation_status' => 'completed']);
        });

        return new ProcessingOutcome(ProcessingStatus::Completed);
    }

    private function blocked(ProcessingBlocker $reason): ProcessingOutcome
    {
        return new ProcessingOutcome(ProcessingStatus::Blocked, $reason);
    }
}
