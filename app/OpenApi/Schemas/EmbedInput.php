<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EmbedInput',
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true, maxLength: 255, description: 'Requerido sin client_id.'),
        new OA\Property(
            property: 'email',
            type: 'string',
            nullable: true,
            format: 'email',
            description: 'Sin client_id, indicar email o teléfono.',
        ),
        new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 20),
        new OA\Property(
            property: 'receipt',
            type: 'string',
            format: 'binary',
            description: 'PDF, JPEG, PNG o WebP. Límite HTTP: intake.max_file_kb. El extractor admite hasta 20 MiB y 6 páginas; imágenes hasta 24 MP.',
        ),
        new OA\Property(property: 'privacy_consent', type: 'boolean', enum: [true]),
        new OA\Property(property: 'privacy_version', type: 'string', description: 'Versión vigente de intake.privacy_version.'),
        new OA\Property(
            property: 'form_token',
            type: 'string',
            maxLength: 4096,
            description: 'Token de un solo uso emitido en GET /embed/cfe.',
        ),
        new OA\Property(property: 'website', type: 'string', maxLength: 0, description: 'Honeypot: vacío.'),
    ],
    required: ['name', 'receipt', 'privacy_consent', 'privacy_version', 'form_token'],
)]
final class EmbedInput {}
