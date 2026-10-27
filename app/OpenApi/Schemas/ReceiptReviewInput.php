<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReceiptReviewInput',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'revision',
            type: 'integer',
            minimum: 0,
            description: 'Revisión esperada; evita sobrescribir una revisión concurrente.',
        ),
        new OA\Property(property: 'reason', type: 'string', minLength: 10, maxLength: 1000),
        new OA\Property(property: 'confirmed', type: 'boolean', enum: [true]),
        new OA\Property(property: 'tariff_code', type: 'string', maxLength: 16),
        new OA\Property(property: 'consumption_kwh', type: 'number', minimum: 0),
        new OA\Property(property: 'period_start', type: 'string', format: 'date'),
        new OA\Property(property: 'period_end', type: 'string', format: 'date', description: 'Posterior a period_start.'),
        new OA\Property(
            property: 'state_code',
            type: 'string',
            pattern: '^(0[1-9]|[12][0-9]|3[0-2])$',
            description: 'Clave INEGI de entidad de suministro (01–32).',
        ),
        new OA\Property(property: 'phases', type: 'integer', nullable: true, enum: [1, 2, 3]),
        new OA\Property(property: 'days', type: 'integer', nullable: true, minimum: 1, maximum: 75),
        new OA\Property(
            property: 'history',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/HistoryPeriod'),
            maxItems: 36,
        ),
        new OA\Property(
            property: 'concepts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ReceiptConcept'),
            maxItems: 36,
        ),
        new OA\Property(property: 'period_amount_mxn', type: 'number', nullable: true, minimum: 0),
        new OA\Property(property: 'total_amount_mxn', type: 'number', nullable: true, minimum: 0),
    ],
    required: [
        'revision',
        'reason',
        'confirmed',
        'tariff_code',
        'consumption_kwh',
        'period_start',
        'period_end',
        'state_code',
        'history',
        'concepts',
    ],
)]
final class ReceiptReviewInput {}
