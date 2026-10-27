<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InterconnectionInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'current_kwh', type: 'number', minimum: 0, maximum: 1000000),
        new OA\Property(property: 'period_days', type: 'integer', minimum: 1, maximum: 366),
        new OA\Property(property: 'hsp', type: 'number', minimum: 0.1, maximum: 24),
        new OA\Property(property: 'module_w', type: 'number', minimum: 1, maximum: 10000),
        new OA\Property(
            property: 'history_kwh',
            type: 'array',
            items: new OA\Items(type: 'number', minimum: 0, maximum: 1000000),
            maxItems: 24,
        ),
        new OA\Property(property: 'modules_per_micro', type: 'integer', nullable: true, minimum: 1, maximum: 100),
    ],
    required: ['current_kwh', 'period_days', 'hsp', 'module_w', 'history_kwh'],
)]
final class InterconnectionInput {}
