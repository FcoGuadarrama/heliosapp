# RFC-001 — Flujo compartido de recibo a cotización solar

- Fecha de consolidación: 2026-10-22.
- Autor: Codex, con decisiones del responsable de producto.
- Estado: definición consolidada; sin preguntas de producto pendientes (seis resueltas el 2026-10-22).
- Implementación: parcial; motor/manual, fundamentos E1 y catálogo NASA E2. Flujo automático completo pendiente.
- Alcance: interconexión CFE, catálogo Syscom, cotización automática y seguimiento.

Orden de implementación, inventario y dependencias del producto completo en
[plan del proyecto](../project-plan.md). Este RFC define comportamiento, no una
secuencia por número de sección. Su nombre anterior era “Cotización con
Ingeniero de IA”; se conserva la ruta para no romper referencias. Las seis
preguntas de producto cerradas no eliminan pendientes técnicos de implementación.

Este RFC reúne todas las decisiones de esta conversación y sustituye sus
redacciones anteriores. Es la especificación vigente de este flujo. El
[PDF de junta](../meetings/2026-10-22-cotizacion-ia.pdf) y su
[fuente editable](../meetings/2026-10-22-cotizacion-ia.md) son material de presentación;
no sustituyen este RFC. La [referencia Syscom](../syscom-calculator-reverse-engineering.md)
sigue siendo la fuente de verdad de las fórmulas.

## 1. Objetivo y alcance

Helios es una aplicación B2B para empresas instaladoras solares en México, con
CRM, ingeniería, presupuestos, cotizaciones y seguimiento. Cada empresa instala
su instancia y administra usuarios, permisos e identidad. No es una tienda de
paneles ni se requiere que sus usuarios sean compradores de Syscom.

El flujo de esta entrega transforma un recibo CFE asociado a un prospecto en una
cotización de equipos. El **Ingeniero de IA** conduce el análisis y elige los
equipos usando datos validados del recibo, ubicación geográfica, recurso solar,
fórmulas y catálogo. Los motores deterministas calculan y validan sus decisiones.

### Incluido

- Solo interconexión CFE, con objetivo del 100% del consumo diario calculado.
- Entrada desde asesor en CRM y desde formulario/dropfiles embebido.
- Generación automática, correcciones en el formulario y derivación humana.
- Catálogo de equipos Syscom sin requerir acceso a precios de compra.
- HSP por estado de México derivado de NASA, con actualización periódica.
- Precio de venta según perfiles de empresa: kWp, panel o paquete.
- Subtotal, IVA y total; **instalación siempre aparte**.
- Edición del asesor, descuentos, versiones, entrega y acciones del lead.

### Evolución posterior

Aislado e híbrido deben poder añadirse separando reglas y entradas por sistema,
pero no se implementan ahora. Otros proveedores y catálogo/inventario por usuario
son ideas futuras: no se construye infraestructura multiproveedor en esta entrega.
Pagos, firma contractual, reserva de stock y contratación de instalación no están
incluidos. Un diseño ejecutivo requiere verificaciones físicas y profesionales.

## 2. Dos canales y generación automática

| Canal                       | Entrada                                                         | Resultado                                                                                                     |
| --------------------------- | --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| Asesor en oficina/CRM       | Registra o selecciona al lead y carga su recibo                 | Cotización interna automática; puede incluir equipos bajo pedido identificados                                |
| Lead en formulario embebido | Registra datos de contacto y adjunta recibo; correo obligatorio | Guardar en CRM, analizar automáticamente y entregar cotización por correo cuando cumpla reglas de publicación |

No exigir creación manual de proyecto, revisión previa universal ni un botón
posterior de generar. Ambos canales usan el mismo caso de uso con políticas de
disponibilidad distintas. El embebido conserva su expediente durante correcciones;
no crea otro lead por recargar ni da acceso al CRM por coincidir correo/teléfono.
Se mantienen las reglas del intake de consentimiento, archivos privados y duplicados.

### API compartida y precios por destinatario (aceptado 2026-10-23)

El formulario del cliente captura datos de contacto y el archivo del recibo CFE;
no ofrece captura manual de consumo ni parámetros técnicos. El asesor crea el
prospecto en CRM y le sube el archivo. Ambos recorridos consultan la misma API
y ejecutan el mismo procesamiento y cálculo; no son dos calculadoras distintas.

