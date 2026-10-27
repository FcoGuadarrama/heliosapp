<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingStatus;
use App\Domain\ReceiptData\DTO\DocumentData;
use App\Domain\ReceiptData\NormalizeReceipt;
use App\Domain\ReceiptData\ReadingAssessment;
use App\Infrastructure\Receipts\SupplyStateResolver;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\ReceiptReading;
use App\Models\ReceiptReview;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReviewReceiptReading
{
    public function __construct(
        private ReadingAssessment $assessment,
        private NormalizeReceipt $normalizer,
        private SupplyStateResolver $states,
    ) {}

    public function __invoke(ReceiptReading $reading, User $actor, string $json, string $reason, int $expectedRevision): ReceiptReview
    {
        return DB::transaction(function () use ($reading, $actor, $json, $reason, $expectedRevision): ReceiptReview {
            $source = ReceiptIntake::findOrFail($reading->receipt_intake_id);
            SolarCase::query()->lockForUpdate()->findOrFail($source->solar_case_id);
            $locked = ReceiptReading::query()->lockForUpdate()->findOrFail($reading->id);
            $intake = ReceiptIntake::query()->lockForUpdate()->findOrFail($locked->receipt_intake_id);
            if (! $actor->is_active || ! $actor->can('process', $intake)) {
                throw new AuthorizationException;
            }
            $run = ReceiptProcessingRun::where('receipt_intake_id', $intake->id)->lockForUpdate()->first();
            if ($run !== null && in_array($run->status, [ProcessingStatus::Queued, ProcessingStatus::Running], true)) {
                throw ValidationException::withMessages(['review' => 'Espera a que termine el análisis antes de corregir datos.']);
            }
            if (ReceiptIntake::where('solar_case_id', $intake->solar_case_id)->max('id') !== $intake->id) {
                throw ValidationException::withMessages(['review' => 'Este recibo fue sustituido por una segunda carga.']);
            }
            $latest = ReceiptReview::where('receipt_reading_id', $locked->id)->max('revision') ?? 0;
            if ($latest !== $expectedRevision || mb_strlen(trim($reason)) < 10 || mb_strlen($reason) > 1000 || strlen($json) > 262144) {
                throw ValidationException::withMessages(['review' => 'Revisión desactualizada o motivo no válido (10–1000 caracteres).']);
            }
            $validation = $this->assessment->validate($json);
            $payload = $validation->payload;
            if ($validation->isRejected() || $validation->missingCriticalFields !== [] || $payload === null
                || ! in_array($payload->extraction->status, ['complete', 'partial'], true)
                || trim($payload->service->tariffCode ?? '') === '') {
                throw ValidationException::withMessages(['review' => 'Corrige los errores y los datos críticos antes de aceptar.']);
            }
            $original = json_decode($locked->payload_json, true, flags: JSON_THROW_ON_ERROR);
            if ($payload->document->toArray() !== DocumentData::fromArray($original['document'])->toArray()) {
                throw ValidationException::withMessages(['review' => 'La revisión no puede cambiar el documento de origen.']);
            }
            $review = ReceiptReview::create([
                'receipt_reading_id' => $locked->id, 'created_by' => $actor->id, 'revision' => $latest + 1,
                'reason' => trim($reason), 'payload_json' => $json, 'validation_result' => $validation->toArray(),
                'normalization' => [...$this->normalizer->normalize($payload),
                    'supply_state_code' => $this->states->code($payload->service->serviceAddress->state)],
            ]);
            $intake->update(['status' => 'accepted']);
            if ($run !== null) {
                $run->update(['status' => ProcessingStatus::Blocked, 'error_code' => 'receipt_needs_review']);
            }
            SolarCase::whereKey($intake->solar_case_id)->update(['calculation_status' => 'pending',
                'location_status' => $review->normalization['supply_state_code'] === null ? 'pending' : 'state_confirmed',
                ...($run === null ? [] : ['processing_status' => ProcessingStatus::Blocked, 'blocker_code' => 'receipt_needs_review']),
            ]);
            SolarCaseEvent::create(['solar_case_id' => $intake->solar_case_id, 'created_by' => $actor->id,
                'event_key' => 'receipt-review:'.$review->id, 'event' => 'receipt_accepted', 'created_at' => now()]);

            return $review;
        });
    }
}
