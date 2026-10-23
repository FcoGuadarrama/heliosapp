<?php

declare(strict_types=1);

namespace App\Infrastructure\Hsp;

use App\Domain\Hsp\ClimatologySource;
use App\Domain\Hsp\GridTile;
use App\Domain\Hsp\NasaClimatologyParser;
use App\Domain\Hsp\NasaTile;
use Illuminate\Support\Facades\Http;

final class NasaClimatologyClient implements ClimatologySource
{
    public function __construct(private NasaClimatologyParser $parser) {}

    public function fetch(GridTile $tile): NasaTile
    {
        $response = Http::acceptJson()->connectTimeout(10)->timeout((int) config('hsp.http_timeout_seconds'))
            ->retry(2, 1000)->get((string) config('hsp.endpoint'), $tile->query())->throw();

        return $this->parser->parse($response->body(), $tile);
    }
}
