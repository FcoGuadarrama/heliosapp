# Contrato v1 — Expediente y procesamiento solar

Entrega E1, 2026-10-23. Estado: contrato para implementar en E3–E5; las rutas
HTTP `/api/v1` de esta página todavía no están registradas. La actualización de
2026-10-26 conecta carga/lectura/cálculo y rutas CRM bajo sesión; ver
[implementación interna y límites](../automatic-receipt-analysis.md). La API
pública por enlace, publicación comercial y precios siguen pendientes.
Relacionado: [plan](../project-plan.md), [API](../api-design.md),
[RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md).

## Límites HTTP acordados para implementación

Base `/api/v1`. Dos accesos al mismo servicio de aplicación; cambia la
autorización y la proyección, no las fórmulas ni la interpretación del recibo.

| Operación prevista                                     | Acceso                                | Comportamiento                                                                                          |
| ------------------------------------------------------ | ------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| GET `/intake-form`                                     | Público sin sesión                    | Aviso/version de privacidad, límites y token de formulario de un uso                                    |
| POST `/solar-cases`                                    | Público sin sesión                    | Contacto y recibo; crear prospecto/expediente privado; devolver 202 y credencial limitada al expediente |
| GET `/solar-cases/{reference}`                         | Bearer del expediente                 | Estado y resultado público permitido; UUID solo no autoriza                                             |
| POST `/solar-cases/{reference}/receipts`               | Bearer limitado y token de formulario | Segunda carga solo si se solicitó por ilegibilidad; mismo expediente                                    |
| PATCH `/solar-cases/{reference}/location`              | Bearer limitado                       | Confirmar/corregir ubicación del mismo expediente; no consume cargas                                    |
| POST `/clients/{client}/solar-cases`                   | Sesión CRM + CSRF + permisos          | Crear expediente con archivo para prospecto activo explícito                                            |
| GET `/clients/{client}/solar-cases/{reference}`        | Sesión CRM + permisos                 | Proyección interna del expediente vinculado, incluyendo disponibilidad de costo                         |
| POST `/clients/{client}/solar-cases/{reference}/retry` | Sesión CRM + CSRF + permisos          | Reintentar fallo/bloqueo del mismo proceso, sin nueva carga                                             |

Público: no iniciar sesión web ni resolver cookies de autenticación CRM. No
aceptar `client_id`, `responsible_user_id`, `role`, `audience`, precios, estado
ni parámetros técnicos suministrados por cliente. La credencial de expediente
será aleatoria, almacenada mediante hash, revocable y limitada por operación;
no usar la referencia UUID como credencial. Su ciclo de vida de procesamiento
se implementa junto al acceso en E4 y no se confunde con el enlace de propuesta
del RFC-001 §11 (sin expiración temporal de versión vigente).

Asesor: usuario activo, sesión verificada, `clients.view`, `receipts.view` y
`receipts.import`, además de las políticas de cliente/expediente. No permitir
acceso a un expediente por cambiar el `client` de la ruta. Un `viewer` puede
ver según policy, pero no tiene derecho a la proyección de precios internos
ni al procesamiento. El archivo original conserva su descarga CRM autorizada;
un bearer de estado público no da acceso al archivo ni al CRM.

## Entrada, reenvíos y errores

Contacto público: nombre y correo obligatorios, teléfono opcional, recibo
PDF/JPEG/PNG/WebP hasta límite configurado, consentimiento y versión vigente,
`form_token` y honeypot vacío. Asesor: prospecto existente más recibo y
consentimiento; el alta de prospecto sigue su operación CRM independiente.
No recibir el JSON CFE del navegador como si fuera extracción real.

Conservar token/honeypot/rate limit y aislamiento del embebido. CSP y consumo
JSON deben adaptarse expresamente en E5; no ampliar CORS ni exceptuar CSRF del
CRM. El público nunca busca ni fusiona contactos existentes.

