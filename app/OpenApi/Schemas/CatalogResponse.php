<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CatalogResponse',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'products',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'string'),
                    new OA\Property(property: 'model', type: 'string'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(
                        property: 'list_price',
                        type: 'string',
                        nullable: true,
                        description: 'precio_lista de Syscom, MXN sin IVA.',
                    ),
                    new OA\Property(property: 'stock', type: 'integer', nullable: true),
                ],
            ),
        ),
        new OA\Property(property: 'page', type: 'integer'),
        new OA\Property(property: 'pages', type: 'integer'),
    ],
)]
final class CatalogResponse {}
