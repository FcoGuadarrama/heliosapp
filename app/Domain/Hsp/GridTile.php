<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

final readonly class GridTile
{
    public function __construct(public int $longitude, public int $latitude) {}

    public function key(): string
    {
        return $this->longitude.':'.$this->latitude;
    }

    /** @return array<string, int|string> */
    public function query(): array
    {
        return [
            'parameters' => 'ALLSKY_SFC_SW_DWN', 'community' => 'RE', 'format' => 'JSON',
            'longitude-min' => $this->longitude, 'longitude-max' => $this->longitude + 5,
            'latitude-min' => $this->latitude, 'latitude-max' => $this->latitude + 5,
        ];
    }
}
