# Documentación navegable con Laravel Swagger

El enlace **Documentation** del sidebar abre `/documentation` en otra pestaña.
L5-Swagger sirve Swagger UI con una guía del proyecto y la referencia OpenAPI
de los endpoints existentes. UI, esquema y assets requieren sesión y correo
verificado. La documentación es de consulta, sin ejecutar mutaciones del CRM.

Incluye módulos, recorrido del recibo al borrador, arquitectura, lectura PDF/OCR,
contrato CFE, fórmulas, Syscom/IVA, permisos, CSRF, respuestas Inertia frente a
JSON, operación de cola y limitaciones. Identifica las funciones propuestas
que todavía no existen y la revisión obligatoria actual del lector `source`.
La petición de eliminar esa obligación general queda pendiente de implementar;
esta integración de documentación no cambia la lectura ni los cálculos.

## Mantener y generar

Los atributos PHP de `app/OpenApi` son la fuente del esquema. Están separados
en guía general, endpoints y componentes por área. Al cambiar rutas, requests
o respuestas, actualiza los atributos correspondientes y ejecuta:

```sh
php artisan l5-swagger:generate
```

Genera `storage/api-docs/helios.json` y `helios.yaml`, fuera de Git. En cada
instalación o despliegue, genera después de instalar dependencias y configurar
Laravel. El esquema JSON se sirve autenticado desde
`/documentation/specification`; la UI ofrece enlace de descarga. Los assets
se sirven desde el paquete instalado, sin CDN ni validador externo.

`composer setup` incluye la generación inicial; `composer docs:generate` permite
repetirla. El middleware `DocumentationNavigation` fuerza navegación completa
cuando se llega desde Inertia, incluido el retorno del login a esta página Blade.

`config/l5-swagger.php` define rutas, middleware y escaneo limitado a
`app/OpenApi`. No se regenera por request. La configuración usa rutas relativas
y servidor `/` para funcionar en el origen actual sin incrustar el dominio local.

Las rutas de negocio conservan sus permisos y CSRF. La documentación no añade
API Bearer ni activa el contrato futuro `/api/v1`. La autenticación avanzada
de Fortify/passkeys sigue gestionada por esos paquetes; aquí se documenta
su entrada desde los ajustes, no se redefine su protocolo.

## Validación

```sh
php artisan test --compact --filter=DocumentationTest
```

Las pruebas generan el esquema en un directorio temporal, comprueban acceso
autenticado/verificado, assets, JSON/YAML, requisitos de revisión y que cada
operación documentada exista en el router de Laravel. Los ejemplos y la guía
no contienen recibos reales, credenciales ni contactos de clientes.

Referencias: [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger),
[swagger-php](https://zircote.github.io/swagger-php/).
