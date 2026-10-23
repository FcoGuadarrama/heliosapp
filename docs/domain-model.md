# Helios App — Modelo de dominio

> v0.3 · Prospectos y recibos · Single-tenant: **ninguna tabla lleva `company_id`.**
> Las tablas marcadas **[F1]** se construyen ahora. El resto queda documentado para que las
> decisiones de hoy no estorben mañana, pero **no se implementan todavía**.

> Revisión 2026-10-23: las marcas F1/F2 y tablas de diseño no acreditan tablas
> creadas. Ver inventario y secuencia en [plan del proyecto](project-plan.md).
> La normalización futura se vincula al expediente/prospecto sin exigir proyecto;
> las referencias históricas `project_id` de abajo deben adaptarse antes de migrar.

## Convenciones

- Dinero: `DECIMAL(14,2)`. Nunca `FLOAT`.
- Energía: `DECIMAL(12,3)` kWh. Potencia: `DECIMAL(10,3)` kW/kWp.
- Instantes con zona horaria explícita; fechas de periodo como `DATE`.
- Estructuras variables (advertencias, supuestos, evidencia): columnas `JSON`.
- Todo registro creado por una persona guarda `created_by` → `users.id`.
- Borrado lógico en entidades con historial comercial (clientes, proyectos, cotizaciones).

---

## Identidad y configuración

### `users` **[F1]**

Del starter kit (Fortify, 2FA, passkeys), con `is_active`. Los roles/permisos
actuales se almacenan mediante Spatie; no existe una columna `role` de negocio.
Roles previstos: `owner`, `admin`, `engineer`, `sales`, `viewer`.

### `company_settings` **[F1]** — registro único (`id = 1`)

Identidad de la empresa que compró Helios.

`company_name`, `legal_name`, `rfc`, `logo_path`, `address` (json), `phone`, `email`,
`website`, `primary_color`, `secondary_color`, `timezone`, `default_currency`,
`proposal_validity_days`, `responsible_name`, `responsible_title`, `legal_texts` (json),
`updated_by`.

No es una entidad con muchas filas: es la configuración de **esta** instalación.

---

## Comercial

### `clients` **[F1]** — prospectos y clientes del CRM

`type` nullable (`residential|commercial|industrial|agricultural`; null = prospecto sin clasificar), `name`, `contact_name`,
`email`, `phone`, `rfc`, `street`, `exterior_number`, `interior_number`, `neighborhood`,
`municipality`, `state`, `postal_code`, `notes`, `created_by`, _soft deletes_.

### `receipt_intakes` **[Ingreso de recibos — en curso]**

Vincula un archivo privado directamente a `client_id`; **no requiere `project_id`**.
`created_by` nullable (público), `source` (`embed|crm`), `file_path` privado y aleatorio,
`mime_type`, `file_size`, `sha256`, `privacy_version`, `privacy_text`, `privacy_url`,
`consented_at`, `status`, `reader_identifier`, `reader_version`, `fixture_identifier`,
`raw_payload` (texto exacto, incluso una fixture malformada), `validation_result` JSON,
timestamps; desde E1, `solar_case_id` nullable. El hash no se usa para fusionar prospectos. El nombre original no se expone
ni se utiliza como ruta. El endpoint público no serializa el registro.

Estados actuales: `stored` indica archivo recibido sin extracción;
`mock_validated|mock_needs_review|mock_rejected` son simulaciones identificadas.
La validación interna distingue `validated|needs_review|rejected`; ninguna equivale a
aceptación humana o diseño apto para instalación. El JSON original de una simulación
no se sobrescribe. Los recibos conservan vínculo a prospectos borrados lógicamente.

### Expediente y ejecución **[E1 — implementado, migración aditiva]**

