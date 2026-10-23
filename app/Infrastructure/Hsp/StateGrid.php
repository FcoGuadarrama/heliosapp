<?php

declare(strict_types=1);

namespace App\Infrastructure\Hsp;

use App\Domain\Hsp\GridTile;
use App\Domain\Hsp\InvalidHspData;
use App\Domain\Hsp\StateArea;
use Illuminate\Support\Facades\Validator;

final readonly class StateGrid
{
    public string $raw;

    public string $hash;

    public string $methodology;

    /** @var list<StateArea> */
    public array $states;

    /** @var list<GridTile> */
    public array $tiles;

    public function __construct()
    {
        $raw = file_get_contents(resource_path('data/hsp/state-grid-v1.json'));
        if ($raw === false) {
            throw new InvalidHspData('sampling_unavailable');
        }
        $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            throw new InvalidHspData('invalid_sampling');
        }
        $this->validate($data);
        $states = [];
        $tiles = [];
        foreach ($data['states'] as $state) {
            $weights = [];
            foreach ($state['cells'] as $cell) {
                $longitude = (float) $cell['longitude'];
                $latitude = (float) $cell['latitude'];
                if (abs($longitude - floor($longitude) - 0.5) > 0.000001
                    || abs($latitude - floor($latitude) - 0.5) > 0.000001) {
                    throw new InvalidHspData('invalid_sampling_grid');
                }
                $key = $longitude.':'.$latitude;
                if (isset($weights[$key])) {
                    throw new InvalidHspData('duplicate_sampling_cell');
                }
                $weights[$key] = (float) $cell['weight'];
                $tile = new GridTile((int) (floor($longitude / 5) * 5), (int) (floor($latitude / 5) * 5));
                $tiles[$tile->key()] = $tile;
            }
            if (abs(array_sum($weights) - 1) > 0.00000001) {
                throw new InvalidHspData('incomplete_state_coverage');
            }
            $states[] = new StateArea($state['code'], $state['name'], $weights);
        }
        $this->raw = $raw;
        $this->hash = hash('sha256', $raw);
        $this->methodology = $data['methodology'];
        $this->states = $states;
        ksort($tiles);
        $this->tiles = array_values($tiles);
    }

    /** @param array<string, mixed> $data */
    private function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'methodology' => ['required', 'in:inegi-2025-ceres-1deg-area-v1'],
            'source_archive_sha256' => ['required', 'string', 'size:64'],
            'grid_degrees' => ['required', 'in:1'], 'area_crs' => ['required', 'in:EPSG:6933'],
            'states' => ['required', 'array', 'list', 'size:32'],
            'states.*.code' => ['required', 'string', 'regex:/^(0[1-9]|[12][0-9]|3[0-2])$/', 'distinct'],
            'states.*.name' => ['required', 'string'],
            'states.*.cells' => ['required', 'array', 'list', 'min:1'],
            'states.*.cells.*.longitude' => ['required', 'numeric', 'between:-120,-85'],
            'states.*.cells.*.latitude' => ['required', 'numeric', 'between:10,35'],
            'states.*.cells.*.weight' => ['required', 'numeric', 'gt:0', 'lte:1'],
        ]);
        if ($validator->fails() || ($data['source_archive_sha256'] ?? null) !== hash_file('sha256', resource_path('data/hsp/inegi-states-2025.geojson.gz'))) {
            throw new InvalidHspData('invalid_sampling_or_provenance');
        }
    }
}
