<?php

declare(strict_types=1);

use App\Domain\ReceiptData\ReceiptImportValidator;
use Tests\TestCase;
use Tests\Unit\ReceiptData\ReceiptFixtures as F;

uses(TestCase::class);

/**
 * Las 18 reglas de la tabla §4 de docs/cfe-receipt-schema.md.
 *
 * Cada caso construye dos payloads casi idénticos a partir de
 * ReceiptFixtures::base() (que, por sí solo, no dispara ninguna regla): uno
 * que dispara el código bajo prueba y otro que no.
 */
function codesFor(array $payload): array
{
    $result = (new ReceiptImportValidator)->validateDecoded($payload);

    return array_map(
        fn ($violation) => $violation->code->value,
        [...$result->errors, ...$result->warnings],
    );
}

/**
 * Construye `consumption_history` con periodos bimestrales consecutivos sin
 * huecos ni traslapes, a partir de una lista de kWh.
 *
 * @param  list<int>  $kwhs
 * @return list<array<string, mixed>>
 */
function historyFromKwh(array $kwhs): array
{
    $start = new DateTimeImmutable('2026-01-15');
    $periods = [];

    foreach ($kwhs as $kwh) {
        $end = $start->modify('+59 days');

        $periods[] = [
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
            'days' => 60,
            'consumption_kwh' => $kwh,
            'amount_mxn' => 1000,
            'source' => 'receipt_chart',
        ];

        $start = $end->modify('+1 day');
    }

    return $periods;
}

test('MALFORMED_JSON se dispara con un cuerpo que no es JSON válido', function () {
    $rejected = (new ReceiptImportValidator)->validateJson('{ "schema_version": "1.0", ');
    expect(array_map(fn ($e) => $e->code->value, $rejected->errors))->toContain('MALFORMED_JSON');
    expect($rejected->status->value)->toBe('rejected');

    $validJson = json_encode(F::base());
    $accepted = (new ReceiptImportValidator)->validateJson($validJson);
    expect(array_map(fn ($e) => $e->code->value, $accepted->errors))->not->toContain('MALFORMED_JSON');
});

test('UNSUPPORTED_SCHEMA_VERSION se dispara con una versión de esquema no soportada', function () {
    expect(codesFor(F::with('schema_version', '2.0')))->toContain('UNSUPPORTED_SCHEMA_VERSION');
    expect(codesFor(F::base()))->not->toContain('UNSUPPORTED_SCHEMA_VERSION');
});

test('NEGATIVE_CONSUMPTION se dispara con un consumo negativo', function () {
    expect(codesFor(F::with('current_period.consumption_kwh', -10)))->toContain('NEGATIVE_CONSUMPTION');
    expect(codesFor(F::base()))->not->toContain('NEGATIVE_CONSUMPTION');
});

test('NEGATIVE_AMOUNT se dispara con un importe negativo', function () {
    expect(codesFor(F::with('current_period.amount_mxn', -100)))->toContain('NEGATIVE_AMOUNT');
    expect(codesFor(F::base()))->not->toContain('NEGATIVE_AMOUNT');
});

test('INVALID_PERIOD se dispara cuando period_end no es posterior a period_start', function () {
    expect(codesFor(F::with('current_period.period_end', '2026-07-01')))->toContain('INVALID_PERIOD');
    expect(codesFor(F::base()))->not->toContain('INVALID_PERIOD');
});

test('FUTURE_PERIOD se dispara cuando period_end es posterior a hoy', function () {
    expect(codesFor(F::with('current_period.period_end', '2027-06-01')))->toContain('FUTURE_PERIOD');
    expect(codesFor(F::base()))->not->toContain('FUTURE_PERIOD');
});

test('IMPOSSIBLE_PERIOD_LENGTH se dispara cuando la duración está fuera de 20-75 días', function () {
    expect(codesFor(F::with('current_period.period_end', '2026-07-20')))->toContain('IMPOSSIBLE_PERIOD_LENGTH');
    expect(codesFor(F::base()))->not->toContain('IMPOSSIBLE_PERIOD_LENGTH');
});

test('PERIOD_DAYS_MISMATCH se dispara cuando "days" difiere en más de 2 días de lo calculado', function () {
    expect(codesFor(F::with('current_period.days', 10)))->toContain('PERIOD_DAYS_MISMATCH');
    expect(codesFor(F::base()))->not->toContain('PERIOD_DAYS_MISMATCH');
});

