# Helios App — Arquitectura

> v0.3 · 2026-10-22 · **Single-tenant**. Supersede cualquier diseño multiempresa previo.

> Orden actualizado 2026-10-23 en [plan del proyecto](project-plan.md).
> Los contratos de esta página son objetivo salvo implementación identificada;
> el inventario y dependencias vigentes están en ese plan.

## 1. Forma general

Monolito modular Laravel. Una instalación por empresa compradora, con su propia base de
datos. Sin microservicios, sin tenancy, sin `company_id`.

```
Navegador (Vue 3 + Inertia 3)
        │
   Controladores HTTP  ← delgados: validan, autorizan, delegan
        │
   Servicios de aplicación (casos de uso)  ← ÚNICO lugar donde vive un flujo de negocio
        │
   Dominio (entidades, servicios deterministas, contratos)
        │
   Adaptadores (proveedores, IA, irradiación)   Persistencia (MySQL)   Colas
```

**Regla de dependencia:** `Http → Application → Domain`. El dominio nunca importa nada de
`App\Http`. Se verifica con pruebas de arquitectura de Pest, no con buena voluntad.

## 2. Estructura de carpetas

```
app/
├── Domain/
│   ├── Identity/            Usuarios, roles, permisos
│   ├── CompanySettings/     Configuración de la empresa (registro único)
│   ├── Clients/             Clientes finales
│   ├── Projects/            Proyectos solares
│   ├── ReceiptData/         Esquema JSON CFE, validación, lectores
│   ├── EnergyAnalysis/      Diagnóstico energético
│   ├── Solar/               Motor de interconexión actual; producción futura
│   ├── Suppliers/           Contrato SupplierProvider y adaptadores
│   ├── Catalog/             Catálogo técnico normalizado
│   ├── Compatibility/       Validación eléctrica determinista
│   ├── Quotation/           Cotizaciones y lista de materiales
│   ├── Pricing/             Costos, perfiles comerciales, márgenes, impuestos
│   ├── Proposals/           Armado de propuestas y documentos
│   ├── Advisor/             Orquestación del ingeniero IA
│   ├── Audit/               Historial y trazabilidad
│   └── Shared/              Value objects comunes (Money, Measure, Availability…)
├── Application/             Casos de uso, un archivo por caso
├── Http/{Controllers,Requests,Middleware}
├── Jobs/                    Trabajos en cola
├── Policies/
└── Models/                  Modelos Eloquent (persistencia del dominio)
```

Los modelos Eloquent viven en `app/Models` (convención de Laravel y del starter kit). La
lógica **no** vive en ellos: vive en servicios de dominio.

## 3. Decisiones de arquitectura (ADR)

**ADR-001 — Single-tenant.** Cada comprador instala Helios con su propia base. Sin
`company_id`, sin global scopes, sin paquetes de tenancy. La configuración de la empresa
es un registro único con `id = 1`.
_Consecuencia:_ la actualización del software y las migraciones deben ser seguras y
repetibles en instalaciones de clientes sin intervención del equipo.

**ADR-002 — La lógica de negocio vive en servicios de aplicación.** Ni en controladores
Inertia ni en componentes Vue. La API compartida de asesor/embebido reutiliza
los mismos casos de uso desde E1–E5; una API externa F6 es ampliación posterior.

**ADR-003 — La IA nunca produce números.** `ReceiptReader` y `SolarEngineeringAdvisor`
son contratos con implementación `Mock` por defecto. El motor determinista funciona y se
prueba sin ningún modelo presente. El lector real se integra en E3 y el Ingeniero
IA en E8, no se pospone lectura hasta después de propuestas. El contrato actual
solo ofrece fixtures y el asesor IA aún no existe.

**ADR-004 — Versionado y reproducibilidad.** Todo cálculo se persiste en
`calculation_runs` con entradas, parámetros, fuente de datos, supuestos, metodología,
versión del motor, resultado y advertencias. Misma entrada + mismas versiones = mismo
resultado.

