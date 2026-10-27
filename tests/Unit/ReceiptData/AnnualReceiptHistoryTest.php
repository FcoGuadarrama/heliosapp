<?php

use App\Domain\ReceiptData\AnnualReceiptHistory;
use App\Domain\ReceiptData\DTO\ReceiptPayload;
use App\Domain\Solar\InterconnectionCalculator;
use App\Domain\Solar\InterconnectionInput;
use Carbon\CarbonImmutable;
use Tests\TestCase;

uses(TestCase::class);

test('annual selection does not count two years of printed consumption as one year', function () {
    $end = CarbonImmutable::parse('2026-07-15');
    $history = [];
    for ($index = 0; $index < 11; $index++) {
        $start = $end->subDays(60);
        $history[] = ['period_start' => $start->toDateString(), 'period_end' => $end->toDateString(), 'consumption_kwh' => 600];
        $end = $start;
    }
    $payload = ReceiptPayload::fromArray(['current_period' => ['period_start' => '2026-07-15'], 'consumption_history' => $history]);
    $selection = (new AnnualReceiptHistory)->select($payload);
    expect($selection['kwh'])->toHaveCount(6)->and($selection['actual_days'])->toBe(360)
        ->and($selection['indices'])->toBe([0, 1, 2, 3, 4, 5]);
});

test('insufficient real history uses current consumption without inventing a full year', function () {
    $payload = ReceiptPayload::fromArray(['current_period' => ['period_start' => '2026-07-15'],
        'consumption_history' => [['period_start' => '2026-05-15', 'period_end' => '2026-07-15', 'consumption_kwh' => 600]]]);
    $selection = (new AnnualReceiptHistory)->select($payload);
    expect($selection['kwh'])->toBe([])->and($selection['reason'])->toBe('insufficient_history_use_current');
});

test('generic sizing returns kWp without assuming panel wattage or module count', function () {
    $result = (new InterconnectionCalculator)->calculate(new InterconnectionInput(600, 60, 5, null));
    expect($result->requiredKwp)->toBeGreaterThan(0)->and($result->minimumInverterW)->toBe(2165)
        ->and($result->moduleCount)->toBeNull()->and($result->selectedKwp)->toBeNull()->and($result->microinverterCount)->toBeNull();
});
