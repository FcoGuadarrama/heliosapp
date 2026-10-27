<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QuoteDraftInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'calculation_id', type: 'integer', minimum: 1),
        new OA\Property(
            property: 'submission_id',
            type: 'string',
            format: 'uuid',
            description: 'UUID idempotente por expediente.',
        ),
        new OA\Property(property: 'confirmed', type: 'boolean', enum: [true]),
        new OA\Property(
            property: 'lines',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'product_id', type: 'string', pattern: '^[1-9][0-9]{0,11}$'),
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 1000),
                ],
                required: ['product_id', 'quantity'],
            ),
            minItems: 1,
            maxItems: 20,
            description: 'IDs distintos. El servidor reconsulta precios; no acepta precio unitario del navegador.',
        ),
    ],
    required: ['calculation_id', 'submission_id', 'confirmed', 'lines'],
)]
final class QuoteDraftInput {}
