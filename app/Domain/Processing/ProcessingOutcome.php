<?php

declare(strict_types=1);

namespace App\Domain\Processing;

use InvalidArgumentException;

final readonly class ProcessingOutcome
{
    public function __construct(
        public ProcessingStatus $status,
        public ?ProcessingBlocker $blockerCode = null,
        public bool $simulated = false,
    ) {
        if (! in_array($status, [ProcessingStatus::Blocked, ProcessingStatus::Completed], true)
            || ($status === ProcessingStatus::Blocked) !== ($blockerCode !== null)) {
            throw new InvalidArgumentException('Invalid processing outcome.');
        }
    }
}
