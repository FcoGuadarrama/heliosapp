<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

use Illuminate\Support\Facades\Validator;
use JsonException;

final class NasaClimatologyParser
{
    public const PERIODS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'ANN'];

    public function parse(string $raw, GridTile $tile): NasaTile
    {
        try {
            $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidHspData('nasa_invalid_json');
        }
        if (! is_array($data)) {
            throw new InvalidHspData('nasa_invalid_structure');
        }
        $validator = Validator::make($data, [
            'type' => ['required', 'in:FeatureCollection'],
            'header.api.version' => ['required', 'string', 'max:30'],
            'header.sources' => ['required', 'array', 'size:1'],
            'header.sources.0' => ['required', 'in:SYN1DEG'],
            'header.start' => ['required', 'string', 'date_format:Ymd', 'regex:/^[0-9]{4}0101$/'],
            'header.end' => ['required', 'string', 'date_format:Ymd', 'after:header.start', 'regex:/^[0-9]{4}1231$/'],
            'header.fill_value' => ['required', 'numeric'],
            'parameters.ALLSKY_SFC_SW_DWN.units' => ['required', 'in:kW-hr/m^2/day'],
            'features' => ['required', 'array', 'list', 'min:1', 'max:100'],
            'features.*.geometry.type' => ['required', 'in:Point'],
            'features.*.geometry.coordinates' => ['required', 'array', 'min:2'],
            'features.*.geometry.coordinates.0' => ['required', 'numeric'],
            'features.*.geometry.coordinates.1' => ['required', 'numeric'],
            'features.*.properties.parameter.ALLSKY_SFC_SW_DWN' => ['required', 'array'],
        ]);
        if ($validator->fails()) {
            throw new InvalidHspData('nasa_invalid_structure_or_units');
        }
        $cells = [];
        foreach ($data['features'] as $feature) {
            [$longitude, $latitude] = $feature['geometry']['coordinates'];
            $key = $this->cellKey((float) $longitude, (float) $latitude, $tile);
            if (isset($cells[$key])) {
                throw new InvalidHspData('nasa_duplicate_cell');
            }
            $cells[$key] = $this->values($feature['properties']['parameter']['ALLSKY_SFC_SW_DWN'], (float) $data['header']['fill_value']);
        }

        return new NasaTile([
            'api_version' => $data['header']['api']['version'],
            'start' => $data['header']['start'], 'end' => $data['header']['end'],
            'source' => 'SYN1DEG', 'units' => 'kW-hr/m^2/day',
        ], $cells, $raw);
    }

    private function cellKey(float $longitude, float $latitude, GridTile $tile): string
    {
        if (! is_finite($longitude) || ! is_finite($latitude)
            || $longitude < $tile->longitude || $longitude >= $tile->longitude + 5
            || $latitude < $tile->latitude || $latitude >= $tile->latitude + 5
            || abs(($longitude - floor($longitude)) - 0.5) > 0.000001
            || abs(($latitude - floor($latitude)) - 0.5) > 0.000001) {
            throw new InvalidHspData('nasa_unexpected_grid');
        }

        return $longitude.':'.$latitude;
    }

    /** @param array<string, mixed> $values
     * @return array<string, float>
     */
    private function values(array $values, float $fill): array
    {
        $result = [];
        foreach (self::PERIODS as $period) {
            $value = $values[$period] ?? null;
            if ((! is_int($value) && ! is_float($value)) || ! is_finite($value)
                || (float) $value === $fill || $value < 0 || $value > 24 || ($period === 'ANN' && $value <= 0)) {
                throw new InvalidHspData('nasa_missing_or_invalid_irradiation');
            }
            $result[$period] = (float) $value;
        }

        return $result;
    }
}
