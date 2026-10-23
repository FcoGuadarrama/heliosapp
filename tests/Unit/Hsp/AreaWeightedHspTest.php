<?php

use App\Domain\Hsp\AreaWeightedHsp;
use App\Domain\Hsp\InvalidHspData;
use App\Domain\Hsp\NasaClimatologyParser;
use App\Domain\Hsp\StateArea;

it('weights the intersected territory instead of taking a capital or an unweighted mean', function () {
    $state = new StateArea('01', 'Synthetic', ['west' => 0.75, 'east' => 0.25]);
    $cells = ['west' => array_fill_keys(NasaClimatologyParser::PERIODS, 4.0), 'east' => array_fill_keys(NasaClimatologyParser::PERIODS, 8.0)];
    $cells['west']['ANN'] = 4.2;
    $cells['east']['ANN'] = 8.4;
    $result = (new AreaWeightedHsp)->calculate($state, $cells);

    expect($result['JAN'])->toBe(5.0)->and($result['ANN'])->toBe(5.25);
});

it('does not renormalize missing coverage or replace missing data', function (array $weights, array $cells) {
    (new AreaWeightedHsp)->calculate(new StateArea('01', 'Synthetic', $weights), $cells);
})->with([
    'no area' => [[], []],
    'incomplete area' => [['a' => 0.9], []],
    'missing cell' => [['a' => 1.0], []],
    'missing months' => [['a' => 1.0], ['a' => ['ANN' => 5.0]]],
    'fill value' => [['a' => 1.0], ['a' => array_fill_keys(NasaClimatologyParser::PERIODS, -999.0)]],
    'invalid weight' => [['a' => -1.0, 'b' => 2.0], []],
])->throws(InvalidHspData::class);