**ADR-005 — Fuente de verdad de cálculos: Syscom (actualizado 2026-10-22).**
El motor reproduce [la referencia Syscom](syscom-calculator-reverse-engineering.md),
sus fórmulas y PR. HSP estatal real proviene del catálogo NASA del ADR-014;
los defaults de la referencia solo se usan en pruebas explícitas. Sin HSP válida
no se activa un fallback a 5.0 ni a la tabla Syscom para el flujo real.
`ParameterGate` comprueba únicamente los parámetros que requiere cada operación.
Las tarifas, compensación e impuestos pendientes bloquean sus cifras dependientes,
no el dimensionamiento de Syscom. La IA no inventa ni modifica los cálculos.

**ADR-006 — Dato no disponible ≠ dato nulo ≠ cero.** Los adaptadores de proveedor
devuelven valores envueltos: `Available(valor)` o `Unavailable(motivo)`. Un `null` crudo
nunca representa "el proveedor no lo informa".

**ADR-007 — Las cotizaciones congelan sus insumos.** Productos, precios,
especificaciones, fecha de consulta, perfil comercial, porcentajes aplicados y versión de
las reglas quedan copiados en la cotización. Cambiar un perfil **no** altera cotizaciones
anteriores.

**ADR-008 — Dinero en enteros.** Un value object `Money` con centavos en `int` y política
de redondeo explícita. Prohibido `float` para dinero. Las columnas monetarias son
`DECIMAL(14,2)` o enteros de centavos, nunca `FLOAT`.

**ADR-009 — Colas sobre base de datos.** `QUEUE_CONNECTION=database`. Evita exigir Redis
en la infraestructura del comprador, lo que encarecería cada instalación. Se revisa si el
volumen lo justifica.

**ADR-010 — Credenciales de proveedor cifradas en reposo.** Se guardan con casting
`encrypted` de Laravel, nunca se envían a Inertia ni aparecen en respuestas HTTP.

**ADR-011 — Sin starter kit de equipos.** No se usa `--teams`. El dominio `Identity` es
propio porque "empresa" aquí es la instalación completa, no un grupo dentro de la app.

### Decisiones de cotización, ubicación y recurso solar (2026-10-22)

El [RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md) reúne el flujo aceptado.
[ADR-012](adrs/012-motor-syscom.md) formaliza la adopción aceptada de Syscom.
[ADR-013](adrs/013-ubicacion-desde-recibo-cfe.md) define ubicación confirmada;
[ADR-014](adrs/014-hsp-nasa-power.md) define catálogo estatal NASA.
Ambas decisiones de producto están aceptadas, con metodología e implementación
pendientes. Google/contornos se mantiene pospuesto según RFC-002 actualizado.

## 4. Contratos esenciales

Son el límite entre lo que Helios controla y lo que depende de terceros. Se definen antes
de implementar cualquier adaptador.

### 4.1 Lectura de recibos

```php
namespace App\Domain\ReceiptData\Contracts;

interface ReceiptReader
{
    public function identifier(): string;          // 'mock', 'ollama'
    public function version(): string;             // versión del lector
    public function supports(string $mimeType): bool;
    public function read(ReceiptSource $source): ReceiptPayload;  // JSON v1.0 (ver cfe-receipt-schema.md)
}
```

Contrato objetivo para extracción futura. El adaptador actual vive en
`Domain/Intake/Contracts/ReceiptReader`: `readFixture(identifier)` devuelve únicamente
JSON sintético de un catálogo cerrado. `MockReceiptReader` no recibe ni interpreta el
archivo y se identifica como `mock-fixture` v1.0.0. No se toca el dominio CFE ya validado;
`ValidateMockReceipt` reutiliza `ReceiptImportValidator` y conserva bytes originales.
Ollama deberá implementar la lectura real de PDF/imagen posteriormente.
El lector **nunca** infiere un campo que no leyó: lo reporta en `missing_fields`.

### 4.2 Proveedores