El contrato HTTP utilizará `Idempotency-Key` opaca por operación y expediente;
misma clave y misma entrada reusan el envío, una clave con contenido diferente
responde 409. La operación inicial pública también se vincula al token de
formulario y su plazo válido; un token consumido no crea otra solicitud con
otra clave. La persistencia del replay HTTP y recuperación segura de su
respuesta se implementan en E4, antes de conectar frontend. E1 solo garantiza
idempotencia de registro/ejecución interna para un recibo ya persistido.

Errores previstos: 401 falta de sesión CRM; 403 permisos insuficientes CRM;
404 credencial pública inválida/revocada o expediente inaccesible, sin indicar
existencia; 409 conflicto de operación/clave; 413 tamaño de servidor; 422 campos
inválidos o paso no permitido; 429 límite con Retry-After; 503 recepción no
configurada. Respuestas públicas nunca contienen traza, error técnico, costo,
contacto, domicilio ni identificadores internos del CRM.

Forma pública de error: `{"error":{"code":"validation_failed","message":
"Revisa los datos enviados.","fields":{"receipt":["Selecciona un recibo válido."]}}}`.
Todos los resultados y errores de expediente llevan `Cache-Control: no-store,
private`, `Referrer-Policy: no-referrer` y `X-Content-Type-Options: nosniff`.
No implementar variantes de error diferentes en cada formulario.

## Estado persistido y transiciones E1

Cada nuevo envío de los controladores actuales crea un `solar_cases` asociado
al prospecto y al `receipt_intakes`. Recibos anteriores conservan su archivo y
estado; la columna nueva es nullable y no se backfillean ni procesan de oficio.
El alta de archivo, prospecto, expediente y evento se hace en la misma transacción,
con la limpieza de archivo ya existente si falla.

| Eje           | Estado actual inicial             | Propósito                                                       |
| ------------- | --------------------------------- | --------------------------------------------------------------- |
| Procesamiento | `received`                        | Trabajo aún no solicitado; no prometer worker activo            |
| Import CFE    | `receipt_intakes.status = stored` | Extracción/validación separadas; estados mock actuales intactos |
| Ubicación     | `pending`                         | Confirmación geográfica pendiente                               |
| Cálculo       | `pending`                         | No hay resultado calculado                                      |
| Publicación   | `draft`                           | Nada publicado al cliente                                       |
| Entrega       | `not_requested`                   | No afirmar correo ni PDF generado                               |

Transiciones de proceso:

```text
received → queued → running → blocked | completed
                       ↘ queued (reintento técnico automático)
                       ↘ failed (agotamiento de intentos/timeout)
blocked | failed → queued (reintento explícito autorizado)
```

`completed` termina una ejecución; no cambia import, ubicación, cálculo,
publicación ni entrega. Una ejecución simulada conserva `simulated=true` y no
puede considerarse publicación real. El procesador por defecto es
`UnavailableReceiptProcessor`: termina `blocked/receipt_reader_unavailable`.
No lee fixtures automáticamente, no interpreta archivos y no crea propuestas.
Los productores reales de E3/E4 reemplazarán ese contrato de procesamiento.

`QueueReceiptProcessing` crea como máximo una ejecución por recibo. Un segundo
llamado devuelve la misma sin duplicar job. `RetryReceiptProcessing` reusa esa
ejecución desde blocked/failed, exige operador autorizado y registra el actor.
Los intentos de ejecución son distintos de la cantidad de archivos: reintentar
no gasta otra oportunidad por legibilidad. E3 implementará la segunda carga y
su autorización; E1 no permite adjuntarla mediante una ruta pública inexistente.