El resultado técnico es común para las mismas entradas. La respuesta autorizada
al asesor incluye precios internos y externos (precio cliente); la respuesta
al cliente incluye únicamente el precio cliente. La API determina el acceso
según identidad, permisos y expediente autorizado; los precios internos se
excluyen de la respuesta pública en el servidor, no solo de su presentación.
La selección de un destinatario enviada por el formulario no concede permisos.
Se conservan las políticas de disponibilidad por canal descritas en la sección 8.

**Precios confirmados el 2026-10-23:** interno significa costo de compra de los
equipos; externo significa precio de venta al cliente. La fuente del costo de
compra sigue pendiente mientras no exista integración con precios del proveedor
u otra fuente acordada. No deducir costos desde el precio de venta ni inventar
importes cuando falte una fuente válida. Las tarifas de venta siguen las reglas
de la sección 9; costo ausente no equivale a cero.
La API compartida y ambos formularios aún no están implementados como flujo
completo; la pantalla manual existente es una herramienta provisional.

La calculadora de `ml-syscom-panel` es referencia funcional del resultado:
resumen de consumo, potencia solar e inversor, seguido de necesidades/equipos
con cantidades y desglose económico según autorización. Durante la etapa sin
API de proveedor, las necesidades son genéricas y no acreditan marcas, stock
ni costos de compra. La referencia observada usa catálogo de prueba: sus
productos, importes y existencias no son datos reales para cotizar en Helios.

La recepción confirma el envío y muestra progreso. Las correcciones necesarias
por legibilidad y ubicación se resuelven **en el mismo formulario**, aun cuando el
procesamiento se ejecute en segundo plano. Correo para corregir imagen o confirmar
mapa no forma parte del recorrido normal.

**Recuperación tras abandono (aceptado 2026-10-22):** si el lead cierra el
formulario con un paso pendiente (segunda carga, confirmación de mapa u otra
corrección), se envía **un único correo recordatorio** con enlace privado que
reabre el mismo expediente en ese paso, conservando el conteo de cargas. Es la
única excepción al recorrido sin correos y solo aplica en caso de abandono. Si
no se completa dentro de un plazo configurable, el caso pasa a **“Pendientes de
atención”** (sección 12). El enlace no da acceso al CRM ni a otros expedientes.

## 3. Responsabilidad del Ingeniero de IA

Su tarea principal es analizar la instalación y escoger los equipos adecuados,
no limitarse a extraer el recibo o narrar una selección fija.

1. Obtener el [JSON interno CFE](../cfe-receipt-schema.md) y su validación.
2. Considerar consumo actual e histórico, períodos/días, tarifa, fases, tensión,
   demanda y demás datos útiles disponibles, con evidencia y faltantes explícitos.
3. Considerar domicilio del suministro, ubicación aceptada, entidad y HSP estatal.
4. Solicitar dimensionamiento a herramientas que implementan las fórmulas Syscom.
5. Consultar productos reales, comparar candidatos y solicitar validaciones.
6. Seleccionar configuración y solicitar cantidades calculadas, sin inventarlas.
7. Aplicar el perfil económico configurado cuando corresponda y explicar elección,
   supuestos, advertencias y datos pendientes con referencias verificables.

Los identificadores seleccionados deben existir en el catálogo consultado. La
IA no inventa precios, productos, especificaciones, fórmulas ni tasas fiscales.
Una configuración incompatible no pasa por preferencia del agente. Ante falta de
información, declarar condición/bloqueo; no fabricar una propuesta completa.
La dirección no acredita por sí sola techo, sombras, estructura ni acometida.

Guardar contexto utilizado, herramientas y resultados, alternativas, selección,
modelo/versión y justificación verificable. No se requiere guardar razonamiento
interno del modelo. Un cálculo se reproduce con entradas y versiones congeladas;
no exige que una nueva llamada al modelo tome exactamente la misma decisión.
**Elección entre configuraciones válidas (aceptado 2026-10-22):** cuando varias
combinaciones cumplen compatibilidad, cobertura y disponibilidad según canal, el
**Ingeniero de IA decide** cuál publicar con base en los datos analizados. No hay
lista de marcas ni ranking configurado por la empresa. Las restricciones
obligatorias no son negociables, y la justificación guardada debe citar
atributos verificables del catálogo y resultados de herramientas, sin inventar
atributos (por ejemplo, garantía si Syscom no la reporta).

