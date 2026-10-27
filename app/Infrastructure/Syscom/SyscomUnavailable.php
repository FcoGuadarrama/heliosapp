<?php

declare(strict_types=1);

namespace App\Infrastructure\Syscom;

use RuntimeException;

final class SyscomUnavailable extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('Syscom unavailable: '.$reason);
    }
}