`solar_cases`: referencia pública UUID (no credencial), `client_id`, responsable
nullable, procesamiento y ejes separados de ubicación/cálculo/publicación/entrega,
marca de simulación, bloqueo y fecha de atención. `receipt_processing_runs`:
ejecución única por intake, estado, número de intentos técnicos y tiempos/código
de error. `solar_case_events`: historial con clave única, evento, código y actor.
Recibos históricos quedan sin expediente hasta vinculación explícita. Nuevas
cargas crean expediente en received; aún no disparan el job automáticamente.
Detalles en [ADR-015](adrs/015-expediente-y-procesamiento-compartido.md).

### `projects` **[F1]** — internos y opcionales en el nuevo flujo

`client_id`, `code` (único, legible, autogenerado), `name`,
`status` (`draft|data_received|analyzing|analyzed|quoted|proposal_sent|won|lost|archived`),
`installation_type` (`roof|ground|carport|other`), `property_type`,
dirección del sitio (`street`…`postal_code`), `latitude` `DECIMAL(10,7)`,
`longitude` `DECIMAL(10,7)`, `objectives` (json: objetivo de compensación, techo
presupuestal, preferencias), `property_data` (json: superficie disponible, orientación,
inclinación, sombreado, tablero, acometida), `notes`, `assigned_user_id`, `created_by`,
_soft deletes_.

> El proyecto opcional puede guardar ubicación. El flujo de recibo necesita una
> ubicación/entidad propia del expediente, fuera del JSON CFE, sin obligar a
> crear proyecto. HSP se obtiene del catálogo estatal NASA; no se exige una
> consulta de irradiación por coordenadas para cada dimensionamiento.

---

## Datos de recibo

### `receipt_imports` **[F1]**

Diseño histórico de la fase de normalización, aún no implementado. El ingreso de producto vive en `receipt_intakes`; Ollama/mock generará JSON interno. Ver `docs/cfe-receipt-schema.md`.

`project_id`, `schema_version`, `source_type`, `source_reference`,
`reader_identifier`, `reader_version`,
`status` (`rejected|needs_review|validated|accepted`),
`raw_payload` (json, íntegro y sin modificar), `normalized_payload` (json),
`errors` (json), `warnings` (json), `missing_fields` (json), `evidence` (json),
`reviewed_by`, `reviewed_at`, `created_by`.

`raw_payload` nunca se edita. Las correcciones supervisadas se aplican a
`normalized_payload` y quedan registradas en auditoría.

### `electric_services` **[F1]**

`project_id`, `receipt_import_id`, `service_number`, `tariff_code`,
`tariff_description`, `voltage_level`, `phases`, `contracted_demand_kw`,
`billing_cycle`, `holder_name`, `service_address` (json).

### `billing_periods` **[F1]**

Historial normalizado que consume el motor.

`electric_service_id`, `receipt_import_id`, `period_start`, `period_end`, `days`,
`consumption_kwh`, `consumption_by_tier` (json), `demand_kw`, `amount_mxn`,
`concepts` (json), `is_from_history` (bool), `is_estimated` (bool).

Índice único: (`electric_service_id`, `period_start`, `period_end`).

---

## Ingeniería _(F2)_

Diseño histórico a adaptar. Falta el catálogo HSP por entidad/versión con
metodología, valores y evidencia NASA (ADR-014); `irradiance_samples` por
coordenadas no sustituye ese catálogo. El motor puro `Domain/Solar` ya existe,
pero ninguna de las tablas de ingeniería descritas aquí está implementada.

### `energy_diagnoses`

`project_id`, `engine_version`, `status` (`preliminary_complete|conditioned|requires_professional_review`),
`inputs_snapshot` (json), `annual_consumption_kwh`, `average_daily_kwh`,
`monthly_profile` (json), `peak_period`, `current_annual_cost_mxn` _(sólo si la tarifa
está verificada)_, `findings` (json), `missing_data` (json), `assumptions` (json).

### `irradiance_samples`

Caché por coordenadas redondeadas: `provider`, `provider_version`, `latitude`,
`longitude`, `monthly_ghi` (json), `annual_ghi`, `retrieved_at`, `raw_response` (json).

### `tariff_schedules` — **datos de referencia, requieren verificación**

