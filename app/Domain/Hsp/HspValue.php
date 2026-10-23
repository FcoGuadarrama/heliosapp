<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

final readonly class HspValue
{
    /** @param array<string, float> $monthly
     * @param  array<string, string>  $source
     */
    public function __construct(
        public string $stateCode,
        public string $stateName,
        public float $annual,
        public array $monthly,
        public string $catalogVersion,
        public string $methodology,
        public array $source,
        public string $publishedAt,
        public string $expiresAt,
    ) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return [...get_object_vars($this), 'unit' => 'h/day', 'scope' => 'state_horizontal_climatology'];
    }
}
