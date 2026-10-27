<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/dashboard',
    operationId: 'get_dashboard',
    summary: 'Abrir dashboard',
    tags: ['Aplicación'],
    description: '',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/settings/profile',
    operationId: 'get_settings_profile',
    summary: 'Consultar perfil',
    tags: ['Ajustes'],
    description: '',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Patch(
    path: '/settings/profile',
    operationId: 'patch_settings_profile',
    summary: 'Actualizar perfil',
    tags: ['Ajustes'],
    description: '',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ProfileInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Delete(
    path: '/settings/profile',
    operationId: 'delete_settings_profile',
    summary: 'Eliminar cuenta propia',
    tags: ['Ajustes'],
    description: 'Requiere contraseña actual y salvaguardas de cuenta.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/DeleteProfileInput'))),
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
    path: '/settings/company',
    operationId: 'get_settings_company',
    summary: 'Consultar empresa',
    tags: ['Ajustes'],
    description: 'Permiso: `settings.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Patch(
    path: '/settings/company',
    operationId: 'patch_settings_company',
    summary: 'Actualizar empresa',
    tags: ['Ajustes'],
    description: 'Permiso: `settings.update`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/CompanyInput'))),
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
    path: '/settings/users',
    operationId: 'get_settings_users',
    summary: 'Listar usuarios y roles',
    tags: ['Usuarios'],
    description: 'Permiso: `users.view`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Post(
    path: '/settings/users',
    operationId: 'post_settings_users',
    summary: 'Crear usuario',
    tags: ['Usuarios'],
    description: 'Permiso: `users.create`; las políticas también verifican el alcance del registro.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/UserCreate'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
    ]
)]
#[OA\Patch(
    path: '/settings/users/{user}',
    operationId: 'patch_settings_users_user',
    summary: 'Actualizar usuario y rol',
    tags: ['Usuarios'],
    description: 'Permiso: `users.update`; las políticas también verifican el alcance del registro.

No permite cambiar el propio rol ni quitar el último propietario activo.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/UserUpdate'))),
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
#[OA\Patch(
    path: '/settings/users/{user}/toggle-active',
    operationId: 'patch_settings_users_user_toggle_active',
    summary: 'Alternar activación de usuario',
    tags: ['Usuarios'],
    description: 'Permiso: `users.update`; las políticas también verifican el alcance del registro.

No permite desactivarse a sí mismo ni desactivar al último propietario activo.',
    security: [['session' => []]],
    parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))],
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
    path: '/settings/security',
    operationId: 'get_settings_security',
    summary: 'Consultar seguridad de cuenta',
    tags: ['Ajustes'],
    description: 'Requiere confirmación reciente de contraseña. Passkeys y 2FA son gestionados por los paquetes de autenticación.',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Put(
    path: '/settings/password',
    operationId: 'put_settings_password',
    summary: 'Cambiar contraseña',
    tags: ['Ajustes'],
    description: 'Límite: 6 solicitudes por minuto.',
    security: [['session' => []]],
    requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/PasswordInput'))),
    responses: [
        new OA\Response(response: 302, description: 'Redirección web con mensaje flash. Un request Inertia PATCH/PUT/DELETE se convierte a 303.', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 303, description: 'Redirección después del envío (Inertia o formulario público).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 419, description: 'Token CSRF ausente, inválido o sesión vencida.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 422, description: 'Validación fallida: JSON con Accept: application/json; formulario CRM redirige con errores; embebido devuelve HTML.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: '#/components/schemas/ValidationError'))),
        new OA\Response(response: 429, description: 'Límite temporal excedido; reintentar después del plazo indicado.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
#[OA\Get(
    path: '/settings/appearance',
    operationId: 'get_settings_appearance',
    summary: 'Preferencias de apariencia',
    tags: ['Ajustes'],
    description: '',
    security: [['session' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Página HTML. Con X-Inertia: true, devuelve el documento de página Inertia (component, props, url y version).', content: new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 401, description: 'Sin sesión al solicitar JSON; en navegación HTML redirige a /login.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
        new OA\Response(response: 403, description: 'Sin permiso, verificación de correo pendiente o registro fuera de alcance.', content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'string'))),
    ]
)]
final class SettingsEndpoints {}
