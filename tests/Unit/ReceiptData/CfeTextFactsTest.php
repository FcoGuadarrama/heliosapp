<?php

use App\Infrastructure\Receipts\CfeTextFacts;
use App\Infrastructure\Receipts\ReceiptGrounding;

function cfePrintedText(): string
{
    return "Página 1:\nTARIFA:1CNO. MEDIDOR:PRUEBA\nNO HILOS:2\n"
        ."PERIODO FACTURADO:15 JUL 26-13 SEP 26\nEnergía (kWh) 1,520 1,000 520\n"
        ."Página 2:\ndel 15 MAY 26 al 15 JUL 26 480 $1,100.00 $1,100.00\n"
        ."del 15 MAR 26 al 15 MAY 26 450 $1,050.00 $1,050.00\n";
}

test('PDF facts separate adjacent labels, accumulated readings and consumption', function () {
    $facts = (new CfeTextFacts)->parse(cfePrintedText());
    expect($facts['verified'])->toBeTrue()
        ->and($facts['fields']['service.tariff_code'])->toBe('1C')
        ->and($facts['fields']['current_period.consumption_kwh'])->toBe(520.0)
        ->and($facts['fields']['current_period.period_start'])->toBe('2026-07-15')
        ->and($facts['fields']['current_period.period_end'])->toBe('2026-09-13')
        ->and($facts['fields']['service.phases'])->toBeNull()
        ->and($facts['fields']['current_period.days'])->toBeNull()
        ->and($facts['history'])->toHaveCount(2)
        ->and($facts['history'][0]['consumption_kwh'])->toBe(480.0)
        ->and($facts['history'][0]['amount_mxn'])->toBe(1100.0)
        ->and($facts['history'][0]['days'])->toBeNull()
        ->and($facts['evidence'][4]['page'])->toBe(2);
});

test('an unreadable historical row prevents claiming verified coverage', function () {
    $facts = (new CfeTextFacts)->parse(cfePrintedText()."del 15 ENE ilegible\n");
    expect($facts['verified'])->toBeFalse()->and($facts['history'])->toBeNull();
});

test('conflicting periods from multiple documents cannot be verified as one receipt', function () {
    $facts = (new CfeTextFacts)->parse(cfePrintedText()."TARIFA:1D\n");
    expect($facts['verified'])->toBeFalse()->and($facts['fields'])->not->toHaveKey('service.tariff_code');
});

test('impossible dates are not normalized into different printed dates', function () {
    $facts = (new CfeTextFacts)->parse(str_replace('15 JUL 26', '31 FEB 26', cfePrintedText()));
    expect($facts['verified'])->toBeFalse()->and($facts['fields'])->not->toHaveKey('current_period.period_start');
});

test('grounding preserves original input and explicitly records source discrepancies', function () {
    $original = ['service' => ['tariff_code' => '1CNO.', 'phases' => 2],
        'current_period' => ['period_start' => '2026-07-16', 'period_end' => '2026-09-13', 'consumption_kwh' => 1520, 'days' => 60],
        'consumption_history' => [], 'extraction' => ['status' => 'partial', 'warnings' => [], 'evidence' => [], 'missing_fields' => ['service.tariff_code']]];
    $result = (new ReceiptGrounding(new CfeTextFacts))->reconcile($original, cfePrintedText());
    expect($original['service']['tariff_code'])->toBe('1CNO.')
        ->and($result['verified'])->toBeTrue()
        ->and($result['payload']['service']['tariff_code'])->toBe('1C')
        ->and($result['payload']['service']['phases'])->toBeNull()
        ->and($result['payload']['consumption_history'])->toHaveCount(2)
        ->and(array_column($result['payload']['extraction']['warnings'], 'code'))->toContain('SOURCE_RECONCILED')
        ->and($result['payload']['extraction']['missing_fields'])->toBe([]);
});

test('image-only input remains explicitly unverified instead of being accepted on model evidence', function () {
    $result = (new ReceiptGrounding(new CfeTextFacts))->reconcile(['service' => [], 'current_period' => [],
        'extraction' => ['status' => 'partial']], null);
    expect($result['verified'])->toBeFalse()
        ->and($result['payload']['extraction']['warnings'][0]['code'])->toBe('SOURCE_UNVERIFIED');
});
