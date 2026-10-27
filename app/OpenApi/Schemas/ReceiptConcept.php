<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReceiptConcept',
    type: 'object',
    properties: [
        new OA\Property(property: 'code', type: 'string', nullable: true, maxLength: 100),
        new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 200),
        new OA\Property(property: 'amount_mxn', type: 'number', nullable: true, minimum: 0),
    ],
)]
final class ReceiptConcept {}
