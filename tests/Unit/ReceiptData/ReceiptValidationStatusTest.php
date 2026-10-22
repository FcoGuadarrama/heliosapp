<?php

declare(strict_types=1);

use App\Domain\ReceiptData\ReceiptImportValidator;
use Tests\TestCase;
use Tests\Unit\ReceiptData\ReceiptFixtures as F;

uses(TestCase::class);

test('un payload sin errores ni advertencias queda validated', function () {
    $result = (new ReceiptImportValidator)->validateDecoded(F::base());

    expect($result->status->value)->toBe('validated');
    expect($result->errors)->toBe([]);
    expect($result->warnings)->toBe([]);
    expect($result->missingCriticalFields)->toBe([]);
});

test('un payload con al menos un error queda rejected', function () {
    $result = (new ReceiptImportValidator)->validateDecoded(
        F::with('current_period.consumption_kwh', -10),
    );

    expect($result->status->value)->toBe('rejected');
    expect($result->errors)->not->toBe([]);
});

test('un payload sin errores pero con advertencias o campos críticos faltantes queda needs_review', function () {
    $withWarning = (new ReceiptImportValidator)->validateDecoded(
        F::with('service.tariff_code', 'ZZZ-NO-EXISTE'),
    );

    expect($withWarning->status->value)->toBe('needs_review');
    expect($withWarning->errors)->toBe([]);

    $withMissingCritical = (new ReceiptImportValidator)->validateDecoded(
        F::with('current_period.consumption_kwh', null),
    );

    expect($withMissingCritical->status->value)->toBe('needs_review');
    expect($withMissingCritical->missingCriticalFields)->toContain('current_period.consumption_kwh');
});

test('un error y una advertencia simultáneos dan rejected, no needs_review', function () {
    $payload = F::base();
    $payload['current_period']['consumption_kwh'] = -10; // error
    $payload['service']['tariff_code'] = 'ZZZ-NO-EXISTE'; // warning

    $result = (new ReceiptImportValidator)->validateDecoded($payload);

    expect($result->status->value)->toBe('rejected');
});

test('accepted nunca se deriva automáticamente', function () {
    $result = (new ReceiptImportValidator)->validateDecoded(F::base());

    expect($result->status->value)->not->toBe('accepted');
});