`code`, `name`, `version`, `effective_from`, `effective_to`, `structure` (json),
`compensation_scheme`, `source_reference`, `source_url`, `verified_at`, `verified_by`.
Sin `verified_at`, el motor **no calcula costos**.

### `loss_models` — **requieren verificación**

`code`, `version`, `factors` (json), `source_reference`, `verified_at`.

### `sizing_scenarios`

`project_id`, `energy_diagnosis_id`, `engine_version`, `label`, `offset_target_pct`,
`required_kwp`, `module_id`, `module_count`, `installed_kwp`, `inverter_id`,
`inverter_count`, `dc_ac_ratio`, `string_configuration` (json),
`estimated_annual_production_kwh`, `monthly_production` (json), `performance_ratio`,
`specific_yield`, `required_area_m2`, `compatibility_report` (json), `assumptions` (json),
`is_recommended`.

### `calculation_runs` — trazabilidad (ADR-004)

`subject_type`, `subject_id`, `engine_version`, `parameter_versions` (json),
`data_sources` (json), `inputs` (json), `inputs_hash`, `methodology`, `outputs` (json),
`warnings` (json), `duration_ms`.

---

## Proveedores y catálogo _(F2)_

### `suppliers`

`identifier` (`syscom`…), `name`, `is_active`, `credentials` (json **cifrado**),
`capabilities` (json), `last_synced_at`.

### `supplier_products`

Caché normalizada: `supplier_id`, `external_id`, `sku`, `manufacturer`, `model`,
`category`, `specifications` (json), `datasheet_url`, `last_price_mxn`, `currency`,
`availability`, `price_checked_at`, `raw_response` (json).

Los campos que el proveedor no informa se guardan como _no disponible_ explícito, nunca
como `0`.

### `pv_modules`, `inverters`, `mounting_structures`, `materials`

Catálogo técnico normalizado con las especificaciones necesarias para validar
compatibilidad (tensiones, corrientes, rangos MPPT, coeficientes de temperatura,
dimensiones). Pueden originarse de un proveedor o capturarse manualmente.

---

## Presupuestos _(F3)_

La regla vigente es RFC-001 §9–10: perfiles de venta por kWp/panel/paquete,
sin IVA ni instalación, inclusiones y prioridad explícitas; descuentos por
concepto/subtotal de un solo tipo por versión. El costo de compra es un dato
separado con disponibilidad explícita y solo se expone a asesor autorizado.
Los diseños históricos de markup/mano de obra siguientes no sustituyen estas
reglas ni autorizan incluir instalación. Cerrar bases por concepto antes de migrar.

### `pricing_profiles`

Perfil comercial reutilizable: `name`, `project_type`, `is_default`, `version`,
`rules` (json: porcentajes sobre productos / materiales / mano de obra, markup general,
margen bruto objetivo, indirectos, contingencia, descuento máximo, utilidad mínima),
`rounding_policy` (json), `is_active`.

### `cost_items`

Conceptos configurables: `category` (`equipment|labor|service|overhead`), `code`, `name`,
`basis` (`fixed|per_module|per_kwp|per_hour|percentage`), `amount`, `is_active`.

### `tax_rules` — **requieren verificación fiscal**

`code`, `name`, `rate`, `applies_to` (json), `effective_from`, `effective_to`,
`source_reference`, `verified_at`.

### `quotes` / `quote_lines`

La cotización **congela** (ADR-007): productos, precios, fecha de consulta al proveedor,
perfil comercial aplicado, porcentajes, descuentos, impuestos, costo total, precio final,
versión de las reglas y responsable de cada cambio manual.

---

## Propuestas y auditoría _(F4)_

### `proposals`

`project_id`, `quote_id`, `version`, `status`, `content` (json: el documento tal como se
emitió), `pdf_path`, `generated_at`, `valid_until`, `created_by`.
Una propuesta emitida reproduce siempre los valores del momento en que se generó.

### `activity_log`

Auditoría de modificaciones sobre precios, perfiles comerciales, cotizaciones y
correcciones supervisadas de recibos.
