# RFC-003 — Lectura verificable de recibos CFE en PDF e imagen con IA

- Fecha: 2026-10-27.
- Autor: Francisco Guadarrama <ingfranciscoguadarrama@gmail.com>.
- Estado: definición funcional aceptada; evaluación e implementación pendientes.
  La aceptación del documento no acredita funcionamiento ni activa servicios.
- Primer ciclo TDD implementado: promedio por periodos verificables y entrada
  explícita al dimensionamiento, con pruebas unitarias y de integración. Lectura
  OCR/IA, aceptación condicional y evaluación real por formato siguen pendientes.
- Decisión confirmada por el usuario: revisión humana solo cuando haga falta;
  los recibos verificables deben poder avanzar automáticamente.
- Relacionados: [RFC-001](001-cotizacion-ia-ubicacion-hsp.md),
  [contrato JSON CFE v1.0](../cfe-receipt-schema.md),
  [evaluación de skills](../receipt-ai-skills-evaluation.md).

## Decisión vigente: lectura íntegra por IA

Actualización explícita del responsable de producto: toda lectura de PDF digital,
PDF escaneado, mixto y fotografías corresponde al modelo de IA. Ollama se utiliza
ahora para reducir gasto de tokens durante desarrollo. El proveedor de IA de
producción se elegirá después; el usuario menciona Claude o Codex como candidatos,
sin decidir aún API, producto o modelo concreto.

No invertir en Apple Vision, PaddleOCR ni otro OCR local como lector de producción
ni como vía alternativa del nuevo flujo. El driver `source` y la implementación Apple Vision han sido eliminados;
las lecturas históricas almacenadas se conservan. La preparación
puede validar/renderizar un PDF para entregarlo al modelo; no extrae ni decide
los campos CFE por su cuenta. El modelo recibe el documento o las imágenes de
sus páginas, según capacidades del adaptador, y devuelve datos estructurados y
evidencia. El cálculo, promedio y validación continúan siendo deterministas.

Esta decisión sustituye la anterior selección local de texto/OCR y la restricción
permanente a procesamiento local. La evaluación actual será con Ollama; habilitar
un proveedor de producción requiere configurar su integración y resolver el
tratamiento de datos conforme al contrato vigente. No se activan servicios externos
ni se envían documentos durante esta actualización documental.

## 1. Resultado esperado

### Definición de producto actualizada el 2026-10-27

Estas decisiones aceptadas prevalecen sobre redacciones anteriores incompatibles:

- Extraer únicamente periodo y consumo actual, histórico de consumos, tarifa y
  dirección del suministro. No se requiere nombre del titular, número de servicio,
  importes del recibo ni otros datos personales para este flujo. El contrato v1
  conserva su estructura; sus campos fuera de alcance permanecen vacíos.
- Incluir periodo actual e histórico sin duplicados, aprovechando todos los
  periodos disponibles, incluso 11 bimestres. Promedio diario aceptado:
  suma de kWh dividida entre suma de días cubiertos por esos periodos únicos.
  Mostrar cobertura temporal real y equivalente anual estimado (diario × 365).
- Si no se completa un año, estimar con lo disponible e indicar cobertura real.
  Una fila ambigua requiere revisión del asesor; no descartarla para estimar.
- No deducir fechas o duraciones ausentes para completar el histórico. Trabajar
  con datos disponibles y comunicar faltantes/limitaciones al cliente embebido o
  al asesor en CRM. Ausencia no equivale a contradicción: no exige otra carga por
  sí sola. Sin días verificables no se inventa un promedio diario; el resultado
  dependiente queda pendiente con explicación. Si solo parte de los periodos
  tiene kWh y días verificables, usar esos periodos, identificar los excluidos
  y advertir que la cobertura es parcial; conservar todas las filas extraídas.
- La carga embebida del cliente final usa el mismo análisis. Ambigüedades llegan
  al asesor sin exigir al cliente interpretar el recibo. No publicar cotización
  automática mientras haya datos ambiguos pendientes.
- Usar dirección de suministro como sitio de instalación cuando sea completa e
  inequívoca; pedir confirmación ante faltantes o ubicación distinta. Esta decisión
  no implementa resolución geográfica ni elimina el bloqueo actual de ubicación.
- Atender tarifas soportadas por el motor y dimensionar al 100% del consumo.

