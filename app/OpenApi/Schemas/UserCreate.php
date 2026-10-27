<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserCreate',
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password', writeOnly: true),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', writeOnly: true),
        new OA\Property(property: 'role', type: 'string', description: 'Nombre de un rol existente.'),
    ],
    required: ['name', 'email', 'password', 'password_confirmation', 'role'],
)]
final class UserCreate {}
