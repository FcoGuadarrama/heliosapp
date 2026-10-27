<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Quotes\ListPriceDraft;
use App\Infrastructure\Syscom\SyscomCatalog;
use App\Models\EquipmentQuoteDraft;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\ReceiptReview;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateEquipmentQuoteDraft
{
    public function __construct(private SyscomCatalog $catalog, private ListPriceDraft $calculator) {}

    /** @param list<array{product_id: string, quantity: int}> $lines */
    public function __invoke(ReceiptIntake $intake, User $actor, int $calculationId, string $submissionId, array $lines): EquipmentQuoteDraft
    {
        $this->authorize($intake, $actor);
        $existing = EquipmentQuoteDraft::where('solar_case_id', $intake->solar_case_id)->where('submission_id', $submissionId)->first();
        if ($existing !== null) {
            return $existing;
        }
        if (count($lines) < 1 || count($lines) > 20) {
            throw ValidationException::withMessages(['quote' => 'Selecciona entre 1 y 20 productos.']);
        }
        $items = [];
        $products = $this->catalog->products(array_column($lines, 'product_id'));
        foreach ($lines as $index => $line) {
            $items[] = ['product' => $products[$index], 'quantity' => $line['quantity']];
        }
        $rate = config('quotes.vat_rate');
        $snapshot = $this->calculator->calculate($items, is_string($rate) && $rate !== '' ? $rate : null);

        return DB::transaction(function () use ($intake, $actor, $calculationId, $submissionId, $snapshot): EquipmentQuoteDraft {
            $case = SolarCase::query()->lockForUpdate()->findOrFail($intake->solar_case_id);
            $this->authorize($intake->fresh(), $actor);
            $existing = EquipmentQuoteDraft::where('solar_case_id', $case->id)->where('submission_id', $submissionId)->first();
            if ($existing !== null) {
                return $existing;
            }
            $reading = ReceiptReading::where('receipt_intake_id', $intake->id)->first();
            $revision = $reading === null ? null : (ReceiptReview::where('receipt_reading_id', $reading->id)->max('revision') ?? 0);
            $calculation = SolarCalculation::find($calculationId);
            if ($reading === null || $revision === null || $revision < 1 || $calculation === null || $calculation->receipt_reading_id !== $reading->id
                || $calculation->review_revision !== $revision || $case->calculation_status !== 'completed'
                || ReceiptIntake::where('solar_case_id', $case->id)->max('id') !== $intake->id) {
                throw ValidationException::withMessages(['quote' => 'El cálculo cambió o requiere revisión. Actualiza el expediente antes de cotizar.']);
            }

            return EquipmentQuoteDraft::create(['solar_case_id' => $case->id, 'solar_calculation_id' => $calculationId,
                'created_by' => $actor->id, 'submission_id' => $submissionId,
                'version' => (EquipmentQuoteDraft::where('solar_case_id', $case->id)->max('version') ?? 0) + 1,
                'snapshot' => $snapshot]);
        });
    }

    private function authorize(ReceiptIntake $intake, User $actor): void
    {
        $currentActor = $actor->fresh();
        if ($currentActor === null || ! $currentActor->is_active || ! $currentActor->can('process', $intake)) {
            throw new AuthorizationException;
        }
    }
}
