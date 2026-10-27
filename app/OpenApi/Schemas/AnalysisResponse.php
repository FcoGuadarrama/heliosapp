<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AnalysisResponse',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'processing',
            type: 'string',
            enum: ['received', 'queued', 'running', 'blocked', 'failed', 'completed'],
        ),
        new OA\Property(
            property: 'reason',
            type: 'string',
            nullable: true,
            description: 'Código de bloqueo/fallo; no equivale al estado de lectura.',
        ),
        new OA\Property(property: 'can_process', type: 'boolean'),
        new OA\Property(property: 'revision', type: 'integer', minimum: 0),
        new OA\Property(property: 'has_reading', type: 'boolean'),
        new OA\Property(property: 'upload_count', type: 'integer'),
        new OA\Property(property: 'can_replace', type: 'boolean'),
        new OA\Property(property: 'source_verified', type: 'boolean'),
        new OA\Property(property: 'result', ref: '#/components/schemas/InterconnectionResult', nullable: true),
        new OA\Property(property: 'calculation_id', type: 'integer', nullable: true),
        new OA\Property(
            property: 'quote_draft',
            type: 'object',
            nullable: true,
            description: 'Última versión del borrador para el cálculo: id, version, snapshot y created_at.',
        ),
        new OA\Property(property: 'hsp', type: 'object', nullable: true, description: 'Proveniencia HSP usada en el cálculo.'),
        new OA\Property(
            property: 'validation',
            type: 'object',
            properties: [
                new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'warnings', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'extraction_warnings', type: 'array', items: new OA\Items(type: 'object')),
            ],
        ),
        new OA\Property(
            property: 'form',
            type: 'object',
            nullable: true,
            description: 'Datos editables del recibo; no expone respuesta cruda ni domicilio. Campos de ReceiptReviewInput excepto reason y confirmed.',
        ),
    ],
)]
final class AnalysisResponse {}
