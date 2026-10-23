<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

interface ClimatologySource
{
    public function fetch(GridTile $tile): NasaTile;
}
