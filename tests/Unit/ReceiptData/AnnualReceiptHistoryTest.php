<?php

use App\Domain\ReceiptData\AnnualReceiptHistory;
use App\Domain\ReceiptData\DTO\ReceiptPayload;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

function consumptionPayload(array $history = [], array $current = []): ReceiptPayload
{
    return ReceiptPayload::fromArray([
        'current_period' => [...['period_start' => '2026-07-01', 'period_end' => '2026-08-30', 'consumption_kwh' => 600], ...$current],
        'consumption_history' => $history,
    ]);
}

test('averages all 22 months plus the current period using actual covered days', function () {
    $end = CarbonImmutable::parse('2026-07-01');
    $history = [];
    for ($index = 0; $index < 11; $index++) {
        $start = $end->subDays(60);
        $history[] = ['period_start' => $start->toDateString(), 'period_end' => $end->toDateString(), 'consumption_kwh' => 600];
        $end = $start;
    }
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload($history));

    expect($selection['kwh'])->toHaveCount(12)
        ->and($selection['actual_days'])->toBe(720)
        ->and($selection['daily_kwh'])->toBe(10.0)
        ->and($selection['annual_kwh'])->toBe(3650.0);
});

test('weights unequal periods and uses history shorter than one year', function () {
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload([
        ['period_start' => '2026-06-01', 'period_end' => '2026-07-01', 'consumption_kwh' => 600],
    ]));

    expect($selection['actual_days'])->toBe(90)
        ->and($selection['daily_kwh'])->toEqualWithDelta(1200 / 90, 0.000000001);
});

test('counts duplicate history and the current period only once', function () {
    $row = ['period_start' => '2026-05-02', 'period_end' => '2026-07-01', 'consumption_kwh' => 1200];
    $current = ['period_start' => '2026-07-01', 'period_end' => '2026-08-30', 'consumption_kwh' => 600];
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload([$row, $row, $current]));

    expect($selection['kwh'])->toHaveCount(2)
        ->and($selection['actual_days'])->toBe(120)
        ->and($selection['daily_kwh'])->toBe(15.0);
});

test('does not count gaps as days with zero consumption', function () {
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload([
        ['period_start' => '2026-01-01', 'period_end' => '2026-01-31', 'consumption_kwh' => 300],
    ]));

    expect($selection['actual_days'])->toBe(90)->and($selection['daily_kwh'])->toBe(10.0);
});

test('excludes incomplete history with an explicit reason instead of inventing dates', function () {
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload([
        ['period_start' => '2026-05-01', 'consumption_kwh' => 900],
    ]));

    expect($selection['daily_kwh'])->toBe(10.0)
        ->and($selection['excluded_indices'])->toBe([0])
        ->and($selection['reason'])->toBe('partial_available_periods');
});

test('retains a zero consumption period in the weighted average', function () {
    $selection = (new AnnualReceiptHistory)->select(consumptionPayload([
        ['period_start' => '2026-05-02', 'period_end' => '2026-07-01', 'consumption_kwh' => 0],
    ]));

    expect($selection['actual_days'])->toBe(120)->and($selection['daily_kwh'])->toBe(5.0);
});

test('requires review for overlapping or contradictory periods', function (array $row) {
    (new AnnualReceiptHistory)->select(consumptionPayload([$row]));
})->with([
    'overlap' => [['period_start' => '2026-06-15', 'period_end' => '2026-07-15', 'consumption_kwh' => 300]],
    'same period different consumption' => [['period_start' => '2026-07-01', 'period_end' => '2026-08-30', 'consumption_kwh' => 700]],
    'reversed dates' => [['period_start' => '2026-06-01', 'period_end' => '2026-05-01', 'consumption_kwh' => 300]],
    'negative consumption' => [['period_start' => '2026-05-02', 'period_end' => '2026-07-01', 'consumption_kwh' => -1]],
    'impossible date' => [['period_start' => '2026-02-30', 'period_end' => '2026-04-01', 'consumption_kwh' => 300]],
    'printed duration contradicts dates' => [['period_start' => '2026-05-02', 'period_end' => '2026-07-01', 'days' => 30, 'consumption_kwh' => 300]],
])->throws(ValidationException::class);

test('does not produce a daily average without a verifiable duration', function () {
    (new AnnualReceiptHistory)->select(consumptionPayload([], ['period_start' => null, 'period_end' => null]));
})->throws(ValidationException::class);
