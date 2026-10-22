# Helios App — Backlog de desarrollo

> v0.3 · 2026-10-22 · Modelo recomendado por tarea: **H** = Haiku (`simple-developer`),
> **S** = Sonnet (`complex-developer`), **O** = Opus (arquitecto), **QA** = `qa-reviewer`.
> Cada tarea se da por terminada sólo cuando `pint`, `larastan` y `pest` pasan.

---

## Prioridad vigente — ingreso de recibos al CRM (en curso)

El historial de F1 se conserva abajo. La prioridad de producto supersede la carga pública
de JSON y la creación manual obligatoria de proyectos: **recibo PDF/imagen → prospecto →
JSON interno Ollama/mock → validación → ingeniería con motores/proveedores reales →
presupuesto configurable → cotización → seguimiento**. `Client` se reutiliza como prospecto.

- **I1 Público + iframe — en curso:** `/embed/cfe`, mínimos datos, archivo privado,
  consentimiento versionado, token un uso, límites, honeypot y CSP allowlist. Pendiente
  evidencia de navegador y configuración real aprobada antes de habilitar producción.
- **I2 CRM — en curso:** crear/seleccionar prospecto y subir archivo sin proyecto;
  snippet copiable, descarga autorizada, estados y errores claros. Navegación prioritaria
  Dashboard/Mis prospectos/Cotizaciones/Proveedores/Costos y márgenes/Usuarios.
  Aviso interno de posibles duplicados por contacto exacto; reutilización por selección
  explícita del prospecto. No se enlazan ni fusionan datos desde la entrada pública.
- **I3 Mock y contrato — en curso:** fixtures internas explícitas, validadores CFE
  existentes, trazabilidad e inmutabilidad. No extrae PDF ni simula ingeniería real.
- **I4 Seguridad y cierre — pendiente:** pruebas permisos/archivos/consentimiento/abuso/
  embed/duplicados/vínculos, checks completos sobre integración y revisión independiente
  de interfaz/seguridad. No se marca terminado solo con tests de servidor.

No expandir módulos futuros ni iniciar la normalización energética histórica F1.7 antes
de cerrar los fundamentos necesarios; su entrada deberá adaptarse al nuevo intake.

## Fase 1 — Dashboard y fundamentos

**Objetivo histórico de F1:** fundamentos de clientes y proyectos. El flujo vigente de
producto recibe PDF/imagen con el prospecto y genera JSON interno; los proyectos quedan
como soporte interno opcional. Los criterios históricos se conservan para trazabilidad.

### F1.0 — Instalación base · **O** · ✅ HECHO

Laravel 13.38 + Inertia 3 + Vue 3 + Fortify (2FA y passkeys) + Pest 5 + MySQL `helios`.
Aplicación verificada en el navegador con sesión iniciada.

### F1.1 — Esqueleto de dominio y pruebas de arquitectura · **H** · ✅ HECHO

_Depende de:_ F1.0

- Crear `app/Domain/{Identity,CompanySettings,Clients,Projects,ReceiptData,Shared}` con un
  `.gitkeep` y el namespace registrado.
- Crear `app/Application/`.
- Prueba de arquitectura Pest: `App\Domain` no usa `App\Http`; ningún modelo en
  `App\Models` contiene métodos con reglas de negocio distintas de relaciones, scopes,
  casts y accessors.
- Script `composer check` que corre `pint --test`, `larastan` y `pest`.

**Aceptación:** `composer check` pasa en limpio. La prueba de arquitectura falla si se
importa `App\Http` desde `App\Domain` (verificarlo rompiéndolo a propósito y revirtiendo).

### F1.2 — Configuración de empresa (registro único) · **H** · ✅ HECHO

_Depende de:_ F1.1

- Migración `company_settings` según `docs/domain-model.md`, con seeder que crea la fila
  `id = 1` vacía.
- Modelo `CompanySetting` con helper `CompanySetting::current()`.
- Página Inertia `settings/company` con formulario: nombre, razón social, RFC, logo,
  dirección, teléfono, correo, sitio, colores, zona horaria, vigencia de propuestas,
  responsable.
- `FormRequest` con validación estricta. Subida de logo al disco `public`.

