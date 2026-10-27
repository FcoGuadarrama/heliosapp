<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/engineering/interconnection',
    operationId: 'get_engineering_interconnection',
    summary: 'Abrir dimensionador manual',
    tags: ['Ingeniería'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/engineering/interconnection',
    operationId: 'post_engineering_interconnection',
    summary: 'Calcular interconexión',
    tags: ['Ingeniería'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 30 solicitudes por minuto.

Devuelve Engineering/Interconnection con props.result (InterconnectionResult). Fórmulas Syscom; PR 0.77 y relación DC/AC 1.2. El dimensionador requiere module_w explícito. No selecciona equipos ni valida compatibilidad eléctrica.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/InterconnectionInput'))),
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/intakes/{intake}/catalog',
    operationId: 'get_intakes_intake_catalog',
    summary: 'Buscar equipos Syscom',
    tags: ['Cotizador'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 15 solicitudes por minuto.

Consulta oficial de solo lectura, MXN sin IVA. Precio de lista, no costo. Hasta 20 resultados por página. Máximo 10 palabras en query.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)), new OA\Parameter(name: 'query', in: 'query', required: true, description: 'Entre 2 y 120 caracteres.', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Página entre 1 y 1000; predeterminado 1.', schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'JSON sin envoltura data. Cache-Control: no-store, private.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/CatalogResponse'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 503, description: 'Recepción, lector o proveedor no disponible.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/intakes/{intake}/quote-drafts',
    operationId: 'post_intakes_intake_quote_drafts',
    summary: 'Guardar versión de borrador comercial',
    tags: ['Cotizador'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 5 solicitudes por minuto.

Requiere cálculo vigente, expediente completado y revisión humana actual. Reconsulta precios y congela snapshot. Un UUID repetido devuelve el mismo borrador. Precio unitario half-up a centavos, cantidades, subtotal y después IVA configurado (16% local). Sin instalación, compatibilidad pendiente. No reserva stock ni publica propuesta.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/QuoteDraftInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
final class EngineeringEndpoints {}
