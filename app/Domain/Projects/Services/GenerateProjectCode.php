<?php

namespace App\Domain\Projects\Services;

use App\Models\Project;
use Illuminate\Database\UniqueConstraintViolationException;

class GenerateProjectCode
{
    private const MAX_RETRIES = 5;

    public function __invoke(): string
    {
        $year = date('Y');
        $retries = 0;

        while ($retries < self::MAX_RETRIES) {
            $sequence = $this->getNextSequence($year);
            $code = "PRY-{$year}-".str_pad($sequence, 4, '0', STR_PAD_LEFT);

            if ($this->tryInsertCode($code)) {
                return $code;
            }

            $retries++;
        }

        throw new \RuntimeException('Failed to generate unique project code after '.self::MAX_RETRIES.' attempts.');
    }

    private function getNextSequence(string $year): int
    {
        $lastProject = Project::whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING(code, -4) AS UNSIGNED) DESC')
            ->first();

        if ($lastProject) {
            $lastSequence = (int) substr($lastProject->code, -4);

            return $lastSequence + 1;
        }

        return 1;
    }

    private function tryInsertCode(string $code): bool
    {
        try {
            // We check if the code already exists without creating anything
            return ! Project::where('code', $code)->exists();
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }
}