```php
namespace App\Domain\Suppliers\Contracts;

interface SupplierProvider
{
    public function identifier(): string;                   // 'syscom', 'mock'
    public function capabilities(): SupplierCapabilities;   // qué sabe hacer ESTE proveedor
    public function searchProducts(ProductQuery $query): ProductSearchResult;
    public function getProduct(string $externalId): SupplierProduct;
    public function getPrice(string $externalId): Availability;   // Available|Unavailable
    public function getStock(string $externalId): Availability;
}
```

No se asume paridad entre proveedores: antes de llamar una operación se consulta
`capabilities()`. Lo que la API no entrega se marca `Unavailable`, jamás se inventa.

### 4.3 Ingeniero IA

```php
namespace App\Domain\Advisor\Contracts;

interface SolarEngineeringAdvisor
{
    public function identifier(): string;
    public function version(): string;
    public function analyze(AdvisorRequest $request, ToolRegistry $tools): AdvisorOutcome;
}
```

`ToolRegistry` expone **solo** servicios deterministas ya probados. El agente elige qué
herramienta llamar y con qué datos; el resultado numérico siempre viene de la herramienta.
`AdvisorOutcome` contiene narrativa, justificaciones y comparaciones — nunca cifras que no
provengan de una herramienta.

### 4.4 Irradiación y producción

```php
interface IrradianceProvider {  // NASA POWER, PVGIS, …
    public function identifier(): string;
    public function forCoordinates(Coordinates $c): IrradianceSample|Unavailable;
}

interface ProductionEstimator {  // PVWatts V8 u otro, previa verificación
    public function estimate(SystemConfiguration $s, IrradianceSample $i, LossModel $l): ProductionEstimate;
}
```

El dimensionamiento recibe HSP estatal NASA versionada, sin requerir consulta
por coordenadas por cada prospecto. Las interfaces por coordenadas y producción
de arriba son ampliaciones; no representan el contrato actual del catálogo estatal.

### 4.5 Compatibilidad eléctrica

```php
interface CompatibilityValidator {
    public function validate(SystemConfiguration $config): CompatibilityReport;
}
```

`CompatibilityReport` lista verificaciones aprobadas, fallidas y **pendientes de
inspección física**. Una configuración no es viable mientras tenga verificaciones
fallidas, sin importar lo que opine el agente.

## 5. Procesos asíncronos

Fundamento E1 implementado: expediente, eventos y `ProcessReceipt`, con dispatch
explícito después del commit y sin modificar estados del import CFE. Binding
por defecto bloquea por lector no disponible. No hay dispatch automático desde
el formulario ni pipeline completo. Ver [ADR-015](adrs/015-expediente-y-procesamiento-compartido.md)
y [contrato v1](contracts/solar-processing-v1.md). Lo siguiente es el objetivo:

Van a cola: importación y validación de recibos, consultas a proveedores, ejecución del
análisis completo, generación de PDF. Cada proceso largo tiene un registro de estado
consultable (`queued|running|succeeded|partial|failed`) para que la interfaz informe
avance real en lugar de un spinner indefinido.

## 6. Pruebas

| Tipo            | Qué cubre                                                                      |
| --------------- | ------------------------------------------------------------------------------ |
| Unitarias       | Servicios deterministas: dimensionamiento, producción, compatibilidad, precios |
| De referencia   | Casos de entrada conocidos con resultado esperado y fuente citada              |
| De integración  | Casos de uso completos contra base de datos                                    |
| De contrato     | Cada adaptador de proveedor contra una respuesta grabada                       |
| De arquitectura | `Domain` no depende de `Http`; los modelos no contienen reglas de negocio      |
| Frontend        | Componentes con Vitest                                                         |

Calidad: `pint` (formato), `larastan` (análisis estático), `pest` (pruebas). Los tres
corren antes de dar por terminada cualquier tarea.

## 6.1 Convenciones de frontend (obligatorias)

Este starter kit usa **Wayfinder**, no Ziggy. Consecuencias que han causado defectos reales:

- **No existe el helper global `route()`.** Escribir `route('company.update')` en una
  plantilla Vue compila sin error y revienta en el navegador con
  `TypeError: e.route is not a function`, dejando la página en blanco.
