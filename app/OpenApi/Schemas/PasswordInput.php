<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PasswordInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'current_password', type: 'string', format: 'password', writeOnly: true),
        new OA\Property(property: 'password', type: 'string', format: 'password', writeOnly: true),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', writeOnly: true),
    ],
    required: ['current_password', 'password', 'password_confirmation'],
)]
final class PasswordInput {}