## 4. Motor nuevo y base Syscom

Construir desde cero en Helios usando el reverse engineering y el bundle local
como especificación. **No copiar ni adaptar el PHP de `ml-syscom-panel`.** Las
110 pruebas reportadas de ese otro proyecto no validan la implementación nueva.

Para interconexión:

```text
kWp requeridos = consumoDiario / (0.77 × HSP)
inversor mínimo W = ceil(1000 × kWp / 1.2)
```

- Dimensionar al 100% del consumo diario, sin porcentaje configurable inicialmente.
- Si el histórico cubre al menos 330 días, usar promedio diario para interconexión.
- Verificar en el bundle las ramas de históricos menores y redondeos; no inventar
  reglas a partir de un resumen. Por aparatos, la referencia usa suma de
  watts × horas/día × cantidad / 1000; la entrada prioritaria es recibo CFE.
- Seleccionar módulos completos para alcanzar potencia; mostrar potencia requerida
  y seleccionada, que puede superarla por redondeo.
- Mantener corrección de cantidad de microinversores según módulos admitidos.
  La regla de híbrido solo con inversor híbrido se conserva para ampliación futura.
- Cubrir energía no garantiza eliminar todo el importe del recibo.

Las validaciones eléctricas son deterministas y declaran datos faltantes: tensión,
corriente, rangos MPPT, compatibilidad módulo/inversor y restricciones aplicables.
No afirmar comprobaciones completas si el catálogo no contiene sus parámetros.
La adopción Syscom no define tarifas CFE, compensación ni impuestos no documentados;
no emitir ahorro o retorno sin sus datos y reglas. Ver [ADR-012](../adrs/012-motor-syscom.md).

## 5. Calidad del recibo: dos cargas

| Situación                          | Acción                                                                                                                                                                                                                                                          |
| ---------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Recibo legible y datos suficientes | Continuar automáticamente                                                                                                                                                                                                                                       |
| Primera carga ilegible             | Explicar problema y consejos en el formulario; permitir otra imagen o PDF original                                                                                                                                                                              |
| Segunda carga ilegible             | Derivar a asesor, avisar al lead y cerrar recaptura automática                                                                                                                                                                                                  |
| Error del servicio de lectura      | Tratar como fallo técnico; no consumir intentos de legibilidad                                                                                                                                                                                                  |
| Dato no impreso en recibo legible  | Declarar faltante; no pedir otra foto como si resolviera ese dato. Si el cálculo o una validación obligatoria lo requiere, derivar a asesor sin publicar cifra; si no, continuar y declararlo como advertencia en la propuesta. No pedir datos técnicos al lead |

Son **dos cargas en total**, conservando archivos e historial en el mismo
expediente. No interpretar automáticamente un fallo del modelo como imagen mala.
Reintentar un job o reenviar la misma petición no consume otra oportunidad.
Solo imports validados o aceptados mediante revisión pueden alimentar cálculo real;
un JSON estructuralmente válido no prueba extracción correcta.

## 6. Ubicación y geocodificación simulada

Usar `service.service_address` del contrato CFE v1.0, correspondiente al suministro,
no domicilio fiscal ni ubicación actual del teléfono. La ubicación derivada se
conserva fuera del JSON; no agregar campos de coordenadas al contrato sin versionarlo.
Guardar original, evidencia, correcciones, coordenadas, procedencia y aceptación.

| Semáforo | Significado                                                                  | Acción                                                    |
| -------- | ---------------------------------------------------------------------------- | --------------------------------------------------------- |
| Verde    | Coincidencia suficientemente precisa, sin ambigüedad según reglas explícitas | Aceptar automáticamente y continuar                       |
| Amarillo | Aproximación, datos incompletos o varias coincidencias                       | Confirmar punto en mapa dentro del mismo formulario       |
| Rojo     | No hay ubicación utilizable                                                  | Corregir dirección o colocar punto en el mismo formulario |

Mostrar texto además del color. Señales del geocodificador determinan el nivel,
no un porcentaje generado por IA. Confirmar manualmente acepta el punto, pero no
convierte la calidad del proveedor en verde ni certifica condiciones del techo.
No elegir automáticamente un centro municipal o la capital como sitio preciso.
Al confirmar, resolver entidad y HSP y reanudar automáticamente; no consume cargas.

