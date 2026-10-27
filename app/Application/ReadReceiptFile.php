<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\NormalizeReceipt;
use App\Domain\ReceiptData\ReadingAssessment;
use App\Domain\ReceiptData\ReceiptValidationResult;
use App\Infrastructure\Receipts\SupplyStateResolver;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReadReceiptFile
{
    public function __construct(
        private FileReceiptReader $reader,
        private ReadingAssessment $assessment,
        private NormalizeReceipt $normalizer,
        private SupplyStateResolver $states,
    ) {}

    public function __invoke(ReceiptIntake $intake): ReceiptReading
    {
        return Cache::lock('receipt-reading:'.$intake->id, 900)->block(1, function () use ($intake): ReceiptReading {
            $existing = ReceiptReading::where('receipt_intake_id', $intake->id)->first();
            if ($existing !== null) {
                return $existing;
            }
            $fresh = $intake->fresh();
            if ($fresh === null || $fresh->client->trashed() || $fresh->raw_payload !== null) {
                throw ValidationException::withMessages(['receipt' => 'Este recibo no admite lectura real.']);
            }
            $reading = $this->reader->read(new ReceiptSource($fresh->id, $fresh->file_path, $fresh->mime_type));
            $validation = $this->assessment->validate($reading->payloadJson);
            [$normalization, $normalizationErrors] = $this->normalize($validation);
            $validationData = [...$validation->toArray(), 'normalization_errors' => $normalizationErrors,
                'source_verified' => $reading->sourceVerified];

            return DB::transaction(function () use ($fresh, $reading, $validation, $validationData, $normalization): ReceiptReading {
                SolarCase::query()->lockForUpdate()->findOrFail($fresh->solar_case_id);
                $locked = ReceiptIntake::query()->lockForUpdate()->findOrFail($fresh->id);
                if ($locked->client->trashed() || $locked->raw_payload !== null) {
                    throw ValidationException::withMessages(['receipt' => 'El recibo cambió durante la lectura.']);
                }
                $status = match (true) {
                    $validation->payload?->extraction->status === 'failed' => 'unreadable',
                    $validation->isRejected() => 'rejected',
                    $reading->sourceVerified && $normalization !== null && $validation->isValidated() && $this->hasCriticalEvidence($validation, $reading->pageCount) => 'validated',
                    default => 'needs_review',
                };
                $saved = ReceiptReading::create([
                    'receipt_intake_id' => $locked->id, 'model' => $reading->model,
                    'prompt_version' => $reading->promptVersion, 'page_count' => $reading->pageCount,
                    'raw_response' => $reading->rawResponse, 'payload_json' => $reading->payloadJson,
                    'validation_result' => $validationData, 'normalization' => $normalization, 'status' => $status,
                ]);
                $locked->update(['reader_identifier' => $validation->payload?->document->extractor->identifier ?? 'unknown', 'reader_version' => $reading->promptVersion,
                    'raw_payload' => $reading->payloadJson, 'validation_result' => $validationData, 'status' => $status]);
                SolarCaseEvent::create(['solar_case_id' => $locked->solar_case_id,
                    'event_key' => 'receipt-reading:'.$saved->id, 'event' => 'receipt_read',
                    'reason_code' => $status, 'created_at' => now()]);

                return $saved;
            });
        });
    }

    /** @return array{array<string, mixed>|null, array<string, list<string>>} */
    private function normalize(ReceiptValidationResult $validation): array
    {
        if ($validation->isRejected() || $validation->payload === null) {
            return [null, []];
        }
        try {
            return [[...$this->normalizer->normalize($validation->payload),
                'supply_state_code' => $this->states->code($validation->payload->service->serviceAddress->state)], []];
        } catch (ValidationException $exception) {
            return [null, $exception->errors()];
        }
    }

    private function hasCriticalEvidence(ReceiptValidationResult $validation, int $pages): bool
    {
        $extraction = $validation->payload?->extraction;
        $critical = ['service.tariff_code', 'current_period.consumption_kwh',
            'current_period.period_start', 'current_period.period_end'];
        if ($extraction === null || ! in_array($extraction->status, ['complete', 'partial'], true)
            || array_intersect($critical, $extraction->missingFields) !== []) {
            return false;
        }
        foreach ($extraction->warnings as $warning) {
            if ($warning->code === 'SOURCE_RECONCILED' && in_array($warning->field,
                [...$critical, 'service.phases', 'current_period.days', 'consumption_history', 'extraction.status'], true)) {
                continue;
            }
            if ($warning->code !== 'NOT_PRINTED' || $warning->field === null || in_array($warning->field, $critical, true)) {
                return false;
            }
        }
        $fields = [];
        foreach ($validation->payload->extraction->evidence as $evidence) {
            if ($evidence->field !== null && $evidence->page !== null && $evidence->page >= 1
                && $evidence->page <= $pages && trim($evidence->text ?? '') !== '') {
                $fields[] = $evidence->field;
            }
        }

        return array_diff($critical, $fields) === [];
    }
}
