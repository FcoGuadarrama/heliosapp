# Carga y análisis automático de recibos

Actualización vigente: [cotizador asistido y extracción local](assisted-quote.md).
El entorno local usa ahora texto/OCR sin modelo generativo para nuevas cargas,
con confirmación obligatoria y borrador interno Syscom. La descripción v3 de abajo
conserva el recorrido Ollama anterior; no es el lector local seleccionado hoy.

Implementado localmente el 2026-10-26. CRM y embebido guardan el archivo privado
y encolan su análisis cuando el lector está habilitado. El detalle del prospecto
muestra progreso, lectura, revisión y dimensionamiento técnico preliminar.
La selección manual y precio de lista existen ahora en borrador interno; PDF de
propuesta, compatibilidad automática y entrega por correo siguen pendientes.

## Qué hace la carga

`ReceiveReceipt` utiliza el almacenamiento existente y solicita el procesamiento
después de conservar el archivo. Fallar al despachar no elimina el recibo: registra
un estado recuperable y un evento sin guardar el cuerpo de la excepción. El
embebido sigue siendo independiente de las cookies CRM; no vincula contactos ni
hereda el usuario autenticado. Sus resultados se consultan en el CRM autorizado;
todavía no existe una página pública de resultados ni envío de propuesta.

El job usa `ReadAndSizeReceipt`, conserva la extracción y se detiene con un motivo
expreso cuando falta lectura, revisión, estado de suministro o HSP vigente.
Los fallos técnicos no se convierten en ilegibilidad ni consumen una recaptura.

En el CRM, el asesor puede cotejar/corregir tarifa, fechas, consumo, histórico e
importes leídos; confirma el estado de la instalación y deja una nota de revisión.
La aceptación se guarda como una versión, sin sobrescribir la lectura original,
y vuelve a encolar el mismo expediente. La revisión exige permiso, usuario
activo, versión vigente y ausencia de un job en curso. Cambiar una lectura ya
calculada invalida el estado de cálculo anterior hasta producir otro snapshot.

## Fuente del dato y precisión

El lector v3 conserva íntegra la respuesta Ollama y produce el JSON CFE v1.0
derivado con procedencia explícita. `CfeTextFacts` reconoce el formato de texto
de CFE: separa etiquetas pegadas, decodifica fechas españolas, distingue lectura
acumulada de consumo, conserva ceros y copia filas históricas completas.
Los años impresos con dos dígitos siguen la interpretación de fecha PHP: 00–69
son 2000–2069 y 70–99 son 1970–1999. Fechas imposibles no se reparan.

Solo declara fuente verificable si identifica los cuatro campos críticos, el
encabezado de hilos/fases y todas las filas históricas candidatas. Etiquetas
críticas contradictorias o una fila no reconocida impiden esa declaración.
No deduce fases de NO HILOS ni días a partir de fechas como si estuvieran impresos.
Los días calculados para ingeniería se mantienen separados en la normalización.

`ReceiptGrounding` concilia datos con el texto literal reconocido y adjunta
evidencia/página y avisos `SOURCE_RECONCILED`; la respuesta del modelo permanece
inalterada y disponible como evidencia. Esos avisos de conciliación no fuerzan
revisión por sí solos si la fuente está verificada y los controles CFE pasan.
Una fuente no verificable recibe `SOURCE_UNVERIFIED` y requiere revisión visual.
El indicador de verificación lo asigna el adaptador, no el modelo.

No se acepta un JSON solo por su estructura: se exige fuente, evidencia crítica,
coherencia y normalización. Las demás advertencias de coherencia mantienen su
revisión obligatoria; no se relajaron reglas monetarias ni umbrales para obtener
un resultado. Las simulaciones antiguas permanecen identificadas y una lectura
con procesamiento real no puede reemplazarse por una fixture.

Reevaluación fresca de los cinco PDF autorizados con v3: 30/30 campos cotejados
(20 críticos más fases/días no impresos); 44/44 filas históricas correctas en los
cuatro archivos con texto verificable. El quinto tiene una fila discrepante y
fuente no verificable, por lo que permanece para revisión; globalmente 54/55.
Tiempos: 51.0/42.2/36.2/40.8/45.5 segundos. Los cinco conservan advertencias CFE
y requieren confirmación en este lote; no se declaran lecturas aprobadas en bloque.
Una imagen WebP sintética en blanco también se probó contra Ollama: 5/5 campos
esperados, clasificación failed, ocho segundos. No se utilizaron datos personales
en pruebas de código ni se incorporaron los recibos a Git.

## Dimensionamiento sin potencia de panel supuesta

El usuario confirmó que todavía no tiene una potencia de referencia por módulo.
El motor admite ese dato ausente: devuelve kWp requeridos, consumo diario e
inversor mínimo de referencia, con cantidad de paneles/potencia seleccionada y
microinversores no disponibles. No introduce 500/550 W ni una cantidad cero.
La pantalla diferencia el resultado técnico de una propuesta comercial.

