<?php

declare(strict_types=1);

namespace App\Domain\Processing;

enum ProcessingBlocker: string
{
    case ReaderUnavailable = 'receipt_reader_unavailable';
    case ProspectUnavailable = 'prospect_unavailable';
    case ProcessingError = 'processing_error';
    case ReadingFailed = 'receipt_reading_failed';
    case ReceiptNeedsReview = 'receipt_needs_review';
    case UnreadableReceipt = 'receipt_unreadable';
    case LocationRequired = 'supply_state_required';
    case HspUnavailable = 'hsp_unavailable';
}
