<?php

use App\Domain\Solar\ConsumptionAverage;
use App\Domain\Solar\InterconnectionCalculator;
use App\Domain\Solar\InterconnectionInput;

it('dimensions a current bill using independent Syscom reference values', function () {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(600, 60, 5, 550));

    expect($result->dailyConsumptionKwh)->toBe(10.0)
        ->and($result->requiredKwp)->toEqualWithDelta(2.5974025974, 0.000000001)
        ->and($result->minimumInverterW)->toBe(2165)
        ->and($result->moduleCount)->toBe(5)
        ->and($result->selectedKwp)->toBe(2.75)
        ->and($result->consumptionSource)->toBe('current_period')
        ->and($result->microinverterCount)->toBeNull();
});

it('uses history only at the 330 day boundary', function (int $days, string $source, float $daily) {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(1000, $days, 5, 550, [110, 220, 330, 440, 550, 660]));

    expect($result->consumptionSource)->toBe($source)
        ->and($result->dailyConsumptionKwh)->toEqualWithDelta($daily, 0.000000001);
})->with([
    '324 days: current period' => [54, 'current_period', 18.5185185185],
    '330 days: weighted average, not peak' => [55, 'history', 7.0],
]);

it('normalizes history beyond 400 days to 365 like the bundle', function (int $days, float $coverage, float $daily) {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(100, $days, 5, 550, [730, 730]));

    expect($result->historyDays)->toBe($coverage)
        ->and($result->dailyConsumptionKwh)->toEqualWithDelta($daily, 0.000000001);
})->with([[200, 400.0, 3.65], [201, 365.0, 4.0]]);

it('rounds module quantities upward and counts multi-module microinverters', function (int $capacity, int $units) {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(600, 60, 5, 550, modulesPerMicroinverter: $capacity));

    expect($result->moduleCount)->toBe(5)
        ->and($result->microinverterCount)->toBe($units);
})->with([[1, 5], [2, 3], [4, 2], [6, 1]]);

it('keeps an exact module boundary without adding a module', function () {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(15.4, 1, 5, 500));

    expect($result->requiredKwp)->toBe(4.0)
        ->and($result->moduleCount)->toBe(8)
        ->and($result->minimumInverterW)->toBe(3334);
});

it('accepts zero consumption in a historic period', function () {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(0, 60, 5, 550, [0, 600, 600, 600, 600, 1200]));

    expect($result->dailyConsumptionKwh)->toBe(10.0);
});

it('rejects invalid inputs instead of supplying defaults', function (float $kwh, int $days, float $hsp, float $watts, array $history, ?int $capacity) {
    (new InterconnectionCalculator)->calculate(new InterconnectionInput($kwh, $days, $hsp, $watts, $history, $capacity));
})->with([
    [0.0, 60, 5.0, 550.0, [], null],
    [-1.0, 60, 5.0, 550.0, [], null],
    [600.0, 0, 5.0, 550.0, [], null],
    [600.0, 60, 0.0, 550.0, [], null],
    [600.0, 60, 5.0, 0.0, [], null],
    [600.0, 60, NAN, 550.0, [], null],
    [INF, 60, 5.0, 550.0, [], null],
    [600.0, 60, 5.0, 550.0, [-1.0], null],
    [600.0, 60, 5.0, 550.0, [INF], null],
    [600.0, 60, 5.0, 550.0, [], 0],
    [1.0e300, 1, 0.0001, 550.0, [], null],
])->throws(InvalidArgumentException::class);

it('dimensions from verified consumption coverage without renormalizing 22 months to a year', function () {
    $average = new ConsumptionAverage(7200, 720);
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(
        600, 60, 5, 550, consumptionAverage: $average,
    ));

    expect($result->dailyConsumptionKwh)->toBe(10.0)
        ->and($result->historyDays)->toBe(720.0)
        ->and($result->consumptionSource)->toBe('verified_periods')
        ->and($result->requiredKwp)->toEqualWithDelta(2.5974025974, 0.000000001)
        ->and($result->moduleCount)->toBe(5);
});

it('rejects invalid verified consumption coverage', function (float $kwh, int $days) {
    new ConsumptionAverage($kwh, $days);
})->with([[600.0, 0], [-1.0, 60], [0.0, 60], [INF, 60], [NAN, 60]])
    ->throws(InvalidArgumentException::class);