**Etapa actual:** simular consulta a Google con fixtures verde/amarillo/rojo/error.
No exigir API key, cuenta de facturación ni llamadas reales para avanzar.
Proveedor real, criterios reales, costos y tratamiento del domicilio se revisarán
cuando se aborde lectura real de archivos con IA; no bloquear ahora por esas decisiones.
No presentar coordenadas mock como verificadas ni generar propuestas reales con
fixtures. El mapa debe poder probarse sin activar servicios pagos.

El contrato actual prohíbe enviar domicilios a terceros: el uso de Google real
no quedó habilitado. No enviar PDF, nombre o RPU a un geocodificador.
Ver [ADR-013](../adrs/013-ubicacion-desde-recibo-cfe.md).

## 7. Catálogo HSP estatal desde NASA

Mantener catálogo de las 32 entidades mexicanas con HSP derivado de NASA y
actualización periódica. **No consultar NASA por cada lead.** La ubicación
resuelve entidad y aporta contexto; el recurso utilizado se identifica como
estatal, no como radiación medida en el techo.

Consultar NASA desde un proceso de actualización separado. Parámetro validado
en E2: `ALLSKY_SFC_SW_DWN`, comunidad `RE`, con contrato y unidades comprobados.

```text
HSP (h/día) = irradiación diaria (kWh/m²/día) / 1 (kW/m²)
```

Esta es conversión dimensional; no aplicar directamente a unidades distintas.
Documentar período climatológico, puntos/malla, agregación y conversión antes de
publicar valores: una consulta a la capital no constituye promedio estatal.

Guardar entidad, fuente, parámetro, comunidad, período, puntos/malla, valores y
unidades originales, método/versiones, HSP, fecha y evidencia. Cada cálculo
congela entidad, HSP y versión. Validar nueva versión antes de publicar; una
actualización fallida conserva última versión válida y notifica internamente.
Sin entrada válida, bloquear cifras dependientes; no sustituir silenciosamente
por 5.0 o tabla Syscom. Metodología y política implementadas en E2: pesos de área
INEGI sobre celdas NASA de 1°, actualización mensual y caducidad inicial de
90 días configurable. Catálogo real de 32 entidades, comandos y consulta local
disponibles. Ver [ADR-014](../adrs/014-hsp-nasa-power.md) y
[operación/metodología](../hsp-catalog.md).

## 8. Catálogo Syscom y reglas de disponibilidad

Primer y único proveedor inicial: Syscom. Acceso desde servidor:

```text
GET https://www.syscom.mx/api/energia/productos?tipo=…&limit=60
```

La evidencia del reverse engineering documentó 200 sin sesión con `precio1: null`
y atributos técnicos normalizados. Es un endpoint **interno**, sin contrato
oficial estable; revalidar respuesta al integrar. No asumir que 60 productos son
catálogo completo ni inventar paginación. Roles documentados: panel, inversor,
batería, controlador, cable, protección, montaje y kit; revisar los necesarios
para interconexión y filtros de inversor. No se requieren credenciales de precios.

Aislar el adaptador del motor y del agente. Conservar ID/SKU, fabricante/modelo,
atributos, confianza, disponibilidad, cantidades y fecha. No completar atributos
con IA. No confundir falta de stock informado con cero ni con existencia.
El criterio de precio de compra se omite del ranking porque no está disponible.

| Destinatario y acción                                  | Regla                                                                                      |
| ------------------------------------------------------ | ------------------------------------------------------------------------------------------ |
| Cotización interna de lead ingresado por asesor en CRM | Puede incluir bajo pedido, claramente identificado                                         |
| Alternativas internas del asesor                       | Pueden incluir bajo pedido y faltantes identificados                                       |
| Publicación automática al lead embebido                | Solo configuración completa compatible con existencia suficiente para todas las cantidades |
| Existencia parcial, desconocida o sin stock suficiente | No cumple publicación automática; buscar otras combinaciones                               |
| Sin combinación completa disponible                    | Derivar a asesor y avisar al lead; no publicar lista incompleta                            |
| Envío manual por asesor                                | Puede incluir bajo pedido identificado en nueva versión y PDF                              |

