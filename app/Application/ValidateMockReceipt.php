<?php

namespace App\Application;

use App\Domain\Intake\Contracts\ReceiptReader;
use App\Domain\ReceiptData\ReceiptImportValidator;
use App\Models\ReceiptIntake;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ValidateMockReceipt
{
    public function __construct(private ReceiptReader $reader, private ReceiptImportValidator $validator) {}

    public function __invoke(ReceiptIntake $intake, string $fixture): void
    {
        DB::transaction(function () use ($intake, $fixture): void {
            $locked = ReceiptIntake::query()->lockForUpdate()->findOrFail($intake->id);
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
