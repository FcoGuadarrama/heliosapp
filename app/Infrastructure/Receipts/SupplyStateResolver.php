<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Infrastructure\Hsp\StateGrid;
use Illuminate\Support\Str;

final class SupplyStateResolver
{
    public function __construct(private StateGrid $grid) {}

    public function code(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }
        $normalized = Str::lower(Str::ascii(trim($text)));
        foreach ($this->grid->states as $state) {
            if ($normalized === Str::lower(Str::ascii($state->name))) {
                return $state->code;
            }
        }

        return null;
    }
}
