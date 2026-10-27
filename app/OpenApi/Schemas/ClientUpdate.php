<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClientUpdate',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'type',
            type: 'string',
            nullable: true,
            enum: ['residential', 'commercial', 'industrial', 'agricultural'],
        ),
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'contact_name', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 20),
        new OA\Property(property: 'rfc', type: 'string', nullable: true, maxLength: 13),
        new OA\Property(property: 'street', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'exterior_number', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'interior_number', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'neighborhood', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'municipality', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'state', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true, pattern: '^[0-9]{5}$'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ],
    required: ['name'],
)]
final class ClientUpdate {}
