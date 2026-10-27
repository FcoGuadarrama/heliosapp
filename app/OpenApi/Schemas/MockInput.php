<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MockInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'fixture_identifier', type: 'string', enum: ['complete', 'partial', 'incoherent', 'malformed']),
    ],
    required: ['fixture_identifier'],
)]
final class MockInput {}
