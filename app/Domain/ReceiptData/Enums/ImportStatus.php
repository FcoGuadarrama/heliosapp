<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\Enums;

/**
 * Estado resultante de un import de recibo CFE (contrato §5).
 *
 * `Accepted` nunca se deriva automáticamente: lo asigna una persona después de
 * revisar un import en `NeedsReview`.
 */
enum ImportStatus: string
{
    case Rejected = 'rejected';
    case NeedsReview = 'needs_review';
    case Validated = 'validated';
    case Accepted = 'accepted';
}
