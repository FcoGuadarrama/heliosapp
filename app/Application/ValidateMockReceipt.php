<?php

namespace App\Application;

use App\Domain\Intake\Contracts\ReceiptReader;
use App\Domain\ReceiptData\ReceiptImportValidator;
use App\Models\ReceiptIntake;
use App\Models\ReceiptProcessingRun;
use App\Models\SolarCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ValidateMockReceipt
{
    public function __construct(private ReceiptReader $reader, private ReceiptImportValidator $validator) {}

    public function __invoke(ReceiptIntake $intake, string $fixture): void
    {
        DB::transaction(function () use ($intake, $fixture): void {
            if ($intake->solar_case_id !== null) {
                SolarCase::query()->lockForUpdate()->findOrFail($intake->solar_case_id);
            }
            $locked = ReceiptIntake::query()->lockForUpdate()->findOrFail($intake->id);
            if (ReceiptProcessingRun::where('receipt_intake_id', $locked->id)->exists()) {
                throw ValidationException::withMessages(['fixture_identifier' => 'Un recibo con análisis real no admite simulaciones.']);
            }
            if ($locked->raw_payload !== null) {
                throw ValidationException::withMessages(['fixture_identifier' => 'Este recibo ya tiene una simulación registrada.']);
            }
            $raw = $this->reader->readFixture($fixture);
            $result = $this->validator->validateJson($raw);
            $locked->update([
                'reader_identifier' => $this->reader->identifier(),
                'reader_version' => $this->reader->version(),
                'fixture_identifier' => $fixture,
                'raw_payload' => $raw,
                'validation_result' => $result->toArray(),
                'status' => 'mock_'.$result->status->value,
            ]);
        });
    }
}
