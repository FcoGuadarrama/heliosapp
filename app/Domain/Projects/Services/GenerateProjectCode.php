<?php

namespace App\Domain\Projects\Services;

use App\Models\Project;

class GenerateProjectCode
{
    public function __invoke(): string
    {
        $prefix = 'PRY-'.now()->format('Y').'-';
        $sequence = 0;
        // Include deleted records: codes remain reserved for their history.
        foreach (Project::withTrashed()->where('code', 'like', $prefix.'%')->pluck('code') as $code) {
            $suffix = substr($code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $sequence = max($sequence, (int) $suffix);
            }
        }

        return $prefix.sprintf('%04d', $sequence + 1);
    }
}
