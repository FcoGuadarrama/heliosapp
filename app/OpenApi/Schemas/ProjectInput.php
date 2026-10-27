<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProjectInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'street', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'exterior_number', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'neighborhood', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'municipality', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'state', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true, pattern: '^[0-9]{5}$'),
        new OA\Property(property: 'client_id', type: 'integer', minimum: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['draft', 'data_received', 'analyzing', 'analyzed', 'quoted', 'proposal_sent', 'won', 'lost', 'archived'],
        ),
        new OA\Property(property: 'installation_type', type: 'string', enum: ['roof', 'ground', 'carport', 'other']),
        new OA\Property(property: 'property_type', type: 'string', nullable: true),
        new OA\Property(property: 'latitude', type: 'number', nullable: true, minimum: -90, maximum: 90),
        new OA\Property(property: 'longitude', type: 'number', nullable: true, minimum: -180, maximum: 180),
        new OA\Property(property: 'objectives', type: 'array', items: new OA\Items, nullable: true),
        new OA\Property(property: 'property_data', type: 'array', items: new OA\Items, nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'assigned_user_id', type: 'integer', nullable: true),
    ],
    required: ['client_id', 'name', 'status', 'installation_type'],
)]
final class ProjectInput {}
