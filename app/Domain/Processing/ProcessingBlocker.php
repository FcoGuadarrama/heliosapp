<?php

declare(strict_types=1);

namespace App\Domain\Processing;

enum ProcessingBlocker: string
{
    case ReaderUnavailable = 'receipt_reader_unavailable';
    case ProspectUnavailable = 'prospect_unavailable';
    case ProcessingError = 'processing_error';
}