Aplicar política antes de seleccionar/publicar, no esconder después renglones
inconvenientes. Borradores internos no se exponen por reutilizar un enlace.
La excepción de envío manual no habilita bajo pedido en generación automática.
No inventar fecha de entrega ni prometer reserva. Guardar consulta y revisar su
vigencia antes de envío; la política de frescura debe definirse técnicamente.

## 9. Precio de venta: kWp, panel o paquete

La empresa configura precios propios, sin depender de conocer costo de compra
Syscom. El objetivo es **un solo importe real o muy cercano a la realidad de su
oferta**, no rango ni aproximación libre. La precisión depende de tarifas vigentes,
completas y condiciones explícitas. No afirmar precios de proveedor inexistentes.

| Modalidad aceptada | Base propuesta para concretar implementación                  |
| ------------------ | ------------------------------------------------------------- |
| Por kWp            | Potencia DC seleccionada de módulos × tarifa por kWp          |
| Por panel          | Número de módulos × tarifa con condiciones de potencia/modelo |
| Por paquete        | Precio de composición y alcance configurados                  |

Las modalidades están aceptadas; condiciones concretas se configuran por empresa.
La empresa define reglas según caso, no una modalidad fija universal. Ejemplo
ilustrativo: paquete si coincide y, si existe regla expresa, kWp como alternativa.
Evaluar condiciones mediante herramientas; el agente no elige tarifa por intuición.
Una modalidad base por cálculo, sin sumar paquete, kWp y panel para el mismo alcance.
No extrapolar paquetes sin reglas expresas. Prioridad explícita resuelve múltiples
coincidencias; ausencia o conflicto de perfil no se rellena con precio inventado.

Perfiles versionados declaran tarifa, moneda, vigencia, condiciones, inclusiones
y exclusiones. El precio por conceptos no fue seleccionado como modalidad inicial.
La selección del paquete no puede forzar equipos incompatibles o no disponibles
para hacer coincidir una tarifa.

**Alcance (aceptado 2026-10-22):** cada perfil, sea kWp, panel o paquete, declara
explícitamente qué conceptos cubre su importe (módulos, inversor, estructura,
cableado, protecciones u otros). Lo no cubierto se muestra como **“No incluido”**
en CRM, página privada y PDF; no se suma aparte ni se cotiza automáticamente.
La instalación siempre queda excluida, aunque el perfil no lo diga.

### Instalación siempre excluida

**Todos los precios capturados son SIN IVA Y SIN INSTALACIÓN.** El configurador
del CRM debe indicar antes de capturar y junto a los campos de cada modalidad:

> Captura precios sin IVA y sin instalación. El IVA se calcula por separado.
> La instalación se cotiza por separado.

Toda cotización debe mostrar visiblemente:

> **La instalación no está incluida. Se cotiza por separado.**

Aplica a CRM, página pública privada, PDF y cualquier resumen económico del correo.
No tratar instalación como cero, incluirla en paquete ni sumarla automáticamente.
Los importes se identifican como equipos sin instalación, no total instalado.
El agente no puede omitir la leyenda. El sistema exige esta convención, aunque
no puede verificar por sí mismo cómo la empresa construyó un importe capturado.

### Subtotal, IVA y total

Mostrar los tres separados, con un solo total de venta. Calcular IVA una sola vez
según regla configurada vigente; no se acordó tasa específica. Guardar base, tasa,
importe y regla. Dinero en centavos y política de redondeo explícita, según ADR-008.
Sin regla necesaria, no inventar IVA cero. No recalcular propuestas publicadas al
cambiar tarifas. Desarrollo técnico sin precio es una etapa de prueba posible;
**no sustituye el requisito comercial de cotización con precio configurado**.
Si falta perfil válido (aceptado 2026-10-22): el lead embebido **pasa a asesor
sin importe**, con el mismo aviso que un fallo bloqueante (sección 12). El asesor
ve el resultado técnico interno y la causa; no se publica ninguna cifra.

## 10. Edición, descuentos y publicación manual

El asesor autorizado puede modificar modelos y cantidades. Solicitar recálculo,
validación de compatibilidad y actualización de subtotal/IVA/total; mostrar si
pierde cobertura. No publicar como viable una configuración incompatible.

Permitir **descuento por porcentaje o monto fijo, decidido por el asesor**.
No se acordó aprobación comercial adicional ni un tope comercial específico.
Validar límites matemáticos para no generar montos negativos o descuentos mayores
que su base; porcentaje entre 0 y 100.

