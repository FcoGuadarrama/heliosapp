<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use RuntimeException;

final class ReceiptReadingFailure extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('Receipt reading failed: '.$reason);
    }
}
