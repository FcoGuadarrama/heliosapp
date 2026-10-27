<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/clients',
    operationId: 'get_clients',
    summary: 'Listar registros',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Paginación, 15 registros por página.', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Búsqueda por nombre, email o teléfono.', schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/clients/create',
    operationId: 'get_clients_create',
    summary: 'Formulario de alta',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.create`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/clients',
    operationId: 'post_clients',
    summary: 'Crear registro',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.create`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ClientCreate'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Get(
    path: '/clients/{client}',
    operationId: 'get_clients_client',
    summary: 'Consultar detalle',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/clients/{client}/edit',
    operationId: 'get_clients_client_edit',
    summary: 'Formulario de edición',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.update`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Patch(
    path: '/clients/{client}',
    operationId: 'patch_clients_client',
    summary: 'Actualizar registro',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.update`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ClientUpdate'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Delete(
    path: '/clients/{client}',
    operationId: 'delete_clients_client',
    summary: 'Eliminar registro',
    tags: ['Prospectos'],
    description: 'Permiso: `clients.delete`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'client', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Get(
    path: '/projects',
    operationId: 'get_projects',
    summary: 'Listar registros',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Paginación, 15 registros por página.', schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/projects/create',
    operationId: 'get_projects_create',
    summary: 'Formulario de alta',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.create`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/projects',
    operationId: 'post_projects',
    summary: 'Crear registro',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.create`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ProjectInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Get(
    path: '/projects/{project}',
    operationId: 'get_projects_project',
    summary: 'Consultar detalle',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/projects/{project}/edit',
    operationId: 'get_projects_project_edit',
    summary: 'Formulario de edición',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.update`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Patch(
    path: '/projects/{project}',
    operationId: 'patch_projects_project',
    summary: 'Actualizar registro',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.update`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ProjectInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Delete(
    path: '/projects/{project}',
    operationId: 'delete_projects_project',
    summary: 'Eliminar registro',
    tags: ['Proyectos'],
    description: 'Permiso: `projects.delete`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 404, description: 'Registro o archivo no encontrado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
final class CrmEndpoints {}