**Base y acumulación (aceptado 2026-10-22):** se permite descontar **por concepto**
y **sobre el subtotal**. El asesor elige **un solo tipo por versión** (porcentaje
o monto fijo) y todos los descuentos de esa versión usan ese tipo. Orden: primero
descuentos por concepto, después el del subtotal sin IVA, y el IVA se calcula sobre
el subtotal ya descontado. La instalación queda fuera (se cotiza por separado).
No otorgar por ello capacidad de modificar tasas fiscales.

Editar en **borrador**, sin avisos por cada ajuste. Al pulsar
**“Enviar actualización al lead”**, validar/recalcular, publicar nueva versión y
notificar por correo. Puede incluir bajo pedido identificado. Conservar versión
anterior, actor, fecha, cambios y descuento. Separar publicación de estado de envío:
un correo fallido se reintenta sin duplicar versiones o mensajes.

## 11. Entrega, versiones y acciones del lead

Correo con identidad de la empresa y botón **“Ver mi propuesta”**. Enlace privado,
sin cuenta, a una versión concreta y su PDF descargable. **Sin PDF adjunto** en
la primera entrega. La URL no lleva datos personales ni autoriza por ID predecible.
Usar acceso no predecible y revocable; PDF privado bajo la misma
autorización, sin exponer CRM, otros leads ni recibos por coincidencias de contacto.

Conservar cada versión publicada con insumos congelados. Una actualización crea
otra versión/enlace, no sustituye silenciosamente lo ya entregado. Registrar
publicación, generación PDF y estado de correo por separado, con idempotencia.

| Acción del lead    | Comportamiento aceptado                                                                    |
| ------------------ | ------------------------------------------------------------------------------------------ |
| Solicitar contacto | Registrar y notificar responsable o bandeja                                                |
| Pedir cambios      | Recoger petición y generar seguimiento para asesor; no editar automáticamente lo publicado |
| Aceptar propuesta  | Registrar aceptación de esa versión y avisar al asesor para continuar                      |

Aceptación no implementa pago, firma, reserva de existencias ni instalación.
Conservar historial sin borrar aceptación anterior al crear otra versión.

### Versiones vencidas y sustituidas (aceptado)

- **Vencida:** el enlace sigue abriendo la versión con aviso “Vencida” y su PDF.
  “Aceptar” se sustituye por **“Solicitar actualización”**, que registra la
  petición y notifica al responsable o a la bandeja. El asesor revisa y publica
  nueva versión; no hay renovación automática.
- **Sustituida:** al publicar una nueva versión, **se revoca el enlace de la
  anterior**; deja de abrir su contenido y PDF. Solo la versión vigente es
  consultable y aceptable. La versión anterior y su historial (incluida una
  aceptación previa) se conservan internamente para el CRM.

### Duración y recuperación del enlace (aceptado 2026-10-22)

- El enlace de la versión vigente **no expira por tiempo**: sigue abriendo
  después del vencimiento, en modo “Vencida” (ver arriba). Solo deja de abrir
  al ser **sustituida** o **revocada manualmente** por un asesor autorizado.
- Un enlace revocado muestra una **página neutra** (“Este enlace ya no está
  disponible”) sin datos de la propuesta ni indicio de otras versiones, con un
  único botón **“Contactar asesor”** que registra una solicitud de contacto y
  notifica al responsable o a la bandeja. No hay reenvío automático: el asesor
  reenvía desde el CRM. El botón no consume cargas ni crea versiones.

### Vigencia por empresa

Configurable por empresa, guardando emisión/vencimiento por versión. Existe
`CompanySetting::proposal_validity_days` y campo en página Empresa: reutilizar
si corresponde. Cambiar configuración no reescribe versiones anteriores.
Distinguir vigencia comercial, expiración del enlace y antigüedad del stock.
Comportamiento al vencer: ver “Versiones vencidas y sustituidas”.

## 12. Fallos y atención humana

Si un fallo técnico bloquea el recorrido y requiere derivación, **avisar al lead
que su caso pasó a un asesor**, tanto en formulario abierto como por correo.
El asesor recibe error/contexto para atención personal lo antes posible.
Mensaje sugerido, sin plazo prometido: “Tu solicitud pasó a un asesor para
revisión. Te contactaremos para dar seguimiento.” No revelar detalles técnicos.

