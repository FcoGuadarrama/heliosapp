<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/embed/cfe',
    operationId: 'get_embed_cfe',
    summary: 'Abrir formulario público embebible',
    tags: ['Embebido'],
    description: 'Límite: 30 solicitudes por minuto.

HTML con token de un solo uso y aviso de privacidad. Sin sesión ni cookies CRM. Solo orígenes permitidos pueden enmarcarlo.',
    security: [],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 503, description: 'Recepción, lector o proveedor no disponible.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/embed/cfe',
    operationId: 'post_embed_cfe',
    summary: 'Enviar contacto y recibo público',
    tags: ['Embebido'],
    description: 'Límite: 5 solicitudes por minuto.

Sin CSRF CRM: utiliza form_token, honeypot y consentimiento. Prohíbe client_id; no vincula por contacto a un prospecto preexistente. Redirige con 303 a /embed/cfe?submitted=1. No expone cálculo ni seguimiento público.',
    security: [],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/EmbedInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 503, description: 'Recepción, lector o proveedor no disponible.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
final class EmbedEndpoints {}
