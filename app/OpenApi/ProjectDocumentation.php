<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Helios — Documentación del proyecto',
    description: <<<'MARKDOWN'
Helios es un CRM para recibir recibos CFE, analizar consumo, dimensionar
interconexión solar y preparar borradores comerciales con equipos de Syscom.
Esta referencia describe la implementación disponible, revisada el 27 de octubre
de 2026. Puedes filtrar los módulos, abrir sus operaciones y descargar OpenAPI.
La interfaz es de consulta: no ejecuta operaciones sobre los expedientes.

## Guía de uso

1. **Prospectos:** registra el contacto en Mis prospectos o selecciónalo al recibir CFE.
2. **Recibos:** carga PDF, JPEG, PNG o WebP con consentimiento y aviso vigente.
   El archivo permanece privado y vinculado al prospecto y a un expediente solar.
3. **Análisis:** la cola `receipts` extrae datos y guarda el resultado. La pantalla
   del prospecto consulta progreso mientras el trabajo está en cola o ejecutándose.
4. **Estado actual del lector:** el adaptador `source` exige cotejo humano en todos
   los recibos, incluso completos, y no extrae la entidad de suministro. El asesor
   confirma/corrige datos y entidad para continuar. El usuario pidió sustituir
   esta obligación por controles condicionales; **ese cambio aún está pendiente**.
5. **Dimensionamiento:** una revisión válida permite calcular consumo diario,
   kWp requeridos e inversor mínimo usando HSP por entidad. Si falta la potencia
   del panel, cantidades de paneles y microinversores permanecen nulas.
6. **Borrador:** desde el cálculo, busca modelos Syscom, agrega cantidades,
   confirma y guarda una versión con precios reconsultados por el servidor.

El **Dimensionador** del sidebar es una herramienta manual separada: necesita
consumo, días, HSP y watts por panel explícitos. No consulta automáticamente
el recibo ni selecciona un modelo de equipo.

## Módulos y alcance

| Módulo | Disponible |
| --- | --- |
| Prospectos | Alta, búsqueda, detalle, edición, eliminación y recibos asociados |
| Proyectos internos | Alta, código, estado, ubicación, asignación y edición |
| Recibos CFE | Recepción privada, lectura, revisión auditable y segunda carga por ilegibilidad |
| Ingeniería | Interconexión; consumo e histórico, HSP y resultados versionados |
| Cotizador asistido | Catálogo Syscom, selección manual y borradores inmutables por expediente |
| Empresa y usuarios | Identidad, preferencias, roles y activación según permisos |
| Embebido | Formulario público de contacto, archivo y consentimiento |

Cotizaciones generales, proveedores y costos/márgenes del sidebar siguen sin
módulo independiente. Aislado/híbrido, selección automática de equipos,
compatibilidad eléctrica completa, PDF de propuesta, correo y enlaces privados
de entrega **no están implementados**. Un borrador no es una propuesta publicada.

## Lectura PDF/OCR y datos

- `source`: `pdfinfo` y `pdftotext` leen PDFs digitales. Si el texto no es
  reconocible, o la entrada es imagen, utiliza OCR Apple Vision local.
- OCR: ejecutable Swift en almacenamiento privado; requiere macOS. Conserva
  páginas, cajas, texto y confianza. No usa una API OCR pagada.
- `ollama`: adaptador alternativo configurable; no es el lector local seleccionado.
- JSON CFE **1.0**: `document`, `receipt`, `service`, `current_period`,
  `consumption_history` y `extraction`. Los valores desconocidos son `null`.
  No se infieren fases de NO HILOS ni se presentan días calculados como impresos.
- El extractor limita archivos a 20 MiB, seis páginas e imágenes a 24 MP;
  la recepción HTTP además aplica `intake.max_file_kb`.
- La evaluación de desarrollo acertó 20/20 campos críticos y 55/55 filas de
  histórico en cinco recibos; hubo un error OCR en una lectura de medidor.
  Esas muestras no prueban precisión universal.

## Ingeniería y precios

Las fórmulas proceden del reverse engineering aceptado de Syscom: cobertura de
consumo de 100%, PR 0.77 y relación DC/AC 1.2. El histórico selecciona periodos
consecutivos recientes con cobertura real de 330–400 días; cobertura insuficiente
usa el periodo actual. Incoherencias, huecos y traslapes pueden bloquear el cálculo.
HSP se obtiene del catálogo local NASA POWER por entidad, con fuente y vigencia.
Esto no es un estudio de sombras, estructura o compatibilidad eléctrica.

El servidor consulta la API oficial Syscom con OAuth `client_credentials`.
Usa `precio_lista` en MXN sin IVA; debe confirmarse su equivalencia comercial
al precio público sugerido por familia. Nunca usa costo de distribuidor como
precio de venta ni recibe credenciales desde el navegador.

Cada borrador congela modelo, precio fuente, cantidades, existencias, fecha,
cálculo, revisión y actor. Redondea precio unitario half-up a centavos, multiplica
cantidades, suma subtotal y calcula IVA una vez. La configuración local acordada
es **16%**; si la tasa no está configurada, IVA y total quedan pendientes.
Versiones anteriores no se recalculan. Instalación excluida, compatibilidad
pendiente y existencias insuficientes/desconocidas se muestran expresamente.

## Autenticación y respuestas HTTP

Las rutas CRM usan **sesión Laravel**, verificación de correo cuando la ruta lo
exige, permisos Spatie y políticas sobre cada registro. No hay autenticación
Bearer ni una API `/api/v1` implementada. Inicia sesión en `/login`.