La fórmula de dimensionamiento utiliza consumo diario en kWh. Antes del primer
ciclo TDD, `InterconnectionInput` recibía solo consumo del periodo, días e histórico,
y `InterconnectionCalculator` derivaba ese valor internamente. Ahora admite también
`ConsumptionAverage` explícito (total kWh y días cubiertos), que tiene precedencia
sin renormalizar a 365 días. Se conserva la ruta anterior para llamadas existentes.
La referencia Syscom local hace lo mismo mediante `eD` y `e$` antes de calcular
`kwp = consumoDiario / (0.77 * HSP)`. Su política de histórico utiliza
cantidad de periodos por días del periodo actual y, si supera 400 días, fija el
denominador en 365. La aplicación anteriormente seleccionaba 330–400 días previos.
El primer ciclo TDD sustituye esa selección por periodos únicos verificables,
incluido el actual, y conserva política y entradas explícitas en nuevos snapshots.

Decisión confirmada: sumar los kWh de periodos únicos y dividir entre sus
días cubiertos; anualizar el promedio diario multiplicando por 365 únicamente
como referencia. Normalizar y calcular fuera del modelo generativo, conservando
las filas y evidencia extraídas por IA. Incorporarlo requiere política versionada
y adaptación explícita del motor, autorizada e implementada en el primer ciclo
TDD; no enviar 22 meses de consumo divididos entre
365 ni presentar ese cambio como reproducción exacta de la política Syscom actual.

Decisiones del recorrido posterior, registradas también en RFC-001: priorizar
existencia inmediata y después precio; estimar por ahora equipos seleccionados
(paneles, inversor y otros equipos que puedan justificarse con los datos).
No definir techo, estructura o distancias estándar ni prometer una lista completa
de instalación. Se excluye mano de obra. Esto sustituye el alcance previo de
materiales para una instalación promedio.
El cliente embebido recibe automáticamente una estimación de materiales con
precios reales del catálogo cuando pasan los controles; la cotización definitiva
requiere confirmación del asesor. Precio de lista Syscom más IVA; no se añade
margen adicional en este modo. El proveedor configurable por empresa mediante API
o inventario interno es evolución futura, no alcance de esta primera entrega.
Si falta existencia suficiente confirmada, precio o información para comprobar
compatibilidad, detener la estimación y derivar al asesor. No presentar una
selección incompleta como cotización respaldada ni sustituir valores ausentes.

Al cargar un recibo CFE en PDF o imagen, Helios debe obtener datos energéticos fieles al documento,
con evidencia consultable, y decidir si la lectura puede continuar, requiere
corrección humana o necesita otra copia. Una falla de infraestructura debe poder
reintentarse sin pedir otro recibo ni perder el archivo.

La confianza se demuestra con referencias verificadas y errores medidos. Un JSON
bien formado, una explicación convincente o la confianza declarada por el modelo
no bastan para aceptar una lectura.

Este RFC define el lector que alimentará al Ingeniero IA. Selección de equipos,
compatibilidad eléctrica, precios, publicación y envío de propuestas permanecen
en RFC-001; no se consideran resueltos por leer correctamente un recibo.

## 2. Alcance y restricciones

Incluye PDF digital, escaneado y mixto, de varias páginas, con orientación o capa
de texto defectuosa. Debe procesar todas las páginas dentro de límites, sin
truncar el documento para obtener un resultado aparentemente completo.

La primera entrega incluye todos los formatos: PDF digital, PDF escaneado, PDF
mixto y fotografías/imágenes JPEG, PNG y WebP. Ninguna categoría queda para una
entrega posterior. La evaluación y el recorrido integrado deben cubrir cada
categoría por separado; aprobar PDF digital no acredita escaneos o fotografías.

Se mantienen como base los límites actuales: 20 MiB y seis páginas por PDF.
Renderizado/OCR también debe limitar píxeles, texto y memoria; los valores
efectivos deben versionarse y probarse antes de activar el candidato. Un límite
excedido produce causa expresa, nunca pérdida silenciosa de páginas o filas.

No se añade otro motor OCR. Se evalúa lectura íntegra con Ollama durante
desarrollo, usando el contrato común de lectura y evidencia. La selección del
modelo/proveedor de producción se resolverá con resultados y requisitos de
integración; no se confunde con seleccionar un OCR local.

