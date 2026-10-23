<?php

use App\Domain\Hsp\GridTile;
use App\Domain\Hsp\InvalidHspData;
use App\Domain\Hsp\NasaClimatologyParser;
use Tests\TestCase;

uses(TestCase::class);

it('parses a recorded real NASA response and preserves original evidence', function () {
    $raw = file_get_contents(base_path('tests/Fixtures/Hsp/nasa-region-2026-10-23.json'));
    $result = (new NasaClimatologyParser)->parse($raw, new GridTile(-101, 20));

    expect($result->metadata['start'])->toBe('20010101')
        ->and($result->metadata['end'])->toBe('20251231')
        ->and($result->metadata['units'])->toBe('kW-hr/m^2/day')
        ->and($result->cells['-100.5:20.5']['ANN'])->toBe(5.8886)
        ->and($result->raw)->toBe($raw);
});

it('rejects invalid units fill values incomplete months and unsupported grids', function (string $fault) {
    $data = json_decode(file_get_contents(base_path('tests/Fixtures/Hsp/nasa-region-2026-10-23.json')), true);
    match ($fault) {
        'units' => $data['parameters']['ALLSKY_SFC_SW_DWN']['units'] = 'MJ/m2/day',
        'fill' => $data['features'][0]['properties']['parameter']['ALLSKY_SFC_SW_DWN']['ANN'] = -999,
        'missing month' => $data['features'][0]['properties']['parameter']['ALLSKY_SFC_SW_DWN']['FEB'] = null,
        'coordinates' => $data['features'][0]['geometry']['coordinates'][0] = -100.25,
        'outside tile' => $data['features'][0]['geometry']['coordinates'][0] = -110.5,
        'duplicate cell' => $data['features'][] = $data['features'][0],
        'period' => $data['header']['end'] = '20200101',
        'source' => $data['header']['sources'] = ['UNKNOWN'],
        'numeric string' => $data['features'][0]['properties']['parameter']['ALLSKY_SFC_SW_DWN']['ANN'] = '5.0',
    };
    (new NasaClimatologyParser)->parse(json_encode($data, JSON_THROW_ON_ERROR), new GridTile(-101, 20));
})->with(['units', 'fill', 'missing month', 'coordinates', 'outside tile', 'duplicate cell', 'period', 'source', 'numeric string'])->throws(InvalidHspData::class);

it('rejects malformed JSON', function () {
    (new NasaClimatologyParser)->parse('{', new GridTile(-101, 20));
})->throws(InvalidHspData::class);
