<?php

declare(strict_types=1);

namespace App\Domain\Hsp;

interface HspProvider
{
    public function forState(string $stateCode): HspLookup;
}