## 3. Estado real y brecha

| Área                | Existe                                                               | Falta para cumplir este RFC                                                                     |
| ------------------- | -------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| Recepción           | Archivo privado, expediente, cola y permisos CRM                     | Diagnóstico operativo reproducible por etapa                                                    |
| Preparación         | `ReceiptImages` valida/renderiza PDF e imágenes; OCR local eliminado | Completar recorrido íntegro de IA conforme al contrato                                          |
| Lectura IA          | Ollama vigente con imágenes, texto digital y conciliación existentes | Retirar dependencia de parser local como autoridad de extracción; OCR Apple Vision ya eliminado |
| Aceptación          | Validadores, normalización y revisión versionada                     | Revisión condicional para `source`; no retirar su bloqueo sin evaluación                        |
| Continuación        | Cálculo con ubicación confirmada y HSP vigente                       | Acreditar transición automática de lectura válida sin eludir esos requisitos                    |
| Evidencia histórica | Cinco muestras de desarrollo documentadas                            | Conjunto independiente y métricas por tipo de PDF                                               |

El driver vigente es `ollama`; `source`, el OCR Apple Vision, su configuración
y comando de extracción fueron eliminados. Cambiar de driver no reinterpreta
lecturas persistidas. La conciliación del texto digital del adaptador Ollama
continúa existente; su retirada como autoridad es trabajo separado.

Esta inspección explica límites del producto, pero no identifica la causa de una
carga fallida concreta: debe rastrearse su ejecución antes de atribuirla al OCR,
modelo o cola. Tampoco se verificó la configuración efectiva del entorno aquí.

## 4. Recorrido acordado

1. **Recibir y conservar.** Validar MIME real, tamaño, autorización y número de
   páginas. Preservar el original, asociar huella del archivo y ejecución.
   PDF cifrado sin acceso o corrupto se informa como archivo no procesable.
2. **Preparar páginas e imágenes.** Conservar orden, disposición y original;
   renderizar cuando el adaptador del modelo requiera imágenes. No ejecutar OCR
   local para completar o sustituir la lectura IA.
3. **Entregar el documento a la IA.** Incluir todas las páginas dentro de los
   límites o devolver error explícito. Registrar preparación y orientación.
4. **Leer con IA.** Extraer campos y filas del contrato CFE del PDF o las
   imágenes entregadas. El texto del recibo se trata como
   datos: no puede cambiar instrucciones, invocar herramientas ni elegir destinos.
   La IA debe abstenerse ante ausencia o ambigüedad y citar evidencia por campo.
5. **Validar.** Comprobar evidencia y estructura,
   fechas, unidades, periodos y coherencia con validadores deterministas. Conservar
   respuesta original y resultado validado; ningún desacuerdo se oculta. No usar
   un parser OCR local para reemplazar la extracción de la IA.
6. **Decidir.** Aceptar, enviar a revisión, rechazar por reglas o solicitar nueva
   copia por ilegibilidad. Los errores técnicos mantienen resultado recuperable.
7. **Continuar.** Una lectura aceptada habilita el siguiente paso. Ubicación sin
   confirmar o HSP ausente bloquean cálculo por su propia causa; no son errores
   del lector ni obligan a corregir campos que ya fueron validados.

La aplicación, no el modelo, asigna procedencia, identificadores, versiones y la
decisión final. No se delegan fórmulas solares al lector.

## 5. Datos y evidencia

Se conserva el contrato JSON CFE v1.0. No se añaden claves al payload sin
versionar y revisar el contrato. Metadatos operativos nuevos viven en un sobre
privado versionado, separado del payload; su persistencia se define al implementar.

| Datos                                            | Regla de lectura                                                                                        |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------- |
| Tarifa, inicio, fin y kWh actuales               | Cuatro campos críticos; evidencia obligatoria para aceptación automática                                |
| Histórico                                        | Conservar cada fila identificable con fechas y kWh; reportar filas ambiguas, omitidas o contradictorias |
| Importes, conceptos e identificadores personales | Fuera del alcance de extracción de este flujo; conservar estructura vacía del contrato                  |
| Domicilio del suministro                         | Extraer dirección para instalación; no completar por conocimiento del modelo                            |
| Fases e hilos                                    | No deducir fases a partir de NO HILOS                                                                   |
| Días                                             | Conservar días impresos si existen; diferencia entre fechas pertenece a normalización derivada          |

