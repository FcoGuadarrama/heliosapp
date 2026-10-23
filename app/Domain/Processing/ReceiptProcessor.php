<?php

declare(strict_types=1);

namespace App\Domain\Processing;

interface ReceiptProcessor
{
    public function process(ReceiptSource $source): ProcessingOutcome;
}
