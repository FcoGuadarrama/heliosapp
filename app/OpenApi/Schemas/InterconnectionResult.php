<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InterconnectionResult',
    type: 'object',
    properties: [
        new OA\Property(property: 'motorVersion', type: 'string'),
        new OA\Property(property: 'consumptionSource', type: 'string'),
        new OA\Property(property: 'historyDays', type: 'number'),
        new OA\Property(property: 'dailyConsumptionKwh', type: 'number'),
        new OA\Property(property: 'requiredKwp', type: 'number'),
        new OA\Property(property: 'minimumInverterW', type: 'integer'),
        new OA\Property(property: 'moduleCount', type: 'integer', nullable: true),
        new OA\Property(property: 'selectedKwp', type: 'number', nullable: true),
        new OA\Property(property: 'microinverterCount', type: 'integer', nullable: true),
    ],
)]
final class InterconnectionResult {}