- Los formularios se enlazan así: `<Form v-bind="MiController.update.form()">`, importando
  el controlador desde `@/actions/App/Http/Controllers/...`.
- **Los archivos de `resources/js/routes/` y `resources/js/actions/` se generan**, no se
  escriben a mano. Se regeneran con el build (`npm run build`) o `php artisan wayfinder:generate`.
- Antes de copiar un patrón, mirar una página equivalente que ya funcione
  (`resources/js/pages/settings/Profile.vue` es la referencia para formularios de ajustes).

- **`npm run build` NO comprueba tipos.** Vite transpila sin verificar TypeScript, así que
  un error de tipos real pasa desapercibido. Ejecuta `npm run types:check`
  antes de dar por terminada cualquier pantalla.
- Las props que llegan de Inertia son `unknown`: hay que declarar su tipo y convertirlas
  explícitamente (`page.props.x as unknown as MiTipo`), no usarlas crudas.

**Definición de terminado para cualquier pantalla:** las pruebas de Pest sobre respuestas
de Inertia **no detectan un fallo de render de Vue** — comprueban el servidor, no el
navegador. Una pantalla no está terminada hasta que se abre en el navegador, se envía el
formulario y se verifica que el dato llegó a la base de datos y que la consola no tiene
errores.

## 7. Seguridad

Autenticación con Fortify (incluye 2FA y passkeys del starter kit). Roles y permisos por
usuario. Credenciales de proveedores cifradas y fuera del alcance del navegador. Control
de acceso a documentos. Validación estricta de toda entrada, incluida la del JSON CFE,
que es entrada no confiable aunque la suba un usuario autenticado. Auditoría de
modificaciones sobre precios, perfiles comerciales y cotizaciones.

## 8. Decisiones abiertas

| #   | Decisión                                                                                                | Estado                                                                           |
| --- | ------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| D1  | Catálogo HSP estatal NASA: metodología, período, frecuencia y caducidad                                 | **Fuente aceptada; implementación pendiente E2, bloquea HSP automático real**    |
| D2  | Uso de PVWatts V8 como referencia de producción: cobertura, límites de cuota y términos                 | **Ampliación futura; no bloquea Syscom**                                         |
| D3  | Origen y vigencia de las tarifas CFE y del esquema de compensación                                      | **Pendiente de fuente oficial**                                                  |
| D4  | Reglas fiscales aplicables y su vigencia (no asumir un porcentaje único)                                | **Pendiente de fuente oficial**                                                  |
| D5  | Motor de PDF: `spatie/laravel-pdf` (Chromium, mejor tipografía) vs `dompdf` (sin dependencias externas) | Propuesta: dompdf, por facilidad de instalación en infraestructura del comprador |
| D6  | Paquete de auditoría: `spatie/laravel-activitylog` vs tabla propia                                      | Propuesta: activitylog                                                           |
| D7  | ¿Helios publica un catálogo técnico base curado, o cada empresa parte de cero?                          | Pendiente de producto                                                            |

D1 no bloquea probar el motor con HSP manual explícita, pero sí el flujo real con
HSP automática. D2 sigue futura. D3 condiciona ahorro/retorno; D4, cifras con IVA.

## 9. Ingreso de recibos y límites de seguridad (v0.3)

Flujo de producto: PDF/imagen → prospecto (`Client`) → JSON interno Ollama/mock →
validación CFE → ingeniero con motores/proveedores reales → presupuesto configurable →
cotización → seguimiento. Los proyectos existentes son internos y opcionales; no se
obliga al prospecto a crearlos. `ParameterGate` sigue siendo un requisito previo a emitir
cifras: acepta la referencia Syscom y exige los datos propios de cada operación;
no se inventan precios ni parámetros ajenos a esa referencia.
En la entrega histórica v0.3 no se implementaron IA real ni API compartida.
El alcance vigente incluye lector/Ingeniero IA y API de los formularios; web
manager, constructor de landings y plataforma de API externa no son requisitos.

