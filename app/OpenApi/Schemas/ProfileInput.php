<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
    ],
    required: ['name', 'email'],
)]
final class ProfileInput {}
