# ADR-015 — Expediente y procesamiento compartido

- Fecha: 2026-10-23.
- Estado: fundamento E1 implementado; integración local E3–E5 parcial, sin cierre del flujo completo.
- Relacionado: [contrato v1](../contracts/solar-processing-v1.md),
  [plan](../project-plan.md), [RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md).

## Decisión

Cada nuevo recibo público o CRM crea un expediente `solar_cases`, sin proyecto
obligatorio. Se reutiliza el almacenamiento, consentimiento y validación del
intake existente. El registro de archivo/intake/expediente/evento es transaccional.
Un registro histórico conserva `solar_case_id` nulo hasta vinculación explícita;
no se ejecuta extracción, backfill o fusión por contacto al aplicar la migración.

El expediente guarda responsable nullable y ejes independientes para proceso,
ubicación, cálculo, publicación y entrega. La extracción/validación CFE permanece
en el intake y no cambia de significado para reutilizar sus estados.
En el alcance inicial E1, el alta dejaba proceso `received` sin dispatch.
La integración vigente descrita abajo añade el despacho condicionado al lector.

`receipt_processing_runs` identifica una ejecución por recibo. Su solicitud es
idempotente, despacha después del commit y no vuelve a guardar el archivo.
Job con lock compartido de solapamiento, timeout y reintentos limitados; acciones
repetidas de un trabajo terminado no repiten efectos. La salida de una ejecución
obsoleta no sobrescribe otro intento. El reintento manual conserva el run/archivo,
exige autorización y deja evento con actor.

Los efectos de lectores/cálculos futuros deben ser idempotentes por ejecución;
esta base no construye un orquestador de agentes. `ReceiptProcessor` delimita el
paso procesable sin depender de Http o Jobs. El binding inicial bloqueaba por lector
no disponible; el actual utiliza `ReadAndSizeReceipt`. Fixtures solo mediante sustitución explícita en pruebas, con
marca de simulación; completar una ejecución no publica cotización.

Auditoría base en `solar_case_events` con claves únicas, códigos y actor, sin
mensajes de excepción/PII ni endpoints de edición. No se adopta un paquete de
auditoría ahora; es el registro específico del expediente, no auditoría global
de todos los módulos. Responsable activo al crear desde CRM; casos públicos sin
responsable. La bandeja/avisos/toma atómica siguen en E5.

## Límites de acceso

Policy de expediente exige usuario activo, prospecto activo y permisos existentes.
La proyección interna requiere además `receipts.import`. La pública es una lista
expresa de campos sin costo, contacto, archivo ni causa técnica; no se usa
`toArray()` del modelo. Su caller deberá validar la credencial antes de construirla.
No existe endpoint público de consulta de expediente en E1.

## Consecuencias

Nueva migración aditiva necesaria antes de usar cargas con este código. No se
aplica automáticamente a la base compartida durante desarrollo. Retroceso de
la migración elimina solo estructuras E1 y desvincula archivos conservando
intakes/prospectos. Preservar respaldo de su auditoría antes de retroceder una
instalación real.

La cola usa conexión database existente y caché con locks. Valores vigentes:
timeout 840 s, lock 900 s y retry_after 960 s por defecto. Mantener retry_after
mayor a ambos; revisar overrides del entorno. No se
necesita Redis ni un proveedor externo para verificar estos fundamentos.

Reenvíos HTTP con Idempotency-Key, autorización pública por credencial y
recepción de segunda carga quedan especificados para E3–E5; la idempotencia
interna implementada no equivale todavía a replay HTTP seguro.

## Integración posterior — 2026-10-27

`ReceiveReceipt` conserva el archivo y solicita procesamiento desde CRM y
embebido cuando el lector está habilitado. `ReadAndSizeReceipt` conserva lectura,
revisión y cálculo con NASA, o devuelve un bloqueo explícito. El CRM consulta
progreso y permite revisión versionada y segunda carga interna. Estos avances
sustituyen el límite inicial de ausencia de lector/dispatch, sin convertir E1
en cierre de E4/E5 ni habilitar propuesta pública.

Los tiempos de cola deben verificarse en [runtime](../runtime.md), configuración
y job vigentes; los tiempos anteriores de E1 (60/75/90 s) quedaron sustituidos.
Recaptura pública, autenticación de consulta pública y entrega comercial siguen
pendientes. Ver [análisis automático](../automatic-receipt-analysis.md).