**Aceptación:** el formulario guarda y recarga los valores; nunca existe más de una fila;
hay prueba de feature que lo comprueba.

### F1.3 — Roles y permisos · **S** · ✅ HECHO

_Depende de:_ F1.1

- Instalar `spatie/laravel-permission` **sin** la función de equipos (single-tenant).
- Roles `owner`, `admin`, `engineer`, `sales`, `viewer` y permisos por módulo
  (`clients.*`, `projects.*`, `receipts.*`, `settings.*`, `users.*`).
- Seeder idempotente de roles y permisos, seguro de re-ejecutar en actualizaciones.
- Pantalla de usuarios: alta, edición, asignación de rol, desactivación.
- `Gate`/políticas base y middleware aplicado a las rutas.

**Aceptación:** un `viewer` recibe 403 al intentar crear un cliente; un `sales` puede.
Pruebas por rol. El seeder corre dos veces sin duplicar nada.
_Requiere revisión de **QA** (es control de acceso)._

### F1.4 — Clientes · **H**

_Depende de:_ F1.3

- Migración, modelo, factory y _soft deletes_.
- CRUD completo con páginas Inertia: índice con búsqueda y paginación, alta, edición,
  detalle con historial de proyectos.
- `FormRequest` con validación de correo, teléfono y RFC (formato, sin inventar reglas
  fiscales).
- Políticas conectadas a los permisos de F1.3.

**Aceptación:** CRUD funcional en el navegador; pruebas de feature para cada operación y
para el control de permisos.

### F1.5 — Proyectos solares · **H**

_Depende de:_ F1.4

Criterios históricos: no imponen crear proyectos ni cargar JSON manualmente en el flujo
vigente I1–I3. El detalle del prospecto y sus recibos es la entrada actual del CRM;
los proyectos permanecen como soporte interno opcional.

- Migración, modelo, factory, _soft deletes_, enum de estado y generación de `code`.
- CRUD con páginas Inertia; el detalle del proyecto es la pantalla central del producto:
  pestañas de datos generales, datos energéticos y (más adelante) análisis.
- Campos de ubicación con latitud y longitud, marcando explícitamente en la interfaz que
  **sin coordenadas no habrá dimensionamiento**.
- `objectives` y `property_data` como formularios estructurados guardados en JSON.

**Aceptación:** crear un proyecto asociado a un cliente, editarlo y verlo listado; el
indicador de "faltan coordenadas" aparece cuando corresponde; pruebas de feature.

### F1.6 — Contrato y validación del JSON CFE · **S** · ✅ HECHO

_Depende de:_ F1.1 · **Especificación: `docs/cfe-receipt-schema.md`**

- DTOs inmutables del payload v1.0 en `app/Domain/ReceiptData`.
- `ReceiptSchemaValidator`: validación estructural de la v1.0.
- `ReceiptCoherenceValidator`: **las 18 reglas** de la tabla §4 del contrato, cada una con
  su código, nivel y mensaje.
- Umbrales (tolerancias, rangos de días) en `config/helios.php`, no incrustados.
- Campos desconocidos se conservan y se reportan como `UNKNOWN_FIELD`.
- Derivación de estado: `rejected` / `needs_review` / `validated`.

**Aceptación:** una prueba unitaria por cada código de la tabla, con un payload que la
dispara y otro que no. Un JSON malformado no lanza excepción no controlada. Ningún campo
del payload original se pierde.
_Requiere revisión de **QA** (es la frontera de datos no confiables)._

### F1.7 — Lector de recibos e importación · **S** — histórico, pendiente de adaptar

_Depende de:_ F1.5, F1.6

- Interfaz `ReceiptReader` y `MockReceiptReader` que valida y normaliza un JSON entregado.
- Binding por configuración (`config/helios.php` → `receipt_reader`), preparado para
  `OllamaReceiptReader` sin tocar el dominio.
- Caso de uso `ImportReceiptPayload`: persiste `receipt_imports`, `electric_services` y
  `billing_periods`; `raw_payload` íntegro e inmutable.
- Job en cola con estado consultable.
- Fixtures en `tests/Fixtures/receipts/`: uno completo, uno parcial, uno incoherente y uno
  malformado.

**Aceptación:** subir cada fixture produce el estado esperado; `raw_payload` es byte a
byte el original; reimportar el mismo periodo no duplica `billing_periods`.

