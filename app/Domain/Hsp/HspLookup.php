<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

use InvalidArgumentException;

final readonly class HspLookup
{
    public function __construct(public ?HspValue $value, public ?string $unavailableReason = null)
    {
        if (($value === null) === ($unavailableReason === null)) {
            throw new InvalidArgumentException('HSP requires a value or an unavailability reason.');
        }
    }
}