Cola después del commit. Se registra `dispatched_at` tras aceptar el dispatch;
si falla, la solicitud interna repetida vuelve a enviarlo sin otro run. Si la
cola acepta y falla registrar el acuse, puede repetirse el mensaje: el guard
de ejecución impide repetir efectos terminales. No se promete entrega exactly-once.
Job con timeout 60 s, tres intentos y backoff 10/30 s;
lock de solapamiento 75 s. Configurar `retry_after` mayor que timeout y lock
(default database: 90 s) y una caché compartida con locks para workers múltiples.
Un trabajo recuperado tras caída puede retomar running; no aplica efectos de
publicación. Los adaptadores futuros deben conservar efectos idempotentes.

Al fallar se persisten códigos cerrados, no mensajes de excepción, rutas ni
payloads. El agotamiento registra failed y necesidad de atención. La capa de
lectura futura debe sanear excepciones antes de que el worker las registre en
`failed_jobs`/logs; esta base no envía datos de recibos a lectores externos.

Auditoría `solar_case_events`: eventos recibidos, cola, inicio, reintento,
bloqueo/fin y fallo; claves únicas por evento/ejecución/intento. Actor cuando
corresponde, sin PII ni contenido del recibo. No existe endpoint para editarla.
Nuevos expedientes CRM conservan al actor activo como responsable; los públicos
quedan sin responsable para bandeja. `attention_required_at` registra atención,
no prueba que se haya enviado una notificación. Toma de caso, reasignación,
notificaciones y plazos se implementan en E5.

## Proyecciones E1

`SolarCaseStatus::customer()` solo serializa campos expresos; quien lo invoque
debe haber autorizado el bearer del expediente. No recibe `Request` ni escoge
permisos desde un parámetro del navegador. `advisor()` además verifica policy de
procesamiento del usuario activo. No serializar directamente modelos Eloquent.

Ejemplo de un estado bloqueado público, sin PII ni código técnico:

```json
{
    "schema_version": "1.0",
    "reference": "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx",
    "processing": "blocked",
    "simulated": false,
    "next_action": "advisor_review",
    "result": null,
    "sale_price": {
        "status": "unavailable",
        "reason": "pricing_not_configured",
        "amount_minor": null
    }
}
```

La proyección interna añade `blocker_code` y `purchase_cost` con estado
`unavailable`, motivo `supplier_not_connected` y `amount_minor: null`. No se
sugiere cero ni se deduce costo a partir de venta. E6/E7 implementarán importes
reales y sus snapshots; E1 no tiene fuente económica.

## Contratos de siguientes módulos

- Lector E3: recibe fuente privada tipada (archivo/MIME), devuelve JSON CFE v1.0
  original, identificador/versión, evidencia y resultado de lectura; distinguir
  ilegible, incompleto y fallo técnico. Reutiliza validación existente sin
  sobrescribir el original. `ReceiptProcessor` es el límite de ejecución E1,
  no una sustitución del JSON CFE ni del futuro contrato específico del lector.
  Implementación E3 en validación: `FileReceiptReader`/`ReceiptReading`, persistencia
  y revisión internas; [operación y límites](../receipt-reader.md). La integración
  con `ReceiptProcessor` y rutas sigue en E4/E5.
- HSP E2 implementado mediante `HspProvider`: consulta local por clave INEGI;
  resultado disponible contiene
  HSP, unidad h/día, fuente, período y versión de catálogo/metodología. Resultado
  no disponible incluye motivo, nunca default. Actualizador NASA separado.
- Cálculo E4: consumo/histórico validado, HSP versionada, potencia por panel
  declarada y capacidad de microinversor opcional. Devuelve el resultado del
  motor existente, equipos genéricos y advertencias, con snapshots/versiones.
- Precios E6/E7: estado disponible/no disponible separado por compra/venta,
  importes en centavos, moneda, fuente/versión/vigencia, subtotal/IVA/total e
  instalación excluida. Costos internos nunca en proyección pública.

Cambios incompatibles se versionan; añadir una credencial a un consumidor no
concede acceso al CRM. Pruebas E1 cubren el límite de serialización y policies;
pruebas HTTP completas esperan las rutas de E4/E5. No marcar API usable por
cliente solo por haber publicado este contrato.
