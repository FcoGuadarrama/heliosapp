<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

final readonly class StateArea
{
    /** @param array<string, float> $weights */
    public function __construct(public string $code, public string $name, public array $weights) {}
}
