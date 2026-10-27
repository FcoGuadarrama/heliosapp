<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReplacementInput',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'receipt',
            type: 'string',
            format: 'binary',
            description: 'PDF, JPEG, PNG o WebP. Límite HTTP: intake.max_file_kb. El extractor admite hasta 20 MiB y 6 páginas; imágenes hasta 24 MP.',
        ),
    ],
    required: ['receipt'],
)]
final class ReplacementInput {}