test('TIER_SUM_MISMATCH se dispara cuando la suma de escalones difiere del consumo total en más de 1%', function () {
    $mismatched = F::base();
    $mismatched['current_period']['consumption_by_tier'][0]['kwh'] = 100;
    expect(codesFor($mismatched))->toContain('TIER_SUM_MISMATCH');
    expect(codesFor(F::base()))->not->toContain('TIER_SUM_MISMATCH');
});

test('CONCEPT_SUM_MISMATCH se dispara cuando la suma de conceptos difiere del importe en más de 1%', function () {
    $mismatched = F::base();
    $mismatched['current_period']['concepts'][0]['amount_mxn'] = 10;
    expect(codesFor($mismatched))->toContain('CONCEPT_SUM_MISMATCH');
    expect(codesFor(F::base()))->not->toContain('CONCEPT_SUM_MISMATCH');
});

test('TOTAL_MISMATCH se dispara cuando el total del recibo difiere del importe del periodo en más de 1%', function () {
    expect(codesFor(F::with('receipt.total_amount_mxn', 10.00)))->toContain('TOTAL_MISMATCH');
    expect(codesFor(F::base()))->not->toContain('TOTAL_MISMATCH');
});

test('OVERLAPPING_HISTORY se dispara cuando dos periodos del historial se traslapan', function () {
    $overlapping = F::base();
    $overlapping['consumption_history'][1]['period_start'] = '2026-04-01';
    expect(codesFor($overlapping))->toContain('OVERLAPPING_HISTORY');
    expect(codesFor(F::base()))->not->toContain('OVERLAPPING_HISTORY');
});

test('los periodos históricos consecutivos pueden compartir fecha de corte sin traslaparse', function () {
    $adjacent = F::base();
    $adjacent['consumption_history'][1]['period_start'] = $adjacent['consumption_history'][0]['period_end'];

    expect(codesFor($adjacent))->not->toContain('OVERLAPPING_HISTORY');

    $adjacent['consumption_history'][1] = $adjacent['consumption_history'][0];
    expect(codesFor($adjacent))->toContain('OVERLAPPING_HISTORY');
});

test('HISTORY_GAP se dispara cuando hay un hueco mayor a 10 días entre periodos consecutivos', function () {
    $withGap = F::base();
    $withGap['consumption_history'][1]['period_start'] = '2026-06-01';
    expect(codesFor($withGap))->toContain('HISTORY_GAP');
    expect(codesFor(F::base()))->not->toContain('HISTORY_GAP');
});

test('CONSUMPTION_OUTLIER se dispara cuando un periodo se desvía más de 3 sigma del resto', function () {
    $withOutlier = F::base();
    $withOutlier['consumption_history'] = historyFromKwh([500, 495, 505, 510, 498, 5000]);
    expect(codesFor($withOutlier))->toContain('CONSUMPTION_OUTLIER');

    $withoutOutlier = F::base();
    $withoutOutlier['consumption_history'] = historyFromKwh([500, 495, 505, 510, 498, 502]);
    expect(codesFor($withoutOutlier))->not->toContain('CONSUMPTION_OUTLIER');
});

test('UNKNOWN_TARIFF se dispara cuando la clave de tarifa no está en el catálogo conocido', function () {
    expect(codesFor(F::with('service.tariff_code', 'ZZZ-NO-EXISTE')))->toContain('UNKNOWN_TARIFF');
    expect(codesFor(F::base()))->not->toContain('UNKNOWN_TARIFF');
});

test('MISSING_CRITICAL_FIELD se dispara cuando falta un campo crítico del contrato §3', function () {
    expect(codesFor(F::with('current_period.consumption_kwh', null)))->toContain('MISSING_CRITICAL_FIELD');
    expect(codesFor(F::base()))->not->toContain('MISSING_CRITICAL_FIELD');
});

test('UNKNOWN_FIELD se dispara con un campo no contemplado por el esquema', function () {
    $withUnknown = F::base();
    $withUnknown['loyalty_program_points'] = 42;
    expect(codesFor($withUnknown))->toContain('UNKNOWN_FIELD');
    expect(codesFor(F::base()))->not->toContain('UNKNOWN_FIELD');
});

test('LOW_CONFIDENCE se dispara cuando la confianza de un campo crítico es menor a 0.70', function () {
    $lowConfidence = F::base();
    $lowConfidence['extraction']['confidence']['service.tariff_code'] = 0.5;
    expect(codesFor($lowConfidence))->toContain('LOW_CONFIDENCE');
    expect(codesFor(F::base()))->not->toContain('LOW_CONFIDENCE');
});