Las páginas devuelven HTML; con `X-Inertia: true` devuelven un documento Inertia,
no un recurso JSON convencional. Los formularios redirigen con mensajes flash
(302, o 303 para mutaciones Inertia). Solo análisis y catálogo devuelven JSON
directo. `Accept: application/json` cambia la representación de los errores,
no convierte las respuestas Inertia exitosas en una API REST.

Las mutaciones CRM requieren CSRF: cookie de sesión y `X-CSRF-TOKEN` vigente o
`X-XSRF-TOKEN` procedente de la cookie correspondiente. No se exceptúa CSRF.
La validación JSON devuelve 422 con `message` y `errors` por campo; navegación
HTML redirige al formulario. Sin sesión, JSON devuelve 401 y HTML va al login;
403 significa acceso denegado, 419 sesión/CSRF vencido y 429 límite temporal.

Roles y permisos se gestionan en usuarios; poder ver documentación no concede
permisos para ejecutar sus operaciones. Archivos, respuestas crudas y secretos
no se publican. Los identificadores de ejemplo son sintéticos.

## Cola y recuperación

Estados de procesamiento: `received`, `queued`, `running`, `blocked`, `failed`
y `completed`. Son distintos del estado de lectura, ubicación y revisión.
Los bloqueos incluyen `receipt_needs_review`, `receipt_unreadable`,
`supply_state_required`, `hsp_unavailable` y `receipt_reader_unavailable`.
Consulta `reason` y `validation` para investigar el motivo.

Reintentar no fuerza una nueva extracción si la lectura ya está persistida.
La revisión se añade al historial con control de versión. Una segunda carga
solo está disponible por ilegibilidad y no permite una tercera. Editar datos
invalida el cálculo anterior hasta obtener uno vigente.

## Operación para desarrollo

Stack: Laravel 13 / PHP 8.4, Vue 3, Inertia 3, TypeScript y Vite; base relacional,
archivos privados y trabajos en segundo plano. `Application` coordina casos de
uso; `Domain` contiene contratos y reglas; `Infrastructure` integra OCR, HSP
y Syscom; controladores autorizan/validan y Vue presenta el resultado.

```sh
composer install
npm install
php artisan migrate
npm run build
php artisan queue:work database --queue=receipts --tries=3 --timeout=840 --sleep=2
php artisan l5-swagger:generate
```

Configura `.env` según `.env.example`: `HELIOS_INTAKE_ENABLED`, aviso de privacidad,
`HELIOS_RECEIPT_READER_ENABLED`, driver, ejecutable OCR, almacenamiento y cola.
Syscom requiere `SYSCOM_CLIENT_ID` y `SYSCOM_CLIENT_SECRET`; IVA se configura con
`HELIOS_QUOTE_VAT_RATE`. Nunca publiques valores secretos. Después de configurar:
`php artisan config:cache`; reinicia workers para cargar código/configuración.
El timeout del worker es 840 s y `retry_after` de la cola debe ser mayor (960 s).

```sh
php artisan helios:syscom:probe
php artisan helios:receipts:extract /ruta/recibo.pdf --output=/ruta/privada/nuevo.json
php artisan test --compact
vendor/bin/phpstan analyse --debug --no-progress
npm run check
npm run types:check
```

La exportación crea un archivo privado nuevo, sin sobrescribir ni modificar CRM.
Para mantener esta documentación, edita atributos PHP en `app/OpenApi` y regenera
con `l5-swagger:generate` en cada entrega. JSON/YAML se generan en
`storage/api-docs`; no se regeneran durante requests ni se envían a validadores
externos. La UI y el esquema requieren sesión autenticada y correo verificado.

Documentación ampliada en el repositorio: `docs/README.md`, `docs/architecture.md`,
`docs/assisted-quote.md`, `docs/automatic-receipt-analysis.md`,
`docs/cfe-receipt-schema.md`, `docs/solar-engineering.md` y `docs/runtime.md`.
Los RFC y el contrato `/api/v1` describen también propuestas futuras:
no implican que existan esos endpoints.
MARKDOWN,
)]
#[OA\Server(url: '/', description: 'Mismo origen que Helios')]
#[OA\SecurityScheme(securityScheme: 'session', type: 'apiKey', in: 'cookie', name: 'laravel_session', description: 'Sesión iniciada en /login. El nombre real de cookie depende de SESSION_COOKIE; Swagger no permite pegar cookies HttpOnly. Las mutaciones requieren CSRF adicional.')]
#[OA\Tag(name: 'Aplicación', description: 'Entrada al CRM autenticado.')]
#[OA\Tag(name: 'Prospectos', description: 'Contactos, direcciones y recibos asociados.')]
#[OA\Tag(name: 'Proyectos', description: 'Seguimiento interno de instalaciones.')]
#[OA\Tag(name: 'Recibos', description: 'Recepción, lectura, consulta, revisión y recaptura.')]
#[OA\Tag(name: 'Ingeniería', description: 'Dimensionador determinista de interconexión.')]
#[OA\Tag(name: 'Cotizador', description: 'Catálogo y borradores versionados con precios Syscom.')]
#[OA\Tag(name: 'Embebido', description: 'Recepción pública aislada de la sesión CRM.')]
#[OA\Tag(name: 'Ajustes', description: 'Perfil, empresa, apariencia y seguridad.')]
#[OA\Tag(name: 'Usuarios', description: 'Gestión de usuarios y roles con salvaguardas de propietario.')]
final class ProjectDocumentation {}