### F1.8 — Interfaz de datos energéticos y corrección supervisada · **S**

_Depende de:_ F1.7

- Pestaña del proyecto: carga del JSON, estado del import, tabla de periodos, gráfica de
  historial de consumo.
- Panel de advertencias y campos faltantes, legible para un asesor, no para un programador.
- Corrección supervisada campo por campo sobre `normalized_payload`, con registro de quién
  cambió qué y cuándo. `raw_payload` nunca se toca.
- Acción de aceptar un import en `needs_review`.

**Aceptación:** un import con advertencias se puede revisar, corregir y aceptar; el
historial de cambios queda visible; `raw_payload` permanece intacto.

### F1.9 — Panel principal y navegación · **H**

_Depende de:_ F1.5

- Barra lateral con los módulos del producto (los no construidos quedan deshabilitados y
  señalados, no ocultos).
- Panel principal con: proyectos por estado, proyectos recientes, proyectos sin datos
  energéticos, actividad reciente.
- Identidad visual de Helios (logotipo, color, tipografía) sobre el starter kit.

**Aceptación:** los indicadores reflejan datos reales de la base; navegación funcional en
escritorio y en móvil.

### F1.10 — Auditoría base · **H**

_Depende de:_ F1.3

- `spatie/laravel-activitylog` registrando cambios en clientes, proyectos, configuración
  de empresa y correcciones de recibos.
- Vista de actividad reciente consumida por el panel principal.

**Aceptación:** editar un cliente genera una entrada con usuario, campos modificados y
marca de tiempo.

### F1.11 — Instalación y actualización · **H**

_Depende de:_ F1.2

- Comando `helios:install` idempotente: comprueba requisitos, ejecuta migraciones, siembra
  roles, crea el usuario administrador inicial y la fila de configuración.
- `README.md` con instalación, actualización y respaldo.

**Aceptación:** sobre una base vacía, el comando deja la aplicación lista para iniciar
sesión. Ejecutarlo dos veces no rompe nada.

---

## Fase 2 — Ingeniería y proveedores · _no iniciar hasta cerrar F1_

Motor energético determinista · `ParameterGate` y parámetros verificables · adaptadores
`MockSupplierProvider` y `SyscomProvider` · catálogo técnico normalizado · validador de
compatibilidad eléctrica · estimación de producción · `calculation_runs`.

**Bloqueada para emitir cifras** por D1–D4 de `docs/architecture.md` §8. La estructura, los
contratos y las pruebas sí pueden construirse.

## Fase 3 — Presupuestos configurables

Perfiles comerciales · conceptos de costo · markup vs margen bruto · impuestos verificables ·
motor de presupuestos · congelado de insumos · vista previa del efecto de los parámetros.

## Fase 4 — Propuestas

Armado · PDF personalizable · alternativas comparadas · versiones e historial.

## Fase 5 — Ingeniero IA

Integración de Ollama · `OllamaReceiptReader` · `SolarEngineeringAdvisor` · `ToolRegistry` ·
validación de los resultados del agente contra el motor determinista.

## Fase 6 — API pública

REST versionada · autenticación de consumidores · OpenAPI · límites de uso · webhooks.

---

## Riesgos abiertos

| #   | Riesgo                                                            | Mitigación                                                                       |
| --- | ----------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| R1  | Parámetros CFE y fotovoltaicos sin fuente verificada              | `ParameterGate` impide emitir cifras; se construye todo lo demás                 |
| R2  | La API de Syscom puede no exponer todo lo que el motor necesita   | `capabilities()` por proveedor y marcado explícito de datos no disponibles       |
| R3  | Precios y existencias cambian entre cotización y compra           | Congelado de insumos y re-verificación obligatoria antes de liberar orden        |
| R4  | Actualizar Helios en instalaciones de clientes puede romper datos | Migraciones y seeders idempotentes, comando de instalación, respaldo documentado |
| R5  | Confundir markup con margen bruto falsea la utilidad              | Fórmulas separadas, validadas y con pruebas unitarias propias                    |
| R6  | El agente IA podría emitir cifras propias                         | Las cifras sólo provienen del `ToolRegistry`; se valida la salida del agente     |
