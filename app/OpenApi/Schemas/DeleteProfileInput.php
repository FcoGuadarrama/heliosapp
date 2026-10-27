<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeleteProfileInput',
    type: 'object',
    properties: [new OA\Property(property: 'password', type: 'string', format: 'password', writeOnly: true)],
    required: ['password'],
)]
final class DeleteProfileInput {}