`AnnualReceiptHistory` selecciona los periodos consecutivos más recientes,
anteriores al actual, hasta cubrir entre 330 y 400 días reales. Conserva índices,
días y criterio de selección en el snapshot. Menos de 330 días reales usa el
periodo actual; huecos, traslapes o datos incompletos bloquean esa adaptación.
El histórico completo permanece en la lectura: no se suman dos años impresos
como si fueran uno ni se pierde la referencia de las filas excluidas.

La fórmula Syscom no cambia: con la selección anual, estima cobertura como
cantidad de periodos por duración del actual y normaliza a 365 si excede 400.
Ambos recuentos (real y motor) quedan registrados. Si la selección real alcanza
330 días pero el motor no, se requiere revisión en lugar de cambiar su fórmula.

Se exige estado de suministro confirmado por el asesor y se consulta el catálogo
HSP estatal NASA local vigente. No se infieren coordenadas ni se consulta un
geocodificador. Cada `solar_calculations` conserva lectura/revisión, normalización,
selección anual, HSP con versión/fuente/vigencia, entradas y resultado/versiones.
La unicidad por lectura/revisión hace idempotentes los reintentos.

## Rutas internas

Todas requieren sesión verificada y acceso al recibo/prospecto. Ninguna es una
API pública por conocer el ID.

| Método | Ruta                            | Permiso y función                                                                        |
| ------ | ------------------------------- | ---------------------------------------------------------------------------------------- |
| GET    | `/intakes/{intake}/analysis`    | receipts.view; proyección privada sin respuesta cruda, archivo ni datos de contacto      |
| POST   | `/intakes/{intake}/analyze`     | receipts.import; iniciar/reintentar análisis                                             |
| POST   | `/intakes/{intake}/review`      | receipts.import; confirmar/corregir con revisión esperada, nota y confirmación explícita |
| POST   | `/intakes/{intake}/replacement` | receipts.import; segunda copia, mismo expediente y máximo dos cargas                     |

Los POST conservan CSRF y throttling. CRM muestra el estado mediante polling
parcial únicamente mientras hay jobs queued/running; los formularios conservan
errores y reciben foco en su resumen. El resultado no expone costo de compra ni
precio de venta. La tercera carga se bloquea en servidor y no se ofrece en UI.

## Operación local

Migración `2026_10_26_000003_create_solar_calculations` aplicada en MySQL, batch 11;
solo crea la tabla y claves nuevas. Todas las migraciones están Ran.

`HELIOS_RECEIPT_READER_ENABLED=true` se activó solo en el entorno local existente,
con caché reconstruida; `.env.example` conserva false para instalaciones nuevas.
Ollama usa qwen3.5:4b por el túnel loopback documentado en
[lector de recibos](receipt-reader.md). El catálogo HSP local está vigente; no se
realiza una consulta NASA por recibo. El equipo Windows debe estar conectado.

La cola dedicada `receipts` evita tomar trabajos antiguos de otras colas:

```bash
php artisan queue:work database --queue=receipts --tries=3 --timeout=840 --sleep=2
```

Timeout 840 s, locks 900 s y retry_after 960 s. Se rechazan colas síncronas o
configuradas con una reserva insuficiente. La inferencia no sucede dentro de la
petición de carga ni de una transacción larga. Las transacciones relacionadas
con una ejecución bloquean primero el expediente para evitar órdenes cruzadas.

`composer dev` incluye un worker receipts si el lector está habilitado. El túnel
SSH sigue siendo un proceso de desarrollo separado; esta sesión dejó activos
túnel y worker para nuevas cargas. No se instaló un servicio de sistema ni un
despliegue remoto; supervisión permanente corresponde a E10.

## Validación y límites de entrega

Pruebas de código aisladas deshabilitan el lector por defecto en phpunit.xml;
los casos de integración lo habilitan con adaptadores/fakes explícitos, sin
contactar la PC ni usar MySQL compartido. Se probó carga CRM y embebida,
autorización, fallas de cola sin perder el archivo, bloqueos de fuente/ubicación,
revisión/continuación, snapshots e idempotencia, selección anual y módulo ausente.

En navegador se usó SQLite y datos sintéticos aislados: login, carga real del
archivo, queued, error de revisión con foco/retención de valores, confirmación y
resultado calculado. Escritorio, 375 px, horizontal, modo oscuro, texto ampliado y
movimiento reducido; sin overflow ni errores JS. Contraste secundario muestreado:
5.58:1 claro y 6.61:1 oscuro. Las pruebas de navegador no crearon contactos en la
base del usuario. Los archivos sintéticos de esa sesión se retiran al cerrarla.

El recorrido interno básico está operativo; no equivale al cierre completo de
E4/E5: quedan contrato/entrega pública privada, ubicación más allá de la entidad,
bandeja y seguimiento/abandono. Equipos, venta propia, Ingeniero IA comercial y
publicación/PDF/correo siguen en sus entregas posteriores. No se declaró precisión
universal para imágenes ni se publicaron propuestas basadas en los ejemplos.