Aplica a Syscom y otros servicios necesarios, no solo proveedor de equipos.
Reintentos recuperados sin derivación no anuncian un traspaso que no ocurrió.
Errores técnicos no consumen cargas. Mantener expediente, registrar incidentes
y evitar avisos duplicados. Falta de una combinación disponible también deriva
al asesor. La actualización NASA se maneja separada del lead mientras exista
un valor válido del catálogo.

### Asignación aceptada

1. Mantener asesor responsable activo y autorizado si ya existe.
2. Si no hay responsable, enviar a **“Pendientes de atención”**, solo usuarios autorizados.
3. **“Tomar caso”** asigna un único responsable, de manera atómica y auditada.
4. Si nadie lo toma, avisar al administrador tras plazo configurable.

Sin reparto rotativo en esta entrega. No inventar plazo de respuesta ni dejar
casos asignados a usuarios inactivos/inaccesibles. La configuración del plazo
y resolución administrativa son parámetros de implementación.

## 13. Persistencia y estados propuestos

Este es diseño para implementación, no tablas existentes ni migraciones realizadas.

| Registro             | Contenido mínimo                                                                                                                         |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Intake/expediente    | Canal, prospecto, archivos privados, consentimiento, cargas y estado de procesamiento                                                    |
| Validación CFE       | JSON original, evidencia, versión de lector, validación y correcciones auditadas                                                         |
| Ubicación            | Dirección original/corregida, coordenadas, entidad, señales, nivel, origen de aceptación y política versionada                           |
| Catálogo HSP         | Entidad, valor, fuente, período, agregación/conversión, versión y evidencia                                                              |
| Catálogo consultado  | IDs, atributos, disponibilidad, fecha y respuesta fuente                                                                                 |
| Selección IA         | Contexto/procedencia, candidatos, IDs elegidos, herramientas/resultados, modelo y justificación                                          |
| Cálculo              | Entradas, HSP/versión, motor/reglas, resultados y advertencias                                                                           |
| Perfil económico     | Modalidad, tarifa/reglas, alcance, impuestos, vigencia y versión                                                                         |
| Cotización/versiones | Cálculo y selección, cantidades, precio, descuento, subtotal/IVA/total, exclusión de instalación, responsable, publicación y vencimiento |
| Seguimiento/entrega  | Asignación, incidentes, acciones del lead, acceso privado, PDF y estados del correo                                                      |

Separar estados del import CFE, calidad geográfica, procesamiento, publicación y
entrega. Una propuesta generada no implica enviada; una toma de caso no implica
resuelto. No romper DTOs/estados actuales para reutilizarlos con otro significado.
Servicios de aplicación y contratos de herramientas, sin construir un orquestador
propio de agentes. El dominio no depende de controladores ni del cliente Syscom.

## 14. Etapa simulada y criterios de aceptación

