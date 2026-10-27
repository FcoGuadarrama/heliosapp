<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CompanyInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'company_name', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'legal_name', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'responsible_name', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'responsible_title', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'rfc', type: 'string', nullable: true, maxLength: 13),
        new OA\Property(
            property: 'logo',
            type: 'string',
            format: 'binary',
            nullable: true,
            description: 'JPEG, PNG, SVG o WebP; hasta 2048 KiB. Para multipart PATCH usar POST con _method=PATCH.',
        ),
        new OA\Property(
            property: 'address',
            type: 'object',
            properties: [
                new OA\Property(property: 'street', type: 'string', nullable: true),
                new OA\Property(property: 'number', type: 'string', nullable: true),
                new OA\Property(property: 'interior_number', type: 'string', nullable: true),
                new OA\Property(property: 'neighborhood', type: 'string', nullable: true),
                new OA\Property(property: 'municipality', type: 'string', nullable: true),
                new OA\Property(property: 'state', type: 'string', nullable: true),
                new OA\Property(property: 'postal_code', type: 'string', nullable: true),
            ],
            nullable: true,
        ),
        new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 20),
        new OA\Property(property: 'email', type: 'string', nullable: true, format: 'email'),
        new OA\Property(property: 'website', type: 'string', nullable: true, format: 'uri'),
        new OA\Property(property: 'primary_color', type: 'string', nullable: true, pattern: '^#[0-9A-Fa-f]{6}$'),
        new OA\Property(property: 'secondary_color', type: 'string', nullable: true, pattern: '^#[0-9A-Fa-f]{6}$'),
        new OA\Property(property: 'timezone', type: 'string', description: 'Identificador válido de DateTimeZone.'),
        new OA\Property(property: 'default_currency', type: 'string', pattern: '^[A-Z]{3}$'),
        new OA\Property(property: 'proposal_validity_days', type: 'integer', minimum: 1, maximum: 365),
    ],
    required: ['timezone', 'default_currency', 'proposal_validity_days'],
)]
final class CompanyInput {}
