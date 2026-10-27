<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'HistoryPeriod',
    type: 'object',
    properties: [
        new OA\Property(property: 'period_start', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'period_end', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'consumption_kwh', type: 'number', nullable: true, minimum: 0),
        new OA\Property(property: 'amount_mxn', type: 'number', nullable: true, minimum: 0),
        new OA\Property(property: 'days', type: 'integer', nullable: true, minimum: 1, maximum: 75),
    ],
)]
final class HistoryPeriod {}
