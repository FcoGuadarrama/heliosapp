<?php

declare(strict_types=1);

namespace App\Domain\Processing;

final class UnavailableReceiptProcessor implements ReceiptProcessor
{
    public function process(ReceiptSource $source): ProcessingOutcome
    {
        return new ProcessingOutcome(ProcessingStatus::Blocked, ProcessingBlocker::ReaderUnavailable);
    }
}