Cada campo aceptado debe referenciar página existente y texto literal; cuando la
fuente sea visual, incluir región verificable en coordenadas normalizadas y su
convención. No inventar cajas para texto que carezca de coordenadas. La evidencia
se coteja con la fuente; copiar el mismo número en una cita no demuestra fidelidad.

Los campos opcionales desconocidos quedan nulos y las listas ausentes vacías,
con causa cuando afecten el resultado. Cero solo representa cero observado.
Preservar identificadores con ceros iniciales y precisión impresa; no redondear
consumos para hacerlos coincidir con reglas. Separar lecturas acumuladas del
medidor, consumo del periodo, demanda e importes.

Ejemplo sintético: si el medidor dice 18,400 y el consumo total del periodo dice
520 kWh, el candidato energético es 520, con la etiqueta y ubicación que lo
justifican. Si hay dos totales incompatibles y no se identifica el correcto,
la salida requiere revisión, aunque ambos valores sean numéricos válidos.

Histórico presente pero parcialmente ilegible no se descarta para calcular solo
con el periodo actual. El promedio aceptado utiliza todos los periodos únicos
con días verificables; no suma huecos como días de consumo cero. Duplicados exactos
se cuentan una vez; traslapes o duraciones ambiguas requieren revisión. La política
anual anterior fue sustituida por `all-unique-verifiable-periods-v2`. El lector conserva
el histórico completo. Varios recibos distintos en
un PDF requieren revisión y delimitación; no se fusionan consumos o domicilios.

## 6. Política de aceptación y revisión

Decisión aceptada: revisión solo cuando sea necesaria. Condiciones de aceptación:

- Los cuatro campos críticos tienen evidencia verificada, sin discrepancia
  pendiente, con normalización válida y reglas deterministas aprobadas.
- Todas las páginas se procesaron y el histórico identificado quedó completo o
  justificadamente ausente; no hay mezcla de recibos ni candidatos contradictorios.
- No hay errores ni advertencias bloqueantes. La ausencia de un campo opcional
  que ningún resultado requiera no fuerza revisión por sí sola.
- El candidato y esa clase de documento superaron los criterios de evaluación
  acordados. La confianza numérica del OCR o IA nunca habilita por sí sola.

La política de advertencias será versionada: los faltantes que no impidan el
cálculo se informan y permiten continuar; las ambigüedades o contradicciones en
datos utilizados requieren asesor. No quitar avisos para aumentar automatización.
Las excepciones técnicas se prueban contra esa política antes de activarse.

| Resultado conceptual                   | Efecto previsto                                                                      |
| -------------------------------------- | ------------------------------------------------------------------------------------ |
| Lectura aceptada                       | Estado de lectura `validated`; continúa sin confirmación humana del recibo           |
| Ambigüedad o faltantes corregibles     | `needs_review`; asesor ve original, evidencia, campos y motivo                       |
| Violación de coherencia de nivel error | `rejected`; conservar motivos y permitir corrección autorizada según flujo vigente   |
| Contenido ilegible                     | `unreadable`; solicitar segunda copia cuando corresponda                             |
| Falla técnica                          | Ejecución fallida recuperable; no guardar una lectura aceptada ni consumir recaptura |

Una revisión humana crea una versión con actor y motivo, preservando lectura
original; solo la versión vigente alimenta cálculo. Si se revisa una lectura ya
calculada se conserva el snapshot anterior y se invalida su uso como actual.
Mantener máximo dos cargas por legibilidad en el mismo expediente; después,
atención humana. Datos ausentes y fallos técnicos no gastan otra carga.

## 7. Reintentos, privacidad y operación

Procesamiento asíncrono, sin inferencia en la petición HTTP ni transacción larga.
Registrar etapa, código de causa, duración, versiones y correlación de ejecución.
No incluir domicilio, contacto, texto OCR o respuestas crudas en logs generales.
Original y evidencias son privados y sujetos a permisos del expediente.

Conservar la idempotencia actual: reintentar no duplica lectura, cálculo o lead.
Una reevaluación con otro modelo es una operación explícita y versionada; no
eliminar la lectura existente para forzar un reintento. Toda corrección o nueva
lectura conserva el original y genera una versión trazable. Su persistencia se
adaptará expresamente porque hoy se reutiliza la lectura del intake.

