<?php

declare(strict_types=1);

namespace App\Domain\Processing;

enum ProcessingStatus: string
{
    case Received = 'received';
    case Queued = 'queued';
    case Running = 'running';
    case Blocked = 'blocked';
    case Failed = 'failed';
    case Completed = 'completed';
}