- GET/POST `/embed/cfe` se registran fuera de `web`: sin sesión, cookies, usuario CRM
  ni privilegios ambientales. No se exceptúa el CSRF del CRM. Un token cifrado expira en
  30 minutos, requiere 2 segundos mínimos y se consume una vez mediante caché atómica.
- Límite por IP: 30 GET/minuto y 5 POST/minuto, incluidos fallos; honeypot vacío.
  En producción el proxy debe configurarse con IP real y una caché compartida y persistente
  si hay varios procesos/nodos. Estos controles limitan abuso; no son un CAPTCHA.
- CSP `frame-ancestors` usa solo orígenes HTTPS exactos de `HELIOS_EMBED_ORIGINS`,
  separados por coma. Sin comodines, credenciales, rutas ni esquemas arbitrarios. Lista
  vacía implica `'none'`. Esto autoriza framing, **no CORS**. No se amplía CORS.
- No hay JavaScript externo en el embed. CSP restringe `default-src`, `form-action` y
  `base-uri`; respuestas sin caché, `nosniff` y sin referrer. Los errores públicos no
  muestran trazas. La configuración del reverse proxy debe conservar estas cabeceras.
- PHP `upload_max_filesize` debe admitir 10 MiB y `post_max_size`/proxy al menos el
  multipart completo (p. ej. 12 MiB); límites inferiores del servidor prevalecen.
- Validación real de MIME, extensión permitida y máximo 10 MiB. El disco `receipts`
  guarda bajo `storage/app/receipts-private`, sin URL pública ni ruta firmada genérica.
  Descargar exige autenticación + `receipts.view` + policy `Client::view`, como adjunto
  `application/octet-stream`. No se visualizan PDF activos inline. No se promete antivirus.
- `StoreReceiptIntake`: archivo primero, transacción cliente+intake, limpieza del archivo
  ante rollback. Un proceso terminado abruptamente puede dejar un archivo huérfano;
  una política de retención/reconciliación requiere configuración operativa posterior.
- El público siempre crea un prospecto independiente; nunca busca ni actualiza contactos
  existentes. En CRM se selecciona un ID explícito (no borrado) y se exige `clients.view`;
  crear requiere `clients.create`; ambos requieren `receipts.import`. No se permite
  secuestro por coincidencia de correo/teléfono ni deduplicación global de archivos.
- El detalle autenticado del prospecto devuelve `possibleDuplicates`: hasta cinco
  candidatos activos con correo o teléfono exactamente iguales según la comparación
  de la BD, excluyendo el registro actual y filtrados por `ClientPolicy::view`. Solo
  expone ID y nombre; no usa nombres similares, contactos vacíos ni coincidencias
  difusas. Es un aviso de revisión manual, no una identidad comprobada. Para reutilizar
  un prospecto se selecciona explícitamente en CRM antes de adjuntar el recibo; no se
  reasignan archivos, se fusionan datos ni se muestran candidatos en el formulario público.
- Se conserva aviso/version/URL/fecha de consentimiento. `HELIOS_INTAKE_ENABLED` solo
  habilita recepción si hay `HELIOS_PRIVACY_VERSION` y `HELIOS_PRIVACY_TEXT` no vacíos;
  `HELIOS_PRIVACY_URL` opcional acepta HTTPS. El propietario debe aprobar el contenido.
- El mock es una acción autenticada explícita y autorizada; solo catálogo
  `complete|partial|incoherent|malformed`, sin rutas suministradas por el usuario.
  Sus datos no proceden del archivo y no alimentan ingeniería/cotizaciones reales.

Cambios de esquema son aditivos y se verifican en SQLite aislada. No aplicar migraciones
sobre la BD compartida sin evaluar y autorizar su impacto. La integración final exige
`composer check`, `composer ci:check` y verificación real del flujo en navegador.

La migración `allow_unclassified_prospects` permite `clients.type = null` sin modificar
clasificaciones existentes. Un prospecto recién ingresado no se clasifica arbitrariamente
como residencial. Su `down` conserva la nulabilidad: restaurar NOT NULL requeriría una
clasificación explícita de los prospectos y no debe alterar ni borrar datos al revertir.