**Estado actual:** lectura de archivos mock existente; Google también debe
simularse. Se implementó motor puro de interconexión y pantalla interna de
dimensionamiento manual de prueba, sin IA, catálogo ni precios. Ver
[guía de uso y límites](../solar-engineering.md#5-primera-entrega-de-dimensionamiento).
E1 incorpora expediente/cola/auditoría y E2 el catálogo HSP NASA real versionado.
Siguen pendientes Ingeniero IA, catálogo Syscom, validación eléctrica completa,
presupuestos, versiones, correo/PDF de producto, bandeja y acciones públicas.

| Área        | Validación necesaria                                                                                              |
| ----------- | ----------------------------------------------------------------------------------------------------------------- |
| Motor       | Pruebas independientes de interconexión, históricos, redondeos y microinversores; sin copiar PHP externo          |
| IA          | Selección con herramientas; rechazar IDs inventados e incompatibilidades; faltantes y justificación trazables     |
| Formulario  | Dos cargas, correcciones sin salir, mapa y continuación; expediente único y autorización                          |
| Mocks       | Casos verde/amarillo/rojo/error explícitamente simulados, sin llamadas Google ni propuestas reales desde fixtures |
| NASA        | Contrato real, unidades, agregación de 32 entidades, publicación versionada y actualización fallida               |
| Syscom      | Contrato real, cobertura, atributos ausentes, stock suficiente por cantidad, error y bajo pedido según canal      |
| Presupuesto | Tres modalidades por reglas, un precio, captura sin IVA/instalación, desglose, descuentos y redondeo              |
| Publicación | Snapshot, borrador/manual, enlace por versión, PDF privado y leyenda de instalación en todas las salidas          |
| Fallos      | Aviso lead/asesor, expediente conservado, incidentes y reintentos sin duplicados                                  |
| Seguimiento | Responsable/bandeja, toma concurrente, alerta, contacto/cambios/aceptación y permisos                             |

El mock sirve para pruebas y demostración; no extrae datos reales del PDF y no
habilita cotizaciones reales. Completar lectura IA y servicio geográfico reales
será otra integración; sus costos no son bloqueo de esta etapa.

## 15. Preguntas de producto: resueltas

Las seis preguntas se respondieron una por una el 2026-10-22. No reabrirlas sin
indicación del usuario.

1. ~~**Vencimiento y versiones sustituidas.**~~ **Resuelta 2026-10-22**, ver
   sección 11.
2. ~~**Descuentos.**~~ **Resuelta 2026-10-22**, ver sección 10.
3. ~~**Alcance económico y falta de tarifa.**~~ **Resuelta 2026-10-22**, ver sección 9.
4. ~~**Preferencia entre equipos válidos.**~~ **Resuelta 2026-10-22**: decide el
   Ingeniero de IA, ver sección 3.
5. ~~**Acceso privado.**~~ **Resuelta 2026-10-22**, ver sección 11.
6. ~~**Formulario abandonado y otros faltantes.**~~ **Resuelta 2026-10-22**, ver
   secciones 2 y 5.

### Trabajo técnico pendiente, no nueva ronda de preguntas de negocio

- Verificar fórmulas detalladas en bundle y escribir pruebas de valores independientes.
- Revalidar contrato/cobertura Syscom y definir caché, reintentos y frescura de stock.
- NASA E2 implementada; integrar consulta local y snapshot en cálculo E4.
- Implementar lector/geocodificación mock y estados asíncronos del formulario.
- Implementar autenticación de enlace, permisos, idempotencia, PDF y correo.
- Configurar reglas fiscales verificadas, redondeo, plazos de aviso y responsables
  administrativos sin inventar tasas o promesas de servicio.
- Revisar integración real Google junto con lectura real IA, incluida privacidad
  y costos. No habilitar facturación ni envío de datos ahora.

## 16. Referencias y decisiones relacionadas

- [ADR-012 — Motor Syscom](../adrs/012-motor-syscom.md).
- [ADR-013 — Ubicación](../adrs/013-ubicacion-desde-recibo-cfe.md).
- [ADR-014 — Catálogo HSP NASA](../adrs/014-hsp-nasa-power.md).
- [Requisitos](../product-requirements.md), [arquitectura](../architecture.md),
  [ingeniería solar](../solar-engineering.md) y [contrato CFE](../cfe-receipt-schema.md).
- [NASA POWER Climatology](https://power.larc.nasa.gov/docs/services/api/temporal/climatology/).
- Referencias comparativas consultadas: [OpenSolar](https://support.opensolar.com/hc/en-us/articles/16782185462671-How-to-configure-pricing-in-OpenSolar),
  [Aurora](https://aurorasolar.com/pricing/),
  [Shopify](https://help.shopify.com/en/manual/inventory-and-locations/setup/selling-when-out-of-stock),
  [Merchant Center](https://support.google.com/merchants/answer/6324448?hl=es),
  [Salesforce](https://help.salesforce.com/s/articleView?id=sf.queues_overview.htm&language=en_US&type=5),
  [HubSpot](https://knowledge.hubspot.com/workflows/assign-and-rotate-record-owners-using-workflows),
  [Google](https://developers.google.com/maps/documentation/javascript/address-validation/build-validation-logic),
  [Mapbox](https://docs.mapbox.com/mapbox-search-js/guides/autofill/),
  [Entrust](https://documentation.identity.entrust.com/getting-started/onfido-studio-product)
  y [Microblink](https://docs.microblink.com/verify/verdict).

Son referencias de diseño, no reglas CFE, tarifas mexicanas ni requisitos
universales. NASA se validó con respuestas reales en E2; contrato Syscom real
sigue pendiente. No se enviaron datos privados ni correos al investigar/documentar.
