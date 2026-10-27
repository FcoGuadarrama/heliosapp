<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/intakes/create',
    operationId: 'get_intakes_create',
    summary: 'Formulario para recibir CFE',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'client_id', in: 'query', required: false, description: 'Prospecto seleccionado; también requiere clients.view.', schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/intakes',
    operationId: 'post_intakes',
    summary: 'Recibir recibo y vincular prospecto',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 10 solicitudes por minuto.

Con client_id usa el prospecto existente; de otro modo crea uno. Valida consentimiento. Conserva archivo privado y expediente, y encola si el lector está habilitado. Guardar no implica haber terminado el análisis.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/IntakeInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 503, description: 'Recepción, lector o proveedor no disponible.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/intakes/{intake}/file',
    operationId: 'get_intakes_intake_file',
    summary: 'Descargar recibo original',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'Archivo privado como descarga; Cache-Control: no-store, private.', content: new OA\MediaType(mediaType: 'application/octet-stream', schema: new OA\Schema(type: 'string', format: 'binary'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/intakes/{intake}/analysis',
    operationId: 'get_intakes_intake_analysis',
    summary: 'Consultar análisis y borrador vigente',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.view`; las políticas también verifican el alcance del registro.

Consultar periódicamente solo mientras processing sea queued o running. No devuelve texto OCR completo ni credenciales.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'JSON sin envoltura data. Cache-Control: no-store, private.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/AnalysisResponse'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/intakes/{intake}/analyze',
    operationId: 'post_intakes_intake_analyze',
    summary: 'Solicitar reintento de procesamiento',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 10 solicitudes por minuto.

Reencola el expediente. La lectura persistida se reutiliza: no fuerza una nueva extracción del mismo archivo.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 503, description: 'Recepción, lector o proveedor no disponible.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/intakes/{intake}/review',
    operationId: 'post_intakes_intake_review',
    summary: 'Guardar revisión y continuar cálculo',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 10 solicitudes por minuto.

Estado actual: source exige confirmación humana y entidad de suministro. Crea revisión auditable y reencola; no modifica la evidencia original. Cambiar esta política está pendiente.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ReceiptReviewInput'))),
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
#[OA\Post(
    path: '/intakes/{intake}/replacement',
    operationId: 'post_intakes_intake_replacement',
    summary: 'Adjuntar segunda carga por ilegibilidad',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 10 solicitudes por minuto.

Solo si el recibo se declaró ilegible y existe una sola carga. Máximo dos cargas por expediente.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/ReplacementInput'))),
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
#[OA\Post(
    path: '/intakes/{intake}/mock',
    operationId: 'post_intakes_intake_mock',
    summary: 'Validar fixture de simulación',
    tags: ['Recibos'],
    description: 'Permiso: `receipts.import`; las políticas también verifican el alcance del registro.

Límite: 10 solicitudes por minuto.

Herramienta sintética heredada. No lee el archivo ni sustituye una lectura real. No produce una propuesta real.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'intake', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/MockInput'))),
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
final class ReceiptEndpoints {}