Temporales únicos privados, limpieza incluso ante error y límites de proceso.
No ejecutar contenido del PDF ni construir comandos desde texto extraído.
Mantener presupuestos de tiempo compatibles con worker, locks y reserva de cola;
no cambiar timeouts para esconder una ejecución colgada.

Se usa Ollama para evaluación en desarrollo. En producción se integrará el
proveedor de IA elegido, manteniendo el mismo contrato. El uso de servicios
externos debe conciliarse con el contrato de privacidad antes de habilitarlo;
esta edición no autoriza envíos ni cambia configuración operativa.

La comprobación operativa debe detectar lector deshabilitado, binarios ausentes,
preparación de documentos no disponible, modelo inaccesible, cola sin worker y configuración de reserva
incompatible; distinguir esas causas del resultado documental. Los mensajes al
asesor deben indicar el siguiente paso sin exponer excepciones o credenciales.

## 8. Evaluación reproducible antes de activar

Aplicación de `pdf`: separar PDF digital/escaneado/mixto, conservar disposición y
verificar páginas renderizadas. Aplicación de `llm-evaluation`: referencias humanas,
comparación por campo, regresiones y trazabilidad de modelo/prompt/dataset.
No se copian sus ejemplos Python como infraestructura de Helios.

### Conjunto y referencias

Usar un conjunto representativo de recibos reales que se recibirán: PDF digital,
PDF escaneado, PDF mixto y fotografías/imágenes. No se fija un número arbitrario
de documentos. Documentar cobertura, cantidad por categoría y limitaciones.
Separar desarrollo y evaluación, originales y variantes: rotar el mismo recibo
no crea otra muestra independiente. Añadir casos sintéticos y errores operativos.

Incluir tablas históricas largas, codificación rota, páginas vacías, rotación,
recortes, cifras similares, varios recibos, no-CFE, archivos corruptos/cifrados
y límites excedidos. Una matriz debe indicar qué formatos/tarifas están realmente
representados; clases sin muestras no quedan aprobadas por promedio global.

Dos cotejos humanos y resolución de discrepancias fijan la referencia de campos,
filas y evidencia. Marcar explícitamente lo ilegible: no convertir una conjetura
del anotador en verdad. Mantener archivos y etiquetas privadas, con manifiesto
de huellas y versión; no subir recibos reales a Git. Los cinco PDF de desarrollo
previos son regresiones conocidas, no prueba independiente.

### Métricas obligatorias

| Métrica                    | Definición                                                                                              |
| -------------------------- | ------------------------------------------------------------------------------------------------------- |
| Exactitud crítica          | Valores correctos / campos críticos legibles esperados; abstención en campo legible cuenta como omisión |
| Exactitud por documento    | Documentos con cuatro críticos e histórico correctos / documentos evaluables                            |
| Histórico                  | Filas exactas por fechas/kWh, faltantes y adicionales; no evaluar solo suma anual                       |
| Aceptación errónea         | Documentos autoaceptados con algún crítico o fila histórica errónea / todos los autoaceptados           |
| Automatización             | Autoaceptados / recibos CFE evaluables, también por tipo y calidad                                      |
| Abstención y clasificación | Confusión entre aceptable, revisión, ilegible y no-CFE frente a referencia humana                       |
| Evidencia                  | Campos y filas aceptados con soporte correcto / campos y filas aceptados                                |
| Operación                  | Latencia p50/p95, errores técnicos, reintentos y recursos por etapa                                     |

Reportar conteos y denominadores, no solo porcentajes; denominador cero se declara
no evaluable. Comparar candidatos en exactamente las mismas entradas. Repetir
tres veces las inferencias generativas del conjunto de evaluación para detectar
variación; cada repetición debe cumplir la política de aceptación. Registrar
versiones de binarios, modelo, prompt, parámetros, equipo y arranque frío/caliente.
No usar similitud textual ni otro LLM como árbitro final de números y fechas.

### Criterios acordados de habilitación

- Ningún error crítico observado entre los casos aceptados automáticamente;
  esto incluye consumos, periodos e histórico utilizados en el promedio.
- Ninguna aceptación de no-CFE, mezcla de recibos o documentos incompletos
  presentados como íntegros; evidencia correcta de datos aceptados.
