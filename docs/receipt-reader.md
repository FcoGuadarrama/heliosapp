# Lector local de recibos — E3

**Lector local seleccionado posteriormente:** [texto/OCR y cotizador asistido](assisted-quote.md).
Este documento conserva las opciones, límites y evaluación del adaptador Ollama;
no significa que nuevas cargas se envíen al modelo con el driver `source`.

Estado: lector con control de fuente y recorrido CRM operativos localmente.
La carga automática, revisión y dimensionamiento técnico están conectados;
alcance y pendientes en [análisis automático](automatic-receipt-analysis.md).
Los comandos de prueba no publican una propuesta comercial.

Evaluación posterior con cinco recibos reales: [resultados y límites](receipt-reader-real-evaluation.md).
La precisión sintética de abajo no sustituye esos resultados ni el cotejo v3.

## Ejecución

Helios se ejecuta en Mac y Ollama en Windows, con RTX 3060 de 12 GB. El lector usa
`qwen3.5:4b` como opción inicial configurable por un túnel SSH. Solo admite una URL HTTP de loopback literal
(`127.0.0.1` o `[::1]`); no sigue redirecciones. No envía domicilios a un proveedor
externo. El operador debe utilizar un modelo local en Ollama.

Instalar Poppler (`pdftoppm`, `pdfinfo` y `pdftotext`) en el equipo de Helios. CI lo instala
mediante `poppler-utils`. PHP no requiere una extensión PDF ni GD para preparar
los archivos. El adaptador REST adjunta bytes base64, nunca una ruta de Windows.
Contrato contrastado con [Vision](https://docs.ollama.com/capabilities/vision),
[Chat](https://docs.ollama.com/api/chat) y
[Structured outputs](https://docs.ollama.com/capabilities/structured-outputs).

Ejemplo de túnel, sustituyendo identidad y equipo por los del operador:

```bash
ssh -i ~/.ssh/clave_windows -o ExitOnForwardFailure=yes \
  -N -L 127.0.0.1:11435:127.0.0.1:11434 usuario@equipo_windows
```

Configurar las variables de `.env.example`: `HELIOS_RECEIPT_READER_ENABLED`,
`HELIOS_OLLAMA_URL`, `HELIOS_RECEIPT_MODEL`, `HELIOS_OLLAMA_TIMEOUT`,
`HELIOS_PDFTOPPM`, `HELIOS_PDFINFO` y `HELIOS_PDFTOTEXT`. La activación predeterminada es falsa.
Actualizar el cache de configuración según la operación de cada instalación.
El túnel usado para validar es temporal; este módulo no instala un servicio SSH.

JPEG/PNG y PDF: máximo 20 MiB; PDF completo de hasta seis páginas. El lector
verifica MIME real. No trunca un PDF largo: devuelve `pdf_page_limit`. Renderiza
cada página hasta 2400 px, rechaza imágenes mayores a 24 millones de píxeles,
y limita el conjunto de imágenes base64 a 32 MiB. Elimina el directorio temporal
privado tanto al terminar como al fallar.
Cada proceso PDF tiene 45 segundos; inferencia predeterminada 300 segundos,
con conexión de cinco segundos, sin reintentos de inferencia automáticos.

## Contratos y evidencia

`Domain/ReceiptData/FileReceiptReader::read(ReceiptSource)` devuelve
`ReceiptReading`: cuerpo original de Ollama, JSON CFE v1.0 con metadatos del
archivo asignados por Helios, modelo, páginas y versión de prompt.
El prompt y esquema están versionados en `resources/prompts` y
`resources/schemas`. El JSON original del modelo permanece dentro de la
respuesta íntegra: no se pierde al asignar procedencia controlada por Helios.
Los campos desconocidos se conservan y advierten si llegan en una respuesta;
el esquema de generación limita al modelo a los campos del contrato.

El prompt actual es `cfe-vision-v3`. Además de las imágenes, recibe la capa de
texto PDF utilizable, identificada por página y tratada como datos no confiables.
Se conservan v1/v2 para lecturas históricas. Texto vacío o sin marcadores reconocibles
no se adjunta; esto se explica al modelo, que usa imágenes. Fallos de `pdftotext`
o capas mayores a 20 000 caracteres producen un error técnico explícito.

`ReadingAssessment` valida los tipos antes de construir DTOs y reutiliza
`ReceiptImportValidator` para estructura/coherencia. Una cadena numérica, fecha
mal formada o respuesta truncada es una falla técnica del lector. Un consumo
negativo numérico sí produce un recibo rechazado por coherencia. Los fallos
de transporte no equivalen a imagen ilegible ni gastan una segunda carga.

`ReadReceiptFile` guarda una lectura por intake mediante lock y unicidad SQL.
Repetirla devuelve la misma lectura. No mantiene abierta la transacción durante
la inferencia. La respuesta y JSON completos quedan privados en
`receipt_readings`, con versión, validación y estado; el intake conserva su JSON
original. Los modelos nuevos ocultan el contenido privado al serializarse.
Los fallos no incluyen cuerpos del proveedor, imágenes o domicilios en errores.

Estados: `unreadable` cuando el modelo declara `failed`; `rejected` para errores
de coherencia; `validated` si pasa estructura/coherencia sin advertencias
y trae evidencia no vacía de los cuatro campos críticos en páginas existentes;
`needs_review` en los demás casos. No se impone revisión humana universal. Un
JSON solo estructuralmente válido no habilita cálculo; la evidencia declarada por
el modelo y su precisión deben evaluarse con muestras representativas.
Una clasificación del modelo `partial` debida exclusivamente a opcionales no
impresos no impide validar: se requieren datos críticos completos, normalización
válida y evidencia, sin faltantes críticos declarados ni advertencias del lector
salvo `NOT_PRINTED` sobre campos opcionales. Se preserva el `status` original.

## Revisión, normalización y segunda carga

`ReviewReceiptReading` requiere usuario activo, permiso de importar/ver y acceso
al prospecto. Recibe JSON corregido, motivo y revisión esperada; registra cada
versión en `receipt_reviews` con autor, fecha, validación y normalización. No
sobrescribe extracción ni respuesta inicial. Rechaza cambios de procedencia,
ediciones desactualizadas, datos críticos ausentes y errores de coherencia.
Las advertencias solo pueden aceptarse mediante esta intervención explícita.

La normalización mantiene fechas, días impresos y duración calculada por
separado; conserva cada fila histórica y marca filas incompletas y presencia del
periodo actual. Rechaza duplicados exactos y periodos futuros/invertidos al
aceptar. No rellena fechas ni convierte gráfica sin fechas en periodos ficticios.
La entidad se resuelve solo por nombre oficial INEGI, ignorando mayúsculas,
acentos y espacios exteriores. Abreviaturas o nombres ambiguos quedan sin código.
No infiere coordenadas y `location_confirmed` permanece falso.

La normalización no invoca ni altera el motor Syscom. E4 debe componer sus
entradas de forma explícita: el motor existente estima cobertura como cantidad
de periodos por duración actual; no confundirla con la suma de días reales
registrada aquí. Los snapshots conservan ambas fuentes para decidir esa adaptación.

`StoreSecondReceipt` prepara la recaptura desde CRM con autorización interna:
solo tras el primer recibo ilegible, en el mismo expediente/prospecto, conservando
ambos archivos. La operación bloquea el expediente; repeticiones con el mismo
hash devuelven el segundo intake y una tercera carga diferente se rechaza.
Un fallo técnico no habilita ni consume la segunda carga. Tras la segunda lectura
ilegible, corresponde revisión humana, sin más recapturas. El enlace público,
correo, UI y continuación automática corresponden a E4/E5; no existen aquí.

## Operación y verificación

La migración aditiva `2026_10_26_000001_create_receipt_readings.php` crea solo
`receipt_readings` y `receipt_reviews`; `2026_10_26_000002` agrega el snapshot
nullable de normalización a cada lectura. Ambas se aplicaron en MySQL local tras
revisar su SQL. No hacen backfill, no borran contactos ni cambian archivos anteriores.
La normalización también se conserva para lecturas validadas automáticamente;
si no puede obtenerse por datos incoherentes, se registra el motivo y se exige revisión.

```bash
php artisan helios:receipts:read ID_INTERNO
php artisan helios:receipts:probe resources/fixtures/receipts/synthetic-readable.pdf \
  --expected=resources/fixtures/receipts/synthetic-readable.expected.json
```

`probe` no crea prospectos ni guarda extracciones; borra su copia temporal y
reporta estado, validación, tiempo y coincidencias de campos sin imprimir datos
personales. La muestra sintética permite verificar transporte y transcripción;
no sustituye una evaluación de recibos CFE representativos autorizados.

### Evaluación local del 2026-10-26

Muestras generadas por `scripts/receipts/generate_synthetic_fixtures.py`, con
expectativas explícitas junto a cada PDF. Modelos ya instalados, sin descargas.

| Modelo                       | Legible       | Parcial      | Página en blanco                    |
| ---------------------------- | ------------- | ------------ | ----------------------------------- |
| qwen3-vl:8b, ID 9cf69da2b16d | 15/15, 94.2 s | 6/6, 106.2 s | Respuesta incompleta; fallo técnico |
| qwen3.5:4b, ID d8b0f5e9760c  | 14/15, 34.3 s | 6/6, 25.5 s  | 5/5, 42.8 s                         |

La discrepancia de 4b fue solo `extraction.status`: declaró `partial` por
opcionales ausentes, conservando los 14 valores esperados. No se modificaron las
expectativas para ocultar esa diferencia. Se elige 4b inicialmente por estabilidad
de estos tres casos y tiempos; 8b continúa disponible mediante configuración,
sin fallback automático entre modelos. La selección no acredita precisión con
recibos reales ni evalúa todavía documentos multipágina, desenfoque o tablas CFE.

Se solicita `think: false`; el runtime puede emitir razonamiento aun así, como
ocurrió con 8b. Ese contenido no se usa como resultado ni se imprime. Los primeros
ensayos 8b agotaron tiempo/tokens; un ensayo intermedio completó 13/15 en 160.5 s
antes de exigir todas las secciones del esquema. No se cuentan fallos técnicos
como ilegibilidad. `ollama ps` confirmó 8b con 7.8 GB y 100% GPU durante lectura.

El job ahora usa `ReadAndSizeReceipt`, timeout 840 s, locks 900 s y retry_after
960 s en la cola dedicada receipts. Se conserva el bloqueo explícito si el
lector está deshabilitado. Operación, evidencia de fuente y endpoints actuales
en [análisis automático](automatic-receipt-analysis.md).
