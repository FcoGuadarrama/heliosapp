# Helios — API compartida de procesamiento solar

Revisión: 2026-10-23. Estado: contrato E1 definido y fundamentos implementados;
endpoints `/api/v1` aún no registrados. El recorrido interno bajo sesión ya
dispone de [análisis, revisión y cálculo](automatic-receipt-analysis.md); el
contrato público previsto se conserva por separado. Contrato concreto en
[procesamiento solar v1](contracts/solar-processing-v1.md) y decisión en
[ADR-015](adrs/015-expediente-y-procesamiento-compartido.md).
Sustituye la instrucción anterior de diferir toda API a F6. Ver
[RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md) y
[plan de entregas](project-plan.md).

## Dos consumidores del mismo negocio

El cliente usa el embebido existente: contacto, correo y archivo de recibo.
El asesor crea un prospecto y le adjunta el recibo. Ambos consumen la misma
API y caso de uso; no mantienen motores ni políticas comerciales duplicados.

La igualdad corresponde al cálculo para iguales entradas y versiones. Las
reglas de publicación/disponibilidad por canal siguen vigentes: el asesor
puede gestionar bajo pedido y la entrega automática requiere stock suficiente.

El servidor autoriza una proyección de respuesta según identidad y expediente:

| Consumidor        | Puede recibir                                                              | Nunca recibe por acceso público                                              |
| ----------------- | -------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Cliente           | Estado/paso pendiente, resultado técnico publicable y precio de venta      | Costo de compra, márgenes internos, credenciales, datos de otros expedientes |
| Asesor autorizado | Resultado técnico, faltantes, costo de compra disponible y precio de venta | Credenciales de proveedor o archivos fuera de sus permisos                   |

No basta ocultar campos en Vue. El servidor excluye costos de toda respuesta y
PDF público, incluso si hay una sesión CRM abierta en el mismo navegador.
Un campo `audience`, `role` o un ID de prospecto del formulario no concede acceso.

## Contenido del contrato E1

- Recepción multipart, validación de contacto/archivo/consentimiento, token
  público de formulario, límites e idempotencia de envío/reintento.
- Consulta de estado y resultado, autorización limitada al expediente y
  respuestas de error estables sin trazas/PII. Sin URLs con datos personales.
- Segunda carga por legibilidad, correcciones de ubicación, revisión de asesor
  y reanudación del mismo expediente. Fallo técnico no consume intentos.
- Estados separados de procesamiento, validación CFE, geografía, cálculo,
  publicación, PDF y correo. Un trabajo recibido no equivale a trabajo ejecutado.
- Esquema de resultado genérico: consumo, fuente/HSP/versiones, cantidades y
  capacidades, supuestos, advertencias y faltantes. Potencia por panel necesita
  parámetro explícito; no pedirla al cliente ni inventar un modelo comercial.
- Venta y costo separados, con estado/motivo de disponibilidad. Sin fuentes
  válidas no devolver cero, precios ficticios ni cotización publicable.
- Semántica HTTP, versionado, URLs, ejemplos y permisos en el contrato v1.
  Credencial pública por expediente y sesión/CSRF internos se implementan con
  las rutas en E4/E5; no existe aún `/api/v1` funcional.

El embebido actual es Blade sin JavaScript externo y con CSP restrictiva;
consumir JSON/progreso requiere adaptar su interacción y CSP expresamente.
Preservar aislamiento sin sesión, token/honeypot/límites y allowlist de iframe.
No ampliar CORS ni exceptuar CSRF del CRM para simplificar la integración.

## Implementación por dependencias

E1 define contrato/estados y preparación con fixtures explícitas. E2 y E3
aportan HSP y lectura real. E4 compone cálculo y persistencia en una API útil;
E5 conecta asesor y embebido. Equipos/precios/propuestas se integran en las
entregas correspondientes sin cambiar el formulario público a captura técnica.

Los controladores validan y autorizan, luego delegan a Application. El dominio
no depende de Http; un caso de uso no conoce Inertia ni la sesión. La selección
IA usa herramientas deterministas; no se convierte una fixture en lector real.

## Ampliación de API externa (F6 histórica)

Claves para consumidores externos, documentación OpenAPI para integradores,
revocación, límites de uso y webhooks son una ampliación diferente de la API
que ahora necesita el producto. Cuotas comerciales y billing no son requisitos
de esta entrega. No construir ese sistema para poder conectar el iframe propio.
