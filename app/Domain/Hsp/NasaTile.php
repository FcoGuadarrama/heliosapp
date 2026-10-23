<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

final readonly class NasaTile
{
    /** @param array<string, string> $metadata
     * @param  array<string, array<string, float>>  $cells
     */
    public function __construct(public array $metadata, public array $cells, public string $raw) {}
}