- Medir exactitud, automatización, abstenciones y fallos por categoría. No aprobar
  un lector que simplemente envíe todos los casos al asesor: demostrar aceptación
  correcta en cada categoría legible incluida en la entrega.
- Fallos técnicos recuperables y separados de ilegibilidad; sin duplicados ni
  pérdida del original en pruebas de concurrencia y reintento.
- Medir latencia real por etapa y equipo, sin imponer los 120 segundos propuestos
  anteriormente. Velocidad y elección final del modelo se resuelven con pruebas.

Se eliminan como requisitos los valores arbitrarios de 60 PDF, 95% de exactitud y
70% de automatización. El informe debe indicar tamaño, cobertura e incertidumbre;
ninguna muestra prueba ausencia de errores futuros. Un error crítico autoaceptado
bloquea la versión: corregir y reevaluar con referencias reservadas que no se
utilizaron para ajustar el candidato. No usar precisión universal como promesa.

## 9. Entregas y prueba de cierre

1. **Contrato acordado.** Aplicar las decisiones de sección 10 y matriz de reglas
   a PDF digital, escaneado, mixto y fotografías; Ingeniero IA comercial conserva
   su alcance separado en RFC-001.
2. **Referencia y diagnóstico.** Crear manifiesto privado, referencias y protocolo;
   rastrear un caso fallido desde carga hasta bloqueo para identificar causa real.
3. **Comparación aislada.** Ejecutar candidatos sin crear expedientes comerciales;
   entregar matriz de resultados, errores y limitaciones por clase.
4. **Integración condicional.** Implementar el candidato elegido y política de
   revisión con pruebas de autorización, idempotencia, fallos y evidencia.
5. **Activación controlada.** Verificar worker/preparación/modelo y un recorrido completo:
   cada formato válido → lectura aceptada → cálculo si ubicación/HSP disponibles;
   recibo ambiguo → revisión → corrección → continuación; falla técnica → reintento.

El cierre exige informe reproducible y demostración del recorrido real. Pruebas
con fakes acreditan contratos y errores, pero no precisión del OCR/IA. El rollback
restaura el driver/política anterior y reinicia workers según operación; preserva
lecturas y snapshots. No borra resultados históricos ni los reinterpreta en silencio.

## 10. Decisiones cerradas y trabajo de ejecución

| Decisión aceptada                        | Aplicación                                                                                                           |
| ---------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Formatos completos desde primera entrega | PDF digital, escaneado, mixto y fotografías JPEG/PNG/WebP                                                            |
| Lector IA                                | Ollama en desarrollo; proveedor IA de producción por elegir; sin inversión en OCR local                              |
| Arquitectura                             | Documento/páginas/imágenes → lectura íntegra IA → validación y promedio deterministas                                |
| Revisión condicional                     | Avance automático con evidencia; ambigüedad al asesor; faltantes no impeditivos informados                           |
| Evaluación                               | Conjunto representativo por categoría, referencias cotejadas, sin umbrales arbitrarios de tamaño/porcentaje/latencia |
| Habilitación                             | Ningún error crítico observado en autoaceptados y demostración real de cada formato                                  |
| Corrección y relectura                   | Original preservado, nueva versión trazable, sin sobrescritura de resultados históricos                              |

Las decisiones de producto quedan cerradas. Quedan tareas de ejecución, no
propuestas de producto: preparar referencias, medir candidatos y tiempos, elegir
modelo por resultados, adaptar persistencia y probar la matriz de advertencias.
Los ADR se redactarán después; este cambio documental no los crea ni implementa
el lector. Las decisiones técnicas elegidas mediante pruebas se registrarán con
su evidencia, sin anticipar resultados.

## Referencias internas verificadas

- [Lector actual y evaluación histórica](../receipt-reader.md).
- [Modo source y cotizador asistido](../assisted-quote.md).
- [Carga, revisión y cálculo](../automatic-receipt-analysis.md).
- Código vigente: `ReceiptImages`, `ReceiptText`, `OllamaReceiptReader`,
  `ReceiptGrounding`, `ReadReceiptFile`, `ReadAndSizeReceipt` y validadores CFE.

Validación documental: lectura de implementación y contratos locales, skills
`pdf` y `llm-evaluation`, revisión de enlaces y diff. Sin inferencias, benchmark,
cambios de configuración ni implementación nueva durante su redacción.
