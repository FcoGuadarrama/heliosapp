# Memoria compartida de Helios

Este archivo conserva el contexto de trabajo entre agentes. Leerlo antes de
trabajar y actualizarlo con cada cambio es obligatorio según `AGENTS.md`.
Las entradas son acumulativas; una propuesta no equivale a una implementación.

## 2026-10-22 — Codex — Estandarización del diseño

- **Implementado:** separación del sidebar mediante borde y superficie propia;
  cabecera de altura estable; márgenes y ancho de contenido centralizados;
  estilos compartidos para encabezados, paneles y tablas; reutilización de
  variantes de botones; navegación activa en rutas de detalle y edición.
- **Tema:** se mantuvo Instrument Sans y se definieron superficies neutras con
  acento verde petróleo y valores para modo claro y oscuro.
- **Áreas:** `resources/css/app.css`, componentes de navegación y encabezados,
  layouts autenticado y de ajustes, dashboard, prospectos, proyectos, recepción
  de recibos y usuarios. Estándar en `docs/design/theme.md`.
- **Validación:** compilación, TypeScript, lint de `resources/js` y
  `git diff --check` pasaron. `aislop scan --changes --json`: 100/100, sin
  hallazgos. El chequeo global encontró problemas preexistentes de formato y
  lint en documentación de referencia de Syscom.
- **Limitación:** apariencia pendiente de revisión en navegador. La portada
  pública conserva su composición y autenticación hereda los tokens globales.
- **Contexto:** `composer.json` y `composer.lock` ya tenían cambios ajenos al
  comenzar; no forman parte de esta intervención de diseño.

## 2026-10-22 — Codex — Investigación de colores por empresa

- **Hallazgo:** `CompanySetting` almacena `primary_color` y `secondary_color`;
  el formulario de Empresa permite editarlos y el request valida valores
  hexadecimales de seis dígitos. `HandleInertiaRequests` todavía no comparte
  estos colores con el layout. El tema aplicado sigue siendo global.
- **Propuesta, no implementada:** compartir la identidad de empresa mediante
  Inertia y convertir los colores en variables CSS; primario para acciones,
  enlaces y foco, secundario para acentos, y variantes suaves para selección.
  Conservar superficies neutras y colores semánticos de error.
- **Siguiente paso propuesto:** conectar los colores guardados al tema y añadir
  vista previa en Empresa, campo hexadecimal, contraste legible, previsualización
  clara/oscura y restablecimiento de colores. Conservar la preferencia de modo
  claro/oscuro de cada usuario.
- **Validación:** lectura del modelo, migración, formulario, request y middleware;
  sin cambios de implementación en esta investigación.

## 2026-10-22 — Codex — Creación de memoria compartida obligatoria

- **Implementado:** creación de `MEMORY.md` y regla en `AGENTS.md` que exige
  leerlo antes de trabajar y actualizarlo con todos los cambios, incluso los
  mínimos, y con hallazgos relevantes de investigaciones. Aplica también a
  subagentes y exige comprobar el registro antes de dar por terminada la tarea.
- **Registro inicial:** se incorporaron los resultados y pendientes conocidos
  de esta conversación; no se pretende reconstruir todo el historial anterior.
- **Validación:** revisión de ambos documentos, formato Markdown,
  `git diff --check` y escaneo aislop de los archivos de memoria y reglas.
- **Pendientes:** la regla depende de que cada agente lea y respete `AGENTS.md`;
  no se ha añadido un hook que impida automáticamente omitir el registro.

## 2026-10-22 — Codex — Diagnóstico de activación de intakes

- **Hallazgo confirmado en la configuración local efectiva:** `intake.enabled`
  es falso y tanto la versión como el texto del aviso de privacidad están vacíos.
  `IntakeSettings::available()` exige las tres condiciones para habilitar cargas.
- **Activación:** configurar `HELIOS_INTAKE_ENABLED=true`,
  `HELIOS_PRIVACY_VERSION` y `HELIOS_PRIVACY_TEXT` en el entorno y limpiar la
  caché de configuración si existe. La URL del aviso es opcional; los orígenes
  HTTPS de `HELIOS_EMBED_ORIGINS` sirven para incrustar el formulario externo.
- **Acceso:** el formulario interno está en `/intakes/create`, con permiso
  `receipts.import`, accesible desde Mis prospectos → Recibir CFE. Los roles
  owner, admin, engineer y sales incluyen ese permiso en el seeder.
- **Validación:** lectura de configuración, dominio, rutas, formulario y seeder;
  consulta de indicadores booleanos mediante arranque de Laravel por CLI.
- **Sin implementar:** no se modificó el entorno ni se activó la recepción.
  La carga guarda archivos; la extracción automática sigue pendiente.

## 2026-10-22 — Codex — Lectura de referencia para cotización con IA

- **Investigado:** `docs/syscom-calculator-reverse-engineering.md`, contrastado
  con requisitos, modelo de dominio, backlog e ingeniería solar. La referencia
  documenta fórmulas para interconexión, aislado e híbrido, selección de equipos
  y una implementación PHP reutilizable en `ml-syscom-panel`; no se inspeccionó
  todavía ese código ni se ejecutaron sus 110 pruebas reportadas.
- **Criterio propuesto, no implementado:** IA para extracción y asistencia;
  motor determinista para dimensionamiento, compatibilidad y presupuesto,
  catálogo normalizado con precios reales y cotizaciones con insumos congelados.
  Conservar híbrido solo con inversor híbrido y cantidad de microinversores
  según módulos admitidos por unidad.
- **Discrepancia pendiente:** `docs/solar-engineering.md` declara que no hay
  fórmulas verificadas y exige irradiación por coordenadas; la nueva referencia
  documenta HSP por estado y PR fijos/interpolados. Alinear el alcance de la
  estimación antes de implementar; estas fórmulas no resuelven tarifas,
  compensación, impuestos ni verificaciones eléctricas completas.
- **Validación:** lectura local de documentos y archivos del dominio; sin
  consultas a proveedores ni cambios de implementación. La extracción actual
  sigue siendo mock y el cotizador permanece pendiente. Ninguna skill de la
  sesión aporta valor concreto a esta lectura de documentación interna.

## 2026-10-22 — Codex — Fuente de verdad de cálculos confirmada

- **Decisión del usuario:** el reverse engineering de la calculadora real Syscom
  es la fuente de verdad para los cálculos de Helios. Sustituye la discrepancia
  pendiente registrada en la entrada anterior y requisitos previos incompatibles.
- **Implementado en documentación:** prioridad explícita de la referencia;
  ingeniería solar, ADR-005, arquitectura y backlog alineados para aceptar HSP
  por estado, PR y defaults documentados sin exigir coordenadas, NASA o PVWatts.
  Tarifas, compensación e impuestos condicionan solo sus resultados dependientes.
- **Áreas:** `docs/syscom-calculator-reverse-engineering.md`,
  `docs/solar-engineering.md`, `docs/architecture.md`, `docs/development-backlog.md`.
- **Validación:** revisión de coherencia, `git diff --check` y escaneo aislop
  de los documentos modificados. Sin cambios de código ni cálculos ejecutados.
- **Pendiente:** implementación del cotizador; esta decisión no implica que
  exista ya un motor integrado. No se requiere una skill para esta actualización.

## 2026-10-22 — Codex — NASA POWER como fuente de HSP

- **Orientación del usuario:** contemplar obtener HSP desde la API NASA,
  conservando Syscom como fuente de verdad de las fórmulas.
- **Investigado:** documentación oficial NASA POWER Climatology API; admite
  consultas por coordenadas y respuestas JSON. Se propone radiación
  `ALLSKY_SFC_SW_DWN` con comunidad `RE`, validando unidades antes de convertir
  a HSP. Una consulta puntual no constituye un promedio estatal.
- **Documentado:** propuesta y trazabilidad en `docs/solar-engineering.md`.
  Pendientes integración, elección de período anual/mensual, agregación estatal
  si se requiere y contingencia explícita. Sustituye la consideración anterior
  de NASA como ampliación sin prioridad, sin modificar fórmulas de Syscom.
- **Validación:** consulta de documentación oficial; el navegador de investigación
  no pudo acceder al ejemplo de endpoint, por lo que no hay respuesta real
  validada ni cifras NASA comprobadas. Revisión de diff y escaneo aislop.
  Sin implementación; ninguna skill resulta necesaria para esta investigación.

## 2026-10-22 — Codex — RFC y ADR de cotización, ubicación y HSP

- **Implementado en documentación:** RFC-001 con flujo completo desde recibo
  hasta cotización, contratos derivados, estados, privacidad, errores, entregas
  y criterios de aceptación. ADR-012 formaliza Syscom aceptado; ADR-013 y
  ADR-014 proponen ubicación confirmada desde domicilio de suministro y NASA.
- **Áreas:** `docs/rfcs/`, `docs/adrs/`, enlaces en arquitectura e ingeniería solar.
  Numeración continúa después de ADR-011; JSON CFE v1.0 permanece intacto.
- **Hallazgo incorporado:** el contrato CFE prohíbe enviar domicilios a terceros.
  Geocodificador externo queda pendiente de selección y autorización expresa;
  captura manual en mapa permite resolver ubicación sin enviar ese domicilio.
  NASA recibe únicamente coordenadas y parámetros necesarios.
- **Decisiones pendientes:** proveedor, período y selección anual/mensual, TTL,
  reintentos, precisión y habilitación del modo estatal alternativo. Propuestas
  distinguidas de decisiones aceptadas y de implementación existente.
- **Validación:** enlaces locales, numeración, revisión de coherencia con contrato
  CFE y documentación previa, `git diff --check` y aislop de archivos afectados.
  Sin pruebas de aplicación: solo documentación. Sin llamadas externas nuevas.
  Ninguna skill disponible aporta valor concreto a esta documentación técnica.
- **Limitación:** no se implementaron motor, geocodificación, NASA ni extracción
  real; verificar respuesta NASA real forma parte de los criterios de entrega.

## 2026-10-22 — Codex — RFC: equipos Syscom sin precios y motor nuevo

- **Decisión del usuario:** primera entrega con productos del endpoint Syscom
  accesible sin sesión, sin precios; motor implementado desde cero según reverse
  engineering, sin copiar ni adaptar el PHP del proyecto externo.
- **Documentado:** RFC-001 actualizado con alcance técnico, endpoint y roles,
  validación de contrato/cobertura, falta explícita de precios, selección sin
  criterio monetario, snapshots y criterios de aceptación. Presupuesto e
  impuestos quedan para después; sin precios no significa costo cero.
- **Coherencia:** ADR-012 e ingeniería solar alineados; sustituye instrucciones
  anteriores de portar PHP y exigir precios para la primera entrega.
- **Archivos:** `docs/rfcs/001-cotizacion-ia-ubicacion-hsp.md`,
  `docs/adrs/012-motor-syscom.md`, `docs/solar-engineering.md`.
- **Validación:** revisión de cambios, enlaces locales, `git diff --check` y
  escaneo aislop de documentos afectados. El acceso sin sesión se basa en la
  evidencia del reverse engineering; no se reconsultó el endpoint en esta tarea.
- **Pendiente:** implementación y comprobación del contrato real, cobertura y
  atributos técnicos del endpoint interno. Sin cambios de aplicación; ninguna
  skill disponible resulta necesaria para esta actualización documental.

## 2026-10-22 — Codex — Primera entrega solo interconexión

- **Decisión del usuario:** solo interconexión CFE inicialmente, con opción de
  crecer a aislado e híbrido. RFC-001 actualizado en alcance, flujo y aceptación.
- **Diseño previsto:** separar entradas y reglas por tipo sin implementar ni
  ofrecer los otros sistemas todavía. Se conserva su referencia para el futuro.
- **Validación:** revisión documental, `git diff --check` y aislop del RFC y memoria.
- **Pendientes:** continuar definición de dominio con el usuario; sin código
  implementado. No aplica ninguna skill especializada a esta actualización.

## 2026-10-22 — Codex — Cobertura de consumo según Syscom

- **Decisión del usuario:** interconexión al 100% del consumo diario calculado
  según Syscom, sin porcentaje configurable inicialmente. Histórico de al menos
  330 días utiliza promedio diario; redondeo a módulos completos puede exceder
  potencia requerida y no implica eliminar el importe del recibo.
- **Documentado:** sección de cobertura aceptada en RFC-001; los detalles de
  históricos menores deben seguir el bundle, sin introducir reglas nuevas.
- **Validación:** revisión documental, `git diff --check` y aislop de RFC/memoria.
- **Pendiente:** continuar preguntas de dominio; sin cambios de código. No
  aplica skill especializada a esta actualización.

## 2026-10-22 — Codex — Selección y cambios de equipo en referencia Syscom

- **Investigado:** bundle local `calc_pretty.js`, funciones de recomendación,
  selección por rol, alternativas y recálculo de cantidades. Selección inicial
  automática, cambios de modelo por alternativas; cantidades derivadas, sin
  editor libre en ese selector. Recomendación y cambio manual no constituyen
  validación eléctrica completa: la selección manual de inversor conserva una
  unidad sin repetir todos los filtros de la recomendación automática.
- **Hallazgo:** paneles prefieren al menos 400 W, stock suficiente cuando existe
  y cantidad por redondeo hacia arriba. Sin sesión se mantiene selección y
  alternativas, ocultando precios/carrito. No se cambió el RFC: pendiente que
  el usuario decida la política de cambios para Helios.
- **Validación:** inspección estática de funciones y componentes del bundle;
  sin prueba en navegador ni consultas externas. Escaneo aislop de memoria.
  Ninguna skill especializada aplica a esta lectura.

## 2026-10-22 — Codex — Ingeniero IA central e independencia de proveedores

- **Decisión explícita del usuario:** la tarea principal del Ingeniero de IA es
  analizar recibo, datos geográficos, fórmulas y catálogos para elegir equipos
  adecuados a la instalación. Los clientes pueden no ser compradores de Syscom;
  independencia de proveedor es requisito del dominio desde el principio.
- **Documentado:** RFC-001 incorpora selección contextual del agente con
  herramientas deterministas, datos útiles y evidencia, contratos comunes,
  separación producto/oferta, trazabilidad y criterios de aceptación con segundo
  adaptador de prueba. Syscom sigue como primer adaptador real sin precios.
- **Coherencia:** ADR-012 y requisitos de producto actualizados; la IA tiene
  responsabilidad de selección, no solo extracción/explicación. Motor y
  validadores conservan autoridad sobre cifras y compatibilidad.
- **Pendientes:** definir preferencias de “mejores”, proveedores habilitados y
  política de mezcla; no se eligieron por cuenta del usuario. Sin implementación.
- **Validación:** revisión documental, enlaces, `git diff --check` y aislop de
  archivos afectados. No se consultaron servicios externos. No aplica skill
  especializada a esta actualización de dominio.

## 2026-10-22 — Codex — Solo Syscom en primera entrega

- **Decisión del usuario:** avanzar únicamente con Syscom; no resolver ahora
  proveedores alternativos ni selección por empresa. Inventario/catálogo por
  usuario es idea futura, sin decisión ni compromiso de implementación.
- **Documentado:** RFC-001 ajustado en alcance, flujo y aceptación. Se elimina
  exigencia de segundo adaptador de prueba e infraestructura multiproveedor;
  se mantiene separación del motor y acceso a catálogo para evolución posterior.
  Sustituye esos requisitos de la entrada anterior, no el papel del Ingeniero IA.
- **Validación:** revisión documental, `git diff --check` y aislop de RFC/memoria.
- **Pendientes:** continuar definición de dominio; sin código implementado.
  No aplica ninguna skill especializada.

## 2026-10-22 — Codex — Generación automática desde oficina y formulario embebido

- **Decisión del usuario:** asesor registra/selecciona prospecto y carga recibo;
  obtiene propuesta automáticamente. Dropfiles embebido recibe lead y recibo,
  guarda ambos en CRM y devuelve propuesta al lead mediante el mismo análisis.
  No hay botón ni revisión previa obligatoria para iniciar generación.
- **Documentado:** RFC-001 incorpora ambos recorridos y disparo automático.
  Se mantiene alcance de propuesta sin precios y reglas de seguridad del intake.
- **Pendientes de aclaración:** canal de entrega pública, consulta del estado,
  faltantes y confirmación geográfica compatibles con automatización. No se
  decidió mecanismo público de acceso ni se modificaron permisos.
- **Validación:** revisión documental, `git diff --check` y aislop de RFC/memoria.
  Sin implementación; no aplica skill especializada.

## 2026-10-22 — Codex — Entrega de propuesta mediante correo y enlace privado

- **Decisión aceptada por usuario:** lead recibe correo con enlace privado para
  consultar su propuesta y descargar PDF, sin cuenta. PDF adjunto queda para
  después. El enlace abre una versión concreta y tiene vigencia por definir.
- **Documentado:** RFC-001 incorpora correo obligatorio en embebido, confirmación
  de recepción, procesamiento en segundo plano, entrega versionada, aislamiento
  de acceso, PDF privado y criterios de aceptación de envío y autorización.
- **Pendientes:** duración/renovación del enlace y comunicación de resultados
  condicionados o fallidos. Cambio de obligatoriedad de correo y entrega son
  requisitos por implementar; no se modificaron formularios ni contratos actuales.
- **Validación:** lectura del RFC, `git diff --check` y aislop de RFC/memoria.
  Sin código ni correos enviados; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Dos cargas por legibilidad y revisión humana

- **Aceptado:** carga inicial más una segunda oportunidad; si ambas son ilegibles,
  revisión por asesor y aviso al lead. Fallos técnicos no consumen oportunidades.
- **Documentado:** RFC-001 conserva expediente e historial, explica motivo de
  recaptura y distingue datos ausentes de mala imagen. Autorización/canal para
  nueva carga sigue pendiente de decisión.
- **Investigación:** fuentes oficiales Entrust Workflow Studio y Microblink
  describen reintentos y revisión manual; no establecen dos como límite universal.
- **Validación:** lectura de fuentes públicas, revisión documental, diff check y
  aislop de RFC/memoria. Sin cambios de código; no aplica skill especializada.

## 2026-10-22 — Codex — Recaptura desde enlace privado por correo

- **Aceptado por usuario:** correo con botón “Volver a subir mi recibo” abre
  página privada para segunda carga en el mismo expediente, sin otro lead ni
  repetir registro completo. RFC-001 actualizado.
- **Diseño documentado:** autorización limitada al envío, control del límite
  en servidor e idempotencia. Segunda carga ilegible pasa a revisión humana.
- **Pendientes:** vigencia y renovación del enlace; implementación posterior.
- **Validación:** revisión documental, `git diff --check` y aislop de RFC/memoria.
  Sin código ni mensajes externos; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Confirmación de ubicación ambigua por mapa

- **Aceptado:** si la dirección no ubica con certeza la instalación, correo con
  enlace privado para confirmar punto en mapa y continuar automáticamente.
  RFC-001 distingue esta confirmación de los intentos por legibilidad.
- **Recomendación pendiente:** aceptación automática cuando existan señales
  suficientes de precisión; umbrales y política aún no aprobados. No se eligió
  proveedor ni se autorizó enviar domicilios a terceros.
- **Investigación:** documentación oficial Google describe corregir/confirmar/
  aceptar y Mapbox mapa de confirmación con marcador ajustable.
- **Validación:** revisión documental, diff check y aislop de RFC/memoria.
  Sin implementación; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Semáforo geográfico aceptado

- **Aceptado por usuario:** verde continúa automáticamente; amarillo pide
  confirmación en mapa y rojo corrección o colocación del punto por enlace
  privado. Usar señales del geocodificador, sin porcentajes inventados por IA.
- **Documentado:** RFC-001 y ADR-013/014 alineados con aceptación automática
  o humana; nivel independiente del estado, origen y política auditables.
  Confirmación humana no cambia artificialmente la calidad del proveedor.
- **Pendientes:** proveedor y criterios exactos antes de habilitar modo verde;
  selección de proveedor y permisos de tratamiento no quedan resueltos aquí.
- **Validación:** revisión documental, `git diff --check`, enlaces y aislop de
  archivos afectados. Sin código; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Google condicionado a gratuidad

- **Instrucción:** avanzar con Google si es gratuito; no autoriza consumo pago.
- **Verificado en fuentes oficiales:** Geocoding tiene franquicia mensual de
  10 000 eventos y primer tramo pago de USD 5/1000; exige cuenta de facturación
  habilitada y credenciales. Mapas y Address Validation son SKUs distintos.
- **Documentado:** evaluación en RFC-001; selección pendiente de aclarar aceptación
  de facturación con límites. No se activó integración ni se hicieron consultas
  de domicilios, altas de servicios o cargos.
- **Validación:** lectura de páginas oficiales de precios y facturación,
  `git diff --check` y aislop de RFC/memoria. Sin código; no aplica skill especializada.

## 2026-10-22 — Codex — Simular Google durante etapa de lectura mock

- **Decisión del usuario:** consulta a Google también simulada; servicio real y
  facturación se revisan cuando se integre lectura real de archivos con IA.
  No continuar solicitando esas decisiones como bloqueo de la etapa actual.
- **Documentado:** RFC-001 y ADR-013 incluyen fixtures explícitas verde/amarillo/
  rojo/error técnico, sin credenciales, llamadas ni costos Google. Resultados
  mock no se presentan como ubicación validada ni propuestas reales.
- **Alcance:** decisión de diseño registrada; no se implementó el adaptador.
  No implica simular otros proveedores adicionales por esta instrucción.
- **Validación:** revisión documental, `git diff --check` y aislop de los documentos
  afectados. Pendiente implementar mock; no aplica skill especializada.

## 2026-10-22 — Codex — Catálogo NASA estatal y atención ante fallos Syscom

- **Decisión del usuario:** consultar NASA para catálogo HSP por estado de México
  con actualización periódica, no una consulta por lead. Si Syscom falla y bloquea
  el caso, avisar al lead que se revisa su solicitud y al asesor qué está fallando,
  para atención personal lo antes posible. No se prometió plazo específico.
- **Documentado:** RFC-001, ADR-014 e ingeniería solar alineados. Versionado,
  procedencia, última versión válida y ausencia explícita sin fallback silencioso.
  Ubicación permanece como contexto y permite resolver entidad. Avisos al lead
  sin detalles técnicos; seguimiento interno con expediente y error.
- **Pendientes:** método de agregación estatal, período, frecuencia y caducidad;
  no se inventaron valores ni se consultó API. Supersede consulta puntual anterior.
- **Validación:** revisión documental, enlaces locales, diff check y aislop de
  archivos afectados. Sin implementación; no aplica skill especializada.

## 2026-10-22 — Codex — Bajo pedido solo para asesor, disponibilidad para lead

- **Aclaración del usuario:** la restricción es sobre equipos recomendados:
  asesor en CRM puede ver bajo pedido; lead solo configuración con equipos
  disponibles. Sin configuración disponible, derivar a asesor para revisar.
  No modifica la política previa de avisos por fallo Syscom.
- **Documentado:** RFC-001 diferencia destinatario antes de seleccionar, exige
  cantidad suficiente y no asume stock desconocido disponible. Protege vista/PDF
  públicos frente a alternativas internas y evita publicar listas incompletas.
- **Investigación:** Shopify exige habilitar venta sin stock; Google Merchant
  Center diferencia disponible/bajo pedido y requiere fecha en este último.
  Separación asesor/lead es decisión Helios, no regla universal de esas empresas.
- **Pendiente:** vigencia/reconsulta de disponibilidad antes de entrega.
- **Validación:** fuentes oficiales, revisión documental, diff check y aislop
  de RFC/memoria. Sin código; no aplica skill especializada.

## 2026-10-22 — Codex — Correcciones en formulario y estimación económica pendiente

- **Corrección explícita:** primera carga ilegible y ubicación amarilla/roja se
  resuelven en el mismo formulario, no por correo. Supersede decisiones previas
  de enlaces de recaptura/mapa por correo. Entrega y avisos de revisión siguen
  por correo; recuperación tras abandono aún no definida.
- **Aclaración:** cotización originada por asesor en CRM puede incluir bajo pedido;
  propuesta automática al lead embebido exige stock suficiente.
- **Nuevo requisito a definir:** estimación económica aproximada configurable
  para el Ingeniero IA. No hay fuente/reglas acordadas; se propone parámetros
  económicos de empresa y aritmética determinista, sin inventar precios Syscom.
- **Áreas:** RFC-001 y ADR-013. No se habilita estimación ni cambia código.
- **Validación:** revisión documental, diff check y aislop de archivos afectados.
  Pendiente aclarar fuente de precios con usuario; no aplica skill especializada.

## 2026-10-22 — Codex — Referencias para estimación configurable

- **Orientación del usuario:** modalidades económicas configurables; solicita
  contraste con otras plataformas antes de continuar preguntas de dominio.
- **Investigado:** fuentes oficiales OpenSolar (por watt, fijo, markup) y Aurora
  Solar (por watt, componente, fijo). No son fuentes de precios de México.
- **Propuesto, no aprobado:** perfiles por empresa y versión, modalidad base
  única más adicionales sin duplicidades, importes/rangos proporcionados por
  empresa, alcance/vigencia explícitos. Ingeniero IA aplica perfiles mediante
  motor; ajustes persistentes requieren usuario autorizado, sin importes inventados.
- **Documentado:** sección de diseño por confirmar en RFC-001; sin activar precios
  ni elegir primera modalidad. Corregida referencia residual a correo de mapa
  en ejemplos mock: confirmación ocurre en formulario.
- **Validación:** consulta de fuentes oficiales, diff check y aislop RFC/memoria.
  Sin código; no aplica skill especializada. Pendiente aceptación de propuesta.

## 2026-10-22 — Codex — Tres modalidades y exclusión obligatoria de instalación

- **Aceptado por usuario:** estimación por kWp, panel y paquete. Instalación
  siempre aparte; cotización debe especificarlo y configurador CRM advertir que
  los importes capturados excluyen instalación. Sustituye propuesta previa por
  kWp instalado/adicional de instalación; modalidad por conceptos no seleccionada.
- **Documentado:** RFC-001 y requisitos de producto con leyenda obligatoria en
  CRM, propuesta pública, PDF y resumen económico; campos etiquetados sin
  instalación, snapshots y criterios de aceptación. No instalación cero ni total
  instalado. Bases de cantidades/potencia y condiciones son diseño por validar.
- **Pendiente:** implementar configurador y cálculo; completar reglas de perfiles,
  rangos, inclusión de otros conceptos y selección. Sin precios inventados.
- **Validación:** revisión de coherencia, enlaces locales, diff check y aislop de
  documentos afectados. Sin cambios de aplicación; no aplica skill especializada.

## 2026-10-22 — Codex — Modalidad económica según reglas del caso

- **Aceptado por usuario:** escoger modalidad según el caso y reglas configuradas
  por la empresa, por ejemplo paquete aplicable y kWp como alternativa explícita.
  No se obliga a usar siempre una modalidad fija.
- **Documentado:** RFC-001 incorpora selección por condiciones, trazabilidad,
  prioridad explícita y falta de configuración sin inventar tarifas. Se conserva
  una sola base por cálculo y exclusión obligatoria de instalación.
- **Pendientes:** condiciones/prioridades concretas y tratamiento del lead cuando
  no haya perfil aplicable o exista conflicto sin resolver.
- **Validación:** revisión documental, `git diff --check` y aislop RFC/memoria.
  Sin implementación; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Un único importe fiel a tarifas de venta, sin instalación

- **Decisión del usuario:** un solo importe, sin rango ni aproximación libre;
  debe ser real o muy cercano a la realidad, excluyendo siempre instalación.
- **Documentado:** RFC-001 usa tarifas de venta vigentes de la empresa según
  modalidad y reglas, sin confundir precio de venta con costo Syscom. Sustituye
  propuestas de rangos/estimaciones muy aproximadas. Sin configuración válida
  no inventar precio; precisión depende de tarifas mantenidas por la empresa.
- **Pendientes:** impuestos y entrega pública cuando no se puede obtener importe.
- **Validación:** revisión documental, diff check y aislop de RFC/memoria.
  Sin código; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Subtotal, IVA y total separados

- **Aceptado por usuario:** mostrar subtotal, IVA y total. Se mantiene único
  precio de venta, sin rango y sin instalación.
- **Documentado:** RFC-001 y requisitos incluyen desglose; no implica una tasa
  fiscal predefinida. Snapshot conserva regla, base y monto aplicado.
- **Pendiente:** convención de captura de tarifas con/sin IVA y regla fiscal
  aplicable. No inventar IVA cero ni duplicar impuesto.
- **Validación:** revisión documental, diff check y aislop de archivos afectados.
  Sin código; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Captura sin IVA y aviso de traspaso por fallo técnico

- **Aceptado:** precios de configuración siempre sin IVA y sin instalación;
  sistema aplica IVA configurado y presenta subtotal, IVA y total.
- **Aceptado:** cuando un fallo técnico bloquea y deriva el caso, avisar al lead
  que pasó a un asesor; no restringirlo a fallo Syscom. Detalles técnicos solo
  internamente. No consume oportunidades de carga; conservar expediente.
- **Documentado:** RFC-001 y requisitos de producto, con aviso en formulario si
  abierto y por correo, notificación interna y control de duplicados. Asignación
  de asesor pendiente. Errores recuperados sin derivación no anuncian traspaso.
- **Validación:** revisión documental, diff check y aislop de archivos afectados.
  Sin implementación ni mensajes enviados; no aplica skill especializada.

## 2026-10-22 — Codex — Investigación de asignación para atención humana

- **Consultado:** documentación oficial Salesforce (colas de leads y reglas de
  asignación) y HubSpot (rotación de propietarios mediante workflows).
- **Recomendación pendiente de aprobación:** conservar responsable existente;
  casos nuevos sin responsable pasan a bandeja compartida autorizada con acción
  de tomar caso, responsable único e historial. Evitar casos olvidados mediante
  aviso a administrador; plazo todavía no decidido. Reparto automático posterior.
- **Alcance:** no cambia RFC ni política aceptada; usuario todavía decide la
  asignación. No confundir bandeja autorizada con acceso público o de todo rol.
- **Validación:** lectura de fuentes oficiales, diff check y aislop de memoria.
  Sin código ni cambios de permisos; no aplica skill especializada.

## 2026-10-22 — Codex — Asignación inicial aceptada

- **Aceptado por usuario:** conservar asesor responsable; si no existe, bandeja
  compartida “Pendientes de atención”, acción “Tomar caso” y responsable único.
  Avisar al administrador si nadie lo toma tras un plazo configurable.
- **Documentado:** RFC-001 incorpora flujo, permisos, auditoría y toma atómica.
  Reparto automático fuera de esta entrega. Sustituye carácter de propuesta de
  la investigación anterior; duración y reasignación de inactivos pendientes.
- **Validación:** revisión documental, diff check y aislop RFC/memoria.
  Sin implementación ni cambios de permisos; no aplica skill especializada.

## 2026-10-22 — Codex — Edición por asesor, descuentos y aviso al lead

- **Aceptado:** asesor modifica equipos/cantidades, recalcula y valida, notifica
  al lead y puede aplicar descuentos. RFC-001 incorpora versiones y auditoría;
  conservar propuesta anterior y exclusión de instalación.
- **Pendiente:** tipo/base/límites de descuento, permisos, publicación de cambios
  y comunicación manual de equipos bajo pedido. No asumir descuentos ilimitados
  ni notificación por cada pulsación; flujo concreto por confirmar.
- **Validación:** revisión documental, diff check y aislop de RFC/memoria.
  Sin implementación ni mensajes enviados; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Descuento porcentual o fijo a decisión del asesor

- **Aceptado:** asesor decide descuento por porcentaje o monto fijo. RFC-001
  actualizado sin imponer límites comerciales ni aprobación adicional ajenos a
  la decisión del usuario. Validaciones matemáticas y auditoría se conservan.
- **Pendiente:** base de aplicación, combinación de descuentos y publicación de
  revisiones. No asumir que “ambos” significa acumularlos en la misma cotización.
- **Validación:** revisión documental, diff check y aislop RFC/memoria.
  Sin código; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Publicación explícita de actualización al lead

- **Aceptado:** asesor edita borrador y pulsa “Enviar actualización al lead”;
  publicar nueva versión y notificar entonces, sin correos por cada modificación.
- **Documentado:** RFC-001 incorpora validación al publicar, historial inmutable
  y separación de publicación/envío para reintentos sin duplicados.
- **Validación:** revisión documental, diff check y aislop de RFC/memoria.
  Sin código ni mensajes enviados; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Envío manual con equipos bajo pedido

- **Aceptado:** asesor puede enviar manualmente al lead cotización con equipos
  bajo pedido. Identificar condición, conservar disponibilidad y actor; no
  inventar fecha de entrega. RFC-001 actualizado.
- **Se conserva:** entrega automática al lead exige stock suficiente; el envío
  manual usa publicación explícita y nueva versión, sin exponer borradores.
- **Validación:** revisión documental, diff check y aislop RFC/memoria.
  Sin código ni mensajes externos; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Tres acciones del lead en propuesta

- **Aceptado:** solicitar contacto, pedir cambios y aceptar para continuar con
  asesor. RFC-001 incorpora acciones sobre versión publicada, seguimiento y
  notificación según responsable/bandeja, conservando historial y expediente.
- **Alcance:** aceptación de esa versión para seguimiento; no implementa pagos,
  firma, reserva ni contratación de instalación. Cambios solicitados no editan
  automáticamente cotización publicada. Versiones sustituidas por definir.
- **Validación:** revisión documental, diff check y aislop RFC/memoria.
  Sin código ni mensajes; ninguna skill especializada aplica.

## 2026-10-22 — Codex — Vigencia configurable por empresa

- **Aceptado:** cada empresa configura vigencia de cotización. RFC-001 registra
  emisión/vencimiento por versión y distingue vigencia comercial, enlace y stock.
- **Pendiente:** comportamiento de consulta/aceptación al vencer; usuario solo
  confirmó configurabilidad. Reutilizar configuración existente antes de duplicar.
- **Validación:** revisión documental, diff check y aislop RFC/memoria.
  Sin código; ninguna skill especializada aplica.

## 2026-10-22 — Codex — PDF para junta y relevo completo de cotización IA

### Entregables y estado

- **Solicitud:** usuario pidió cerrar la sesión con PDF para junta y dejar todo
  listo para otro agente por límite de uso. No continuar haciendo preguntas hoy.
- **Creado:** `docs/meetings/2026-10-22-cotizacion-ia.pdf` (8 páginas) y fuente
  editable `docs/meetings/2026-10-22-cotizacion-ia.md`. Consolidación vigente de
  reglas aceptadas, alcance, ejemplos, pendientes y orden sugerido de trabajo.
- **Referencias:** RFC-001 en `docs/rfcs/001-cotizacion-ia-ubicacion-hsp.md`;
  ADR-012 motor, ADR-013 ubicación, ADR-014 NASA en `docs/adrs/`; ingeniería
  solar y requisitos recibieron ajustes durante la definición. RFC enlaza la
  consolidación para evitar interpretar frases antiguas como decisiones nuevas.
- **No implementado en esta sesión:** motor, Ingeniero IA real, Syscom, catálogo
  HSP estatal, mock de geocodificación, presupuestos, propuestas/versiones,
  entrega correo/PDF de producto, bandeja y acciones del lead. El PDF de junta
  no equivale al generador PDF de producto. Lector de recibos existente es mock.

### Decisiones vigentes que el siguiente agente debe preservar

1. Primera entrega solo interconexión CFE, con opción futura aislado/híbrido.
   Motor desde cero según reverse engineering Syscom, sin copiar/adaptar PHP
   externo. Cubrir 100% consumo diario calculado; históricos >=330 días usan
   promedio diario en interconexión. Ramas restantes se verifican en bundle.
2. Ingeniero IA es el actor central: analiza todos los datos útiles validados
   del recibo y ubicación, compara y selecciona equipos con herramientas.
   Cálculo, cantidades y compatibilidad son deterministas; no inventar cifras.
3. Solo Syscom por ahora. Endpoint interno abierto sin precios según evidencia
   previa, pendiente revalidar contrato/cobertura. Otros proveedores e inventario
   por usuario son ideas futuras, no tareas actuales ni requisito multiproveedor.
4. Oficina: asesor registra/selecciona lead y carga recibo, obtiene cotización
   automática en CRM que SÍ puede incluir bajo pedido. Embebido: lead + recibo
   se guardan en CRM, análisis automático y propuesta con disponibilidad suficiente.
5. Corrección por primera carga ilegible EN EL MISMO FORMULARIO. Dos cargas en
   total; segunda ilegible deriva a asesor. NO correo para recaptura normal.
   Conservar expediente/archivos; fallo técnico no consume oportunidades.
6. Ubicación EN EL MISMO FORMULARIO. Verde continúa; amarillo confirma; rojo
   corrige/coloca punto. Señales geocodificador, no porcentaje inventado por IA.
   Google se SIMULA por ahora con fixtures; costos/integración real se revisan
   junto con lectura real IA. No pedir facturación ahora como bloqueo.
7. NASA alimentará catálogo HSP de 32 entidades con actualización periódica;
   NO consulta por lead. Pendientes agregación, período, frecuencia/caducidad.
   Versionar valor/estado/fuente; no usar capital como promedio sin metodología.
8. Al lead embebido enviar automáticamente solo configuración completa compatible
   con existencia suficiente. Stock desconocido/insuficiente no cumple. Buscar
   alternativas; sin combinación disponible, derivar y avisar. Asesor puede
   enviar MANUALMENTE bajo pedido, identificado, mediante nueva versión.
9. Entrega por correo con enlace privado a versión concreta, consulta sin cuenta
   y PDF descargable. Sin PDF adjunto inicialmente. Enlaces no dan acceso al CRM.
   Propuesta publicada no cambia al modificar datos o tarifas posteriormente.
10. Precios comerciales: por kWp, por panel y por paquete. Elegir según reglas
    configuradas por empresa. Un solo importe fiel a tarifas vigentes, NO rango
    ni aproximación libre. Sin precios compra Syscom no inventar costo proveedor.
    Desarrollo técnico sin precios sigue posible; objetivo incluye venta configurada.
11. Captura SIEMPRE SIN IVA Y SIN INSTALACIÓN. Cotización desglosa subtotal, IVA
    y total. Leyenda obligatoria: “La instalación no está incluida. Se cotiza por
    separado.” En CRM, propuesta/PDF y resumen económico del correo. Configurador
    advierte lo mismo; instalación no es cero ni adicional automático.
12. Asesor puede cambiar equipos/cantidades y descontar por porcentaje o monto
    fijo, él decide. Recalcular/validar; borrador sin correos por cada ajuste.
    Botón “Enviar actualización al lead” publica nueva versión y correo.
13. Fallo técnico bloqueante: avisar al lead que pasó a asesor, formulario si
    abierto y correo. Al asesor error/contexto, sin detalles técnicos al lead.
    No inventar SLA ni pedir otro recibo por fallo de servicio.
14. Asignación: conservar responsable activo; sin responsable, “Pendientes de
    atención”. “Tomar caso” da responsable único; toma atómica. Avisar admin
    tras plazo configurable aún no decidido. Sin reparto rotativo inicialmente.
15. Lead puede solicitar contacto, pedir cambios y aceptar para continuar con
    asesor. No pagos, firma ni reserva de stock. Aceptación ligada a versión.
16. Vigencia configurable por empresa. Ya existe `proposal_validity_days` en
    `CompanySetting` y página de Empresa; no duplicar sin revisar.

### Punto exacto para retomar y pendientes

- **Última pregunta SIN RESPUESTA:** al vencer, mantener visible con aviso
  “Vencida” y sustituir “Aceptar” por “Solicitar actualización”, notificando asesor.
  Es propuesta del agente, NO regla aceptada. Retomar esta pregunta si continúa
  definición; usuario solo aceptó vigencia configurable por empresa.
- Faltan: expiración/renovación de enlaces, versiones sustituidas/aceptadas,
  recuperación tras abandono del formulario, base de descuento y acumulación,
  reglas concretas de perfiles/paquetes y conceptos incluidos además de equipos,
  caso sin tarifa aplicable, impuestos configurados y fuentes, criterio de mejores
  equipos, reconsulta/caducidad de stock, plazos de atención y reasignación.
- Técnico: contrato/cobertura real Syscom, metodología/cifras NASA, señales Google
  simuladas ahora/reales después, consumo con históricos menores a 330 días,
  pruebas independientes y estados asíncronos del formulario.
- **Armonización necesaria:** RFC creció incrementalmente y conserva contradicciones
  históricas (sin precios vs precio configurado; algunos mensajes por correo de
  confirmación; ubicación puntual vs catálogo estatal). La consolidación de junta
  y esta entrada fijan lo vigente. Reescribir coherentemente antes de implementar,
  sin deshacer decisiones ni dar por aprobados los pendientes. ADR-013/014 y
  requisitos pueden requerir ajustes de referencias al cerrar esa armonización.
- Orden sugerido, no aceptado como plan de ejecución: cerrar reglas indispensables,
  armonizar documentos, motor puro, catálogo, recorrido con mocks, perfiles/
  versiones, entrega/seguimiento y luego lectura IA/geocodificación reales.
- **Workspace compartido:** hay cambios previos de diseño y `composer.json`/lock
  ajenos a esta tarea. No resetear ni atribuirlos al cotizador. No push/deploy.
  Leer memoria y reglas siempre; actualizar con cada cambio y atender aislop.

### Validación y límites de este cierre

- PDF generado localmente sin dependencias nuevas ni servicios externos usando
  pypdf disponible; texto de las 8 páginas extraído y validado. Portada y página
  de mayor densidad renderizadas para revisión visual. Fuente Markdown conserva
  contenido editable; el generador temporal no se añade al producto.
- Revisados enlaces locales, `git diff --check` y aislop de documentos del cierre.
  Markdown no es puntuable por aislop en este entorno: sin hallazgos, no 100/100.
- No pruebas de aplicación: solo documentación y PDF. No correos enviados, API
  consultada con datos reales, dependencias instaladas, commit ni despliegue.
- Ninguna skill disponible añade valor concreto a consolidación documental/PDF;
  no se usaron skills de sitios, frontend ni Swift para este trabajo.

## 2026-10-22 — Codex — RFC completamente consolidado; seis preguntas restantes

- **Corrección solicitada por usuario:** todo el trabajo debía quedar en RFC,
  no relegado a PDF/memoria con contradicciones. Reescrito RFC-001 completo como
  especificación vigente, no colección de enmiendas. Sustituye la advertencia de
  “armonización necesaria” de la entrada de relevo anterior: ya quedó resuelta.
- **Contenido integrado:** alcance y canales; Ingeniero IA central; motor desde
  cero Syscom e interconexión al 100%; dos cargas y correcciones en formulario;
  geocodificación mock/semáforo; HSP estatal NASA; Syscom sin precios de compra;
  disponibilidad automática/manual; tarifas kWp/panel/paquete, precio único,
  instalación excluida, subtotal/IVA/total; descuentos, borradores, versiones,
  correo/PDF, tres acciones del lead; vigencia, fallos, bandeja/asignación,
  privacidad, persistencia propuesta, etapa simulada y criterios de aceptación.
- **Seis preguntas de producto al final del RFC:** (1) vencimiento/versiones
  sustituidas, (2) base/acumulación de descuentos, (3) alcance/prioridad de tarifas
  y falta de precio, (4) preferencias entre equipos válidos, (5) expiración y
  renovación de enlace, (6) recuperación de formulario/otros datos faltantes.
  Separadas de investigación e implementación técnica para no alargar definición.
- **Continuidad:** retomar pregunta 1 cuando usuario quiera; no hacer más preguntas
  en esta corrección. Todas las respuestas previas aceptadas siguen preservadas.
- **Coherencia:** ADR-013 ahora refiere cambio de entidad/HSP estatal, no muestra
  solar puntual. PDF y Markdown de junta actualizados: RFC ya consolidado y seis
  preguntas. No cambió la implementación de aplicación ni se autorizó despliegue.
- **Validación:** lectura de acuerdos y contraste de cobertura del RFC, enlaces,
  ausencia de reglas antiguas de recaptura/mapa por correo, diff check y aislop.
  PDF de 8 páginas regenerado y texto verificado. Markdown no puntuable por
  aislop; no se atribuye 100/100. Ninguna skill especializada aplica.

## 2026-10-22 — Codex — Rechazo del verde; tema y PDF en azul

- **Instrucción del usuario:** quitar verde de PDF y tema. La elección de verde
  petróleo anterior fue del agente, no preferencia del usuario. Sustituida por
  azul en app y azul marino/azul en documento de junta.
- **Implementado:** tokens primario, foco, sidebar y gráficas claros/oscuros en
  `resources/css/app.css`; cinco mensajes/iconos de autenticación, perfil y 2FA
  con verde fijo ahora usan `text-primary`. Sin cambios a reglas semáforo del
  dominio. Guía `docs/design/theme.md` documenta paleta y sustituye tema anterior.
- **PDF:** regenerado `docs/meetings/2026-10-22-cotizacion-ia.pdf`, mismas ocho
  páginas y contenido, fondo neutro y cabecera/títulos azules. Vista previa revisada.
- **Validación:** `npm run build`, oxlint de Vue afectados y diff check pasaron.
  Contraste texto/botón claro 4.97:1, oscuro 7.22:1; sidebar 8.22:1 y 9.26:1.
  Verificados ausencia de tokens verdes anteriores y ocho páginas del PDF.
  Escaneo aislop de archivos afectados. No revisión de app en navegador real.
- **Skills:** frontend-design para paleta y ui-ux-pro-max para contraste/tokens.
  Sin dependencias añadidas, commit ni despliegue. Preferencia futura: no volver
  a aplicar verde como color de marca en PDF o tema sin indicación del usuario.

## 2026-10-22 — Claude Code — Pregunta 1 del RFC-001 resuelta (vencidas/sustituidas)

- **Decisión del usuario (aceptada):**
    - Vencida: sigue visible con aviso “Vencida” y PDF; “Aceptar” se sustituye por
      “Solicitar actualización”, que notifica a responsable o bandeja. El asesor
      publica nueva versión; sin renovación automática.
    - Sustituida: al publicar nueva versión **se revoca el enlace anterior** (no
      abre contenido ni PDF). Solo la versión vigente es consultable/aceptable.
      Historial y aceptaciones previas se conservan internamente.
    - Sustituye la propuesta pendiente de la entrada anterior sobre vencimiento.
- **Cambios:** `docs/rfcs/001-cotizacion-ia-ubicacion-hsp.md` (estado, sección 11
  nueva subsección “Versiones vencidas y sustituidas”, sección 15 marca pregunta 1
  resuelta y cuenta cinco pendientes). Acta de junta (md/PDF) no se modificó: es
  registro de la junta.
- **Pendiente:** qué muestra exactamente un enlace revocado se define con la
  pregunta 5 (acceso privado). Siguiente: pregunta 2 (descuentos).
- **Validación:** `git diff --check` del RFC. Sin cambios de código, commit ni
  despliegue. Sin skills aplicables (definición de dominio).

## 2026-10-22 — Claude Code — Pregunta 2 del RFC-001 resuelta (descuentos)

- **Decisión del usuario (aceptada):** descuentos por concepto **y** sobre
  subtotal; un solo tipo por versión (porcentaje o monto fijo) para todos sus
  descuentos. Orden: conceptos → subtotal sin IVA → IVA sobre subtotal descontado.
  Instalación fuera. Límites ya aceptados (no negativos, 0–100 %) se mantienen.
- **Cambios:** RFC-001 sección 10 (base y acumulación), estado y sección 15
  (pregunta 2 resuelta; quedan cuatro: 3, 4, 5, 6).
- **Siguiente:** pregunta 3 (alcance económico y falta de tarifa).
- **Validación:** `git diff --check`. Sin código, commit ni despliegue.

## 2026-10-22 — Claude Code — Pregunta 3 del RFC-001 resuelta (alcance y sin tarifa)

- **Decisión del usuario (aceptada):** cada perfil (kWp/panel/paquete) declara
  qué conceptos cubre; lo no cubierto se muestra “No incluido”, sin sumarse aparte.
  Instalación siempre excluida. Sin perfil aplicable: lead embebido pasa a asesor
  sin importe (mismo aviso que fallo bloqueante, sección 12); asesor ve resultado
  técnico y causa; no se publica cifra. Prioridad entre reglas ya estaba aceptada
  (prioridad explícita), no se reabrió.
- **Cambios:** RFC-001 sección 9 (alcance; sin perfil válido), estado y sección 15
  (quedan 4, 5, 6).
- **Siguiente:** pregunta 4 (preferencia entre equipos válidos).
- **Validación:** `git diff --check`. Sin código, commit ni despliegue.

## 2026-10-22 — Claude Code — Pregunta 4 del RFC-001 resuelta (elección de equipos)

- **Decisión del usuario:** entre configuraciones válidas, **decide el Ingeniero
  de IA** (ya implícito en sección 3: “comparar candidatos… seleccionar
  configuración”). Sin marcas preferidas ni ranking configurable por empresa.
  Restricciones obligatorias intactas; justificación con atributos verificables.
- **Lección:** el agente propuso opciones de configuración por empresa sin haber
  releído la sección 3; el usuario lo corrigió. Leer las secciones relacionadas
  del RFC antes de formular cada pregunta.
- **Cambios:** RFC-001 sección 3, estado y sección 15 (quedan 5 y 6).
- **Siguiente:** pregunta 5 (expiración/renovación del enlace privado).
- **Validación:** `git diff --check`. Sin código, commit ni despliegue.

## 2026-10-22 — Claude Code — Pregunta 5 del RFC-001 resuelta (enlace privado)

- **Decisión del usuario (aceptada):** el enlace de la versión vigente no expira
  por tiempo; vencida sigue visible (decisión 1 confirmada sobre una respuesta
  inicial “igual a la vigencia” que la contradecía; el usuario eligió mantener 1).
  Deja de abrir solo si es sustituida o revocada manualmente por asesor. Enlace
  revocado: página neutra sin datos ni indicio de otras versiones, botón
  “Contactar asesor” (registra solicitud y notifica); sin reenvío automático.
- **Cambios:** RFC-001 sección 11 (se quitó “con expiración” del acceso; nueva
  subsección “Duración y recuperación del enlace”), estado y sección 15.
- **Siguiente:** pregunta 6 (formulario abandonado y datos faltantes del recibo).
- **Validación:** `git diff --check`. Sin código, commit ni despliegue.

## 2026-10-22 — Claude Code — Pregunta 6 resuelta; RFC-001 sin preguntas de producto

- **Decisión del usuario (aceptada):**
    - Abandono: un único correo recordatorio con enlace privado que reabre el mismo
      expediente en el paso pendiente (mismo conteo de cargas); tras plazo
      configurable sin completar → “Pendientes de atención”. Única excepción al
      recorrido sin correos.
    - Dato faltante en recibo legible: si cálculo/validación obligatoria lo requiere,
      derivar a asesor sin cifra; si no, continuar y declararlo como advertencia.
      No pedir datos técnicos al lead.
- **Cambios:** RFC-001 secciones 2 y 5, estado y sección 15 (seis resueltas).
- **Estado de definición:** las seis preguntas de producto quedaron cerradas.
  Plazos configurables (abandono, aviso admin) siguen siendo parámetros sin valor
  decidido. Queda solo el trabajo técnico de la sección 15. Acta de junta (md/PDF)
  conserva el estado de la junta y no se regeneró. **No se empezó implementación:**
  esperar instrucción explícita del usuario. Sin commit ni despliegue.
- **Validación:** `git diff --check` del RFC. Sin skills aplicables.

## 2026-10-22 — Codex — Revisión del cierre realizado por Claude

- **Revisado:** RFC-001 completo y entradas de las seis decisiones en memoria.
  El resumen coincide con la especificación actual; no se modificó el RFC ni
  se inició implementación. Ninguna skill añade valor concreto a esta revisión.
- **Hallazgos:** las seis decisiones están incorporadas y la vigencia comercial
  quedó separada de la duración del enlace. Queda por concretar cómo asignar
  bases monetarias a descuentos por concepto cuando el precio viene de un
  paquete, kWp o panel; las inclusiones no definen esos importes por sí solas.
  También falta definir detección/temporización del abandono y su relación con
  casos que ya tienen asesor, conservando la política de asignación existente.
- **Evaluación:** cierre documental útil; “sin preguntas de producto” debe
  entenderse como cierre de las seis preguntas, sin asumir resueltas todas las
  especificaciones operativas. Estas observaciones son propuestas de precisión,
  no cambios a decisiones aceptadas ni una nueva ronda de preguntas.
- **Validación:** contraste local de secciones 2, 3, 5, 9–12 y 15. No es posible
  atribuir todos los cambios del workspace a Claude ni verificar desde el árbol
  actual la conversación original o ausencia histórica de commits/push/deploy.

## 2026-10-22 — Codex — Estado para formalizar ADR del RFC-001

- **Investigado:** ADR-012, ADR-013 y ADR-014 existentes. El primero ya está
  aceptado; ubicación y HSP tienen decisiones de producto aceptadas y detalles
  técnicos pendientes. No hace falta terminar la implementación para emitir ADR.
- **Hallazgo:** ADR-012 conserva validación de tres sistemas y banco de baterías,
  que debe delimitarse a interconexión inicial conforme al RFC. La elección de
  equipos corresponde al Ingeniero IA dentro de restricciones deterministas.
- **Propuesta no ejecutada:** alinear esos ADR y redactar decisiones de
  orquestación asíncrona/estados, versiones/acceso/entrega y perfiles económicos.
  NASA requiere metodología sustentada; economía requiere base monetaria de
  descuentos por concepto. Estos pendientes no bloquean los otros ADR.
- **Validación:** lectura local de ADR y memoria; sin cambios a implementación
  ni a los ADR. Ninguna skill disponible aporta valor concreto a esta revisión.

## 2026-10-22 — Codex — Consulta sobre polígonos de vivienda en Google

- **Investigado:** documentación oficial de Geocoding y Solar API. Google ofrece
  contornos de edificios mediante SearchDestinations de Geocoding v4; no asumir
  disponibilidad para toda vivienda ni confundir contorno con límite del predio.
  La función experimental anterior de v3 fue sustituida por ese método.
- **Distinción:** Solar API ofrece información de segmentos de techo y capas
  raster; esos datos requieren cobertura y procesamiento, y no equivalen por
  sí solos a un polígono de superficie instalable confirmado.
- **Fuentes:** documentación Google de building-attributes, geocoding-v4-migrate,
  Solar data-layers y coverage. No se probó una vivienda en México ni se activó
  servicio, facturación o integración. No se cambió alcance del RFC/ADR.
- **Validación:** consulta web de fuentes primarias; investigación registrada.
  Ninguna skill especializada aporta valor concreto a esta consulta.

## 2026-10-22 — Codex — Preparación Google y RFC-002

- **Solicitud:** usuario decide comenzar conexión Google para ubicación y
  polígonos y pide pasos en navegador. Cambia la prioridad previa de postergar
  esa preparación; no se habilitan servicios automáticamente.
- **Documentado:** RFC-002 en borrador con mapa, Geocoding v4/SearchDestinations,
  preparación Cloud, credenciales separadas, validación de cobertura y pendientes.
  Solar API queda como ampliación para techo, no requisito del contorno.
- **Pendiente:** usuario realiza configuración Cloud; ninguna credencial creada,
  facturación habilitada por el agente ni integración implementada. Antes de
  usar domicilios reales, alinear contrato CFE y tratamiento de datos; pruebas
  iniciales con referencia pública. No se alteraron RFC-001 ni ADR-013.
- **Validación:** guías oficiales Google de configuración y contornos; diff check
  y aislop documental. Sin skills aplicables a instrucciones de consola Cloud.

## 2026-10-22 — Codex — Alternativas sin tarjeta para geografía

- **Contexto:** usuario reporta que Google no permite agregar tarjeta; solicita
  otra API. No se considera seleccionado ni implementado otro proveedor.
- **Investigado:** Geoapify permite registro y clave gratuita sin tarjeta para
  geocodificación/mapas (límites y atribución del plan aplican). Para contornos,
  Overture Buildings ofrece datos abiertos descargables; requiere integración
  distinta de una API de direcciones y validar cobertura en las zonas objetivo.
  Overpass permite consultar edificios ya cartografiados en OpenStreetMap.
- **Propuesta:** probar Geoapify para ubicación y evaluar Overture/OSM para
  geometrías, conservando Google pendiente. No prometer contorno por domicilio,
  cobertura completa de México ni datos de orientación/inclinación del techo.
- **Validación:** documentación primaria Geoapify, Overture, Overpass y políticas
  OSM. Sin consultas con datos privados ni cambios a RFC-002 o aplicación.
  Ninguna skill añade valor concreto a esta investigación.

## 2026-10-22 — Codex — Inicio propuesto de RFC-001 sin modelo local

- **Consultado:** usuario pregunta cómo empezar sin modelo IA instalado; Google
  y polígonos quedan pospuestos por indicación anterior. No se inicia código.
- **Confirmado:** AppServiceProvider enlaza ReceiptReader a MockReceiptReader;
  este usa fixtures y no interpreta archivos. Contratos de asesor y herramientas
  en arquitectura son diseño objetivo, no implementación comprobada.
- **Propuesta:** comenzar por motor puro de interconexión y pruebas de referencia
  Syscom; después conectar un recorrido de desarrollo con datos sintéticos de
  recibo/HSP/catálogo y asesor simulado, siempre identificado como simulación.
  Preparar contratos para integrar lector y asesor reales posteriormente sin
  convertir una selección fija de pruebas en la política final del Ingeniero IA.
- **Límites:** datos HSP de prueba no habilitan cálculos comerciales; faltan NASA,
  catálogo validado, IA real y demás requisitos antes de cotizar para leads.
  Elegir modelo local requiere conocer hardware; no se recomienda uno todavía.
- **Validación:** lectura de código intake, bindings, arquitectura y ADR-012;
  diff check y aislop documental. Ninguna skill especializada aplica a este plan.

## 2026-10-22 — Codex — Primer motor y dimensionador manual de interconexión

- **Autorizado e implementado:** inicio del motor RFC-001 sin IA. Nuevo dominio
  `app/Domain/Solar` con entradas/resultados tipados, versión de motor, consumo
  actual/histórico, potencia requerida, paneles completos, potencia seleccionada,
  inversor mínimo y cantidad de microinversores según capacidad de módulos.
  Construido desde bundle Syscom, sin leer/copiar PHP del proyecto externo.
- **Reglas verificadas:** histórico de al menos 330 días usa promedio; si supera
  400 días se normaliza a 365, usando duración uniforme por período. El inversor
  mínimo usa potencia requerida anterior al redondeo de paneles. Se rechazan
  consumos negativos/no finitos, divisores inválidos y cantidades fuera de rango.
- **Pantalla:** `resources/js/pages/Engineering/Interconnection.vue`, rutas GET/POST
  `/engineering/interconnection`, controller y request nuevos; enlace Dimensionador
  en AppSidebar. Sesión verificada y permiso existente `receipts.import`, con
  límite de peticiones. Captura manual vacía inicialmente, histórico opcional,
  errores vinculados a campos y foco en errores/resultados. Cambiar datos retira
  resultado anterior. Se conserva el tema azul/Instrument Sans existente.
- **Documentación:** ADR-012 limita primera validación a interconexión y registra
  implementación parcial; RFC-001 actualiza estado sin cambiar reglas aceptadas;
  `docs/solar-engineering.md` incorpora uso, ejemplo y límites.
- **Validación:** 22 unitarias iniciales; 216 pruebas de suite completa pasaron
  antes de añadir validación de histórico como lista. Validación final dirigida:
  42 pruebas de motor, HTTP y arquitectura, 153 aserciones. PHPStan global sin
  errores, TypeScript, build, Pint de PHP afectado, formato Vue con vp, oxlint y
  diff check pasaron. Aislop sin hallazgos, 100 en seis archivos PHP soportados;
  no atribuir cobertura de Vue/Markdown a ese puntaje.
- **Entorno de pruebas:** configuración cacheada intentó usar MySQL; la conexión
  fue bloqueada sin ejecutar migraciones. Se repitieron pruebas con cache de
  configuración excluida por proceso y SQLite en memoria. PHPStan necesitó
  permiso para su puerto local. No se cambiaron entorno ni base de aplicación.
- **Límites:** no revisión visual/interacción en navegador disponible. Pantalla
  interna de dimensionamiento, no cotizador comercial: no guarda, elige modelos,
  valida eléctricamente, consulta stock, aplica precios/IVA/descuentos, entrega
  PDF/correo ni interpreta archivos. HSP manual de prueba no acredita catálogo
  NASA ni habilita cotizaciones reales. Próximo paso propuesto: perfiles de venta
  y presupuesto, seguido de persistencia y catálogo según RFC. No implementados.
- **Skills:** frontend-design y ui-ux-pro-max para formulario y accesibilidad;
  sus propuestas de paleta/landing se descartaron en favor del tema existente.
  Sin dependencias nuevas, commit, push ni despliegue; cambios previos conservados.

## 2026-10-23 — Codex — Continuidad hacia cotizador útil dentro del prospecto

- **Contexto:** usuario cuestionó la utilidad del dimensionador aislado. Se
  aclaró que es una base técnica probada, pero todavía no entrega equipos reales,
  importe ni cotización para cliente. La pantalla manual no equivale al cotizador
  comercial solicitado; evitar presentar ese avance como el producto completo.
- **Siguiente entrega propuesta en conversación:** conectar catálogo real Syscom,
  verificar contrato, atributos y disponibilidad; permitir al asesor seleccionar
  equipos válidos y calcular cantidades dentro del prospecto. Selección manual
  durante etapa sin IA, sin sustituir la responsabilidad final del Ingeniero IA
  definida en RFC-001. Esta entrega aún no se implementó.
- **Orden posterior propuesto:** perfiles de precios propios por panel/kWp/paquete,
  presupuesto con descuentos e IVA; después guardar cotización y generar PDF.
  No inventar precios de compra Syscom, compatibilidad ni tasas fiscales.
- **Estado:** motor y pantalla descritos en la entrada anterior permanecen como
  primera implementación. Google/polígonos siguen pospuestos. No se inició la
  integración de catálogo ni se cambió código en este registro.
- **Validación:** lectura de memoria y contraste con los últimos mensajes;
  diff check y aislop documental. Sin skills aplicables a este registro.

## 2026-10-23 — Codex — Prioridad siguiente del proyecto

- **Revisión:** memoria y estado local contrastados con rutas, motor solar y
  binding del lector de recibos. Existe dimensionador manual; lectura sigue mock.
- **Recomendación:** siguiente entrega centrada en el prospecto: integrar catálogo
  Syscom, comprobar atributos y stock y permitir selección manual de equipos con
  cantidades calculadas mientras no exista IA real. Después perfiles de venta,
  presupuesto y persistencia/versiones/PDF. NASA e IA real siguen pendientes
  para automatización comercial; Google/polígonos continúan pospuestos.
- **Alcance:** orientación de continuidad, sin iniciar implementación ni cambiar
  decisiones aceptadas. No aplica skill especializada a esta revisión interna.
- **Validación:** inspección local de código y memoria; diff check y escaneo
  documental. No se ejecutaron pruebas de aplicación ni consultas a proveedores.

## 2026-10-23 — Codex — Calculadora de necesidades sin proveedor

- **Solicitud e implementado:** usuario prioriza calcular equipos genéricos
  mientras consigue API Syscom. Dimensionador ahora presenta primero cantidad
  de paneles y watts por panel, potencia DC total y un inversor con capacidad AC
  mínima en kW; microinversores como alternativa, sin sumar ambas topologías.
  No se cambiaron fórmulas ni se redondeó a modelos comerciales inventados.
- **Áreas:** `resources/js/pages/Engineering/Interconnection.vue` y sección 5 de
  `docs/solar-engineering.md`. Conservados ruta, permisos, tema azul, Instrument
  Sans, foco en errores/resultados y retiro de resultados al editar entradas.
- **Alcance:** sin API, catálogo ni cuenta de proveedor. Consumo, días, potencia
  por panel y HSP siguen manuales. No calcula materiales de instalación,
  baterías, precios ni compatibilidad; no persiste. Syscom deja de ser requisito
  para esta calculadora, sin cambiar el objetivo posterior de integración.
- **Validación:** 40 pruebas existentes de motor/HTTP, 149 aserciones; TypeScript,
  lint Vue, formato y build correctos. Aislop dirigido sin hallazgos, sin archivos
  soportados para puntuar Vue/Markdown. Diff check correcto. Sin revisión en
  navegador; build avisa dependencia opcional fontaine ausente (preexistente).
- **Skills:** frontend-design y ui-ux-pro-max; se mantuvo el sistema visual actual
  y se priorizó lista de necesidades sobre detalle técnico. Sin dependencias,
  commit ni despliegue. Pendiente NASA, diseño eléctrico y cotizador comercial.

## 2026-10-23 — Codex — Consulta de acceso inicial

- **Investigado:** cuenta inicial definida en `database/seeders/DatabaseSeeder.php`.
  Se indicó al usuario dónde está definido el acceso de desarrollo, sin guardar
  credenciales ni datos personales en memoria.
- **Validación:** lectura del seeder; no se consultó la base ni se modificaron
  usuarios. La cuenta existente podría tener una contraseña distinta porque el
  seeder usa firstOrCreate. Sin skill especializada aplicable.

## 2026-10-23 — Codex — Corrección de enfoque API y formularios pendientes

- **Corrección explícita del usuario:** el producto en construcción es una API
  consumida por formularios de cliente y asesor; la calculadora de
  `ml-syscom-panel` es referencia del comportamiento. No seguir asumiendo que
  mejorar una pantalla interna resuelve esa necesidad. Definir ambos formularios
  con el usuario antes de implementar más interfaz o cerrar el contrato API.
- **Investigado:** URL local redirige a login. Se revisó el componente Vue de
  referencia, que captura consumo/histórico/estado, consulta JSON al servidor y
  muestra dimensionamiento/materiales. No se inició sesión, probó cálculo ni
  leyó/copió el motor PHP externo. No se modificó el proyecto de referencia.
- **Preguntas pendientes:** cliente solo carga recibo o admite captura de consumo;
  asesor carga/corrige datos dentro del prospecto o usa captura manual. Las
  opciones preguntadas no son decisiones aceptadas. Conservar interconexión
  inicial y decisiones anteriores salvo corrección explícita.
- **Áreas/validación:** investigación de referencia y registro en memoria; sin
  cambios de implementación. Diff check; no pruebas de aplicación. Sin skill
  adicional necesaria para lectura funcional y aclaración de alcance.

## 2026-10-23 — Codex — Dos cargas de recibo, misma API y precios por acceso

- **Aceptado por el usuario:** cliente solo captura contacto y sube recibo; asesor
  crea prospecto y le sube archivo. Ambos consultan la misma API con el mismo
  procesamiento y cálculo. Asesor recibe precios internos y externos; cliente
  solo precio cliente. Sustituye las opciones pendientes de captura manual de
  la entrada anterior; no se acordaron dos calculadoras independientes.
- **Documentado:** sección 2 de RFC-001, contrato funcional común y exclusión de
  precios internos desde servidor en respuestas públicas según autorización.
  Conservadas políticas de stock por canal. No se implementó la API ni formularios.
- **Pendiente de aclaración:** significado y fuente del precio interno; no asumir
  costo de proveedor ni derivarlo con un margen inventado. Precio externo se
  interpreta como precio cliente conforme a la descripción del usuario.
- **Validación:** contraste con RFC y memoria, diff check y escaneo documental;
  sin pruebas de aplicación. Sin skill especializada aplicable a esta definición.

## 2026-10-23 — Codex — Precios confirmados y referencia revisada en navegador

- **Aceptado:** precio interno = costo de compra de equipos; externo = venta al
  cliente. Resuelve significado pendiente anterior. Fuente de costo aún no
  disponible/acordada; no derivar costo desde venta ni tratar ausencia como cero.
- **Verificado en navegador autenticado:** calculadora de referencia presenta
  consumo diario/anual, arreglo solar, inversor y tabla de materiales con rol,
  producto, cantidad, existencia, unitario y subtotal. Etiqueta Catálogo de prueba.
  Al cambiar consumo sintético de 600 a 1200 kWh actualizó de 10 a 20 kWh/día,
  de 6 a 11 paneles y de 2 a 3 microinversores; POST de cálculo respondió 200.
  Se restauró captura a 600. No se publicaron kits ni se exportaron archivos.
- **Documentado:** RFC-001 sección 2, significado de precios y referencia de
  resultado para API compartida. Formularios siguen contacto/recibo y
  prospecto/recibo, no se trasladan los controles manuales de la referencia.
- **Límites:** sin cambios de implementación; no copiar precios/stock ficticios,
  accesorios genéricos como diseño eléctrico validado ni ampliar sistemas.
  No se alteró el proyecto de referencia. Sin skills adicionales necesarias.
- **Validación:** interacción de cálculo en navegador, lectura del resultado,
  diff check y aislop documental; sin pruebas de aplicación Helios en esta tarea.

## 2026-10-23 — Codex — Revisión y preparación de commit del trabajo acumulado

- **Autorizado:** usuario pide revisar todos los cambios y commitearlos antes
  de continuar API. API y consumo del asesor siguen autorizados para después;
  esta tarea no los implementa. Embebido existente consumirá el mismo flujo.
- **Revisado:** diffs de diseño/navegación, motor/request/controller/rutas,
  pruebas, RFC/ADR y documentación acumulada. Composer agrega php-cs-fixer y
  dependencias de desarrollo; no cambia versiones de paquetes ya presentes.
  Conservados todos los cambios existentes y PDF de junta como registro histórico.
- **Corregido:** formato Markdown pendiente; capturas/logs de navegador excluidos
  de Git en `.gitignore`. Vite excluye del lint/formato únicamente el archivo
  externo de referencia bajo `docs/reference/syscom-calculator/`, preservando
  evidencia y números de línea; no se alteraron sus contenidos ni reglas aislop.
  `.gitattributes` identifica PDF como binario para preservar bytes y evitar
  interpretar su tabla de referencias como texto con espacios sobrantes.
- **Validación:** 217 pruebas/1027 aserciones; Pint, PHPStan, TypeScript,
  compilación, check global (101 archivos formateados y 76 sin errores lint),
  Composer validate estricto y plataforma del lock correctos. Diff check correcto;
  aislop 100, sin hallazgos en PHP soportado; formato aislop omitido por herramienta
  no disponible en PATH, cubierto separadamente por Pint.
- **Incidente resuelto:** primera suite coincidió con build y falló al leer CSS
  temporalmente eliminado por este. Suite completa repetida después del build,
  sin fallos. Sin cambios a base/entorno de aplicación; SQLite en memoria.
- **Límites:** sin revisión visual exhaustiva ni auditoría de vulnerabilidades
  remota. Aviso opcional fontaine del build, no bloqueante. Sin API real, lector
  real, NASA ni Syscom; no declarar cotizador comercial completo. Commit local
  solicitado, sin push ni despliegue. Ninguna skill adicional necesaria.

## 2026-10-23 — Codex — Revisión global y plan de entregas por dependencias

- **Solicitud:** usuario pausa implementación aislada de API y pide revisar todo
  el proyecto para completar alcance por partes, con dependencias y bloqueos;
  autoriza alinear nombres RFC/ADR si resulta útil. No se inició código API.
- **Revisado:** requisitos, arquitectura, dominio, backlog, API, RFC-001/002,
  ADR-012/013/014, ingeniería, contrato CFE (reglas/privacidad), memoria y estado
  del código mediante rutas, migraciones, servicios, vistas, configuración y
  pruebas existentes. No equivale a auditoría exhaustiva ni prueba en producción.
- **Creado:** `docs/project-plan.md`, inventario, alcance completo, exclusiones,
  entregas E0–E10, dependencias, criterio de cierre por módulo, diagrama y
  siguiente unidad E1. `docs/README.md` enlaza fuentes y explica precedencias.
- **Orden recomendado, no implementación:** E1 contratos/expediente/estados/
  auditoría/cola/runtime; E2 NASA estatal; E3 lector real; E4 API desde recibo;
  E5 asesor/embebido/atención; E6 precios; E7 equipos/costos; E8 Ingeniero IA;
  E9 versiones/PDF/correo; E10 cierre de frontend/CRM/operación instalable.
  NASA y lector no dependen entre sí; proveedor no bloquea necesidades genéricas.
  Interfaces se entregan con cada capacidad, no todas antes o después del backend.
- **Alineado:** `docs/development-backlog.md`, `docs/product-requirements.md`,
  `docs/architecture.md`, `docs/domain-model.md`, `docs/api-design.md`,
  `docs/solar-engineering.md`, RFC-001/002 y ADR-012/013/014. Fases antiguas se
  identifican como historia; lector/API dejan de esperar F5/F6. Google queda
  pospuesto y requiere vía manual real; defaults HSP no sustituyen NASA válida.
  RFC-001 cambia solo título, conservando ruta/número y decisiones; PDF intacto.
- **Bloqueos registrados:** fuente de watts de panel genérico, metodología NASA,
  entorno/hardware del lector, costo Syscom, bases monetarias para descuentos
  por concepto y plazos configurables. Lock requiere PHP >=8.4.1, manifiesto
  declara ^8.3 y CI usa 8.3; alineación y prueba limpia quedan en E1, sin tocar
  runtime/dependencias/CI en esta tarea documental. No se reabren seis decisiones
  de producto cerradas ni se aprueban tarifas, servicios pagos o acceso a PII.
- **Validación realizada:** check global correcto (103 archivos formateados,
  76 sin errores lint), enlaces locales y diff check correctos; aislop sin
  hallazgos, sin atribuir puntaje PHP a Markdown. Sin nuevas pruebas de aplicación: solo docs;
  217 pruebas es resultado del commit anterior, no ejecutado en esta revisión.
  No aplica skill especializada a planificación técnica interna. Sin commit,
  push, migraciones, cambios de entorno ni implementación de funcionalidades.

## 2026-10-23 — Codex — E1: runtime, contrato y fundamentos del expediente

- **Autorizado e implementado:** usuario inicia paso uno del plan global.
  Composer requiere PHP ^8.4.1 y CI usa 8.4/SQLite aislada; lock actualizado sin
  cambiar versiones. Se conservaron los cambios documentales anteriores.
- **Persistencia:** migración aditiva para `solar_cases`,
  `receipt_processing_runs`, `solar_case_events` y FK nullable en intakes.
  Nuevos envíos CRM/embebido registran expediente, responsable activo si es CRM
  y evento dentro de la transacción existente; rollback limpia archivo.
  Recibos históricos quedan intactos, sin backfill automático. Registro repetido
  del mismo intake no crea otro expediente ni cambia responsable.
- **Procesamiento:** enum de estados independiente del import CFE y ejes
  separados de ubicación/cálculo/publicación/entrega. Contrato ReceiptProcessor
  recibe fuente privada tipada; binding bloquea con receipt_reader_unavailable.
  Fixtures solo por sustitución explícita en pruebas y marca simulated. No se
  despacha automáticamente desde las pantallas ni se generan resultados ficticios.
- **Cola/reintentos:** solicitud idempotente de un run por intake, dispatch tras
  commit con acuse persistido y recuperación si falla envío. Job database con
  timeout 60 s, lock compartido 75 s, tres intentos/backoff 10/30; retry_after
  debe superar 75. Evita solapamiento/efectos terminales repetidos; salidas
  obsoletas no sobreescriben otro intento. Retry explícito autorizado reusa
  archivo/run y audita actor; no gasta otra carga. Fallos guardan códigos, no
  mensajes de excepción. Atención pendiente no equivale a correo enviado.
- **Acceso:** policy exige usuario y prospecto activos/permisos; proyección de
  cliente con campos expresos, sin contacto, archivo, bloqueo técnico ni costo.
  Proyección asesor exige receipts.import y view además de policy. Compra/venta
  ausentes son no disponibles, no cero. No se expusieron endpoints HTTP nuevos;
  la autorización bearer y replay de envío HTTP se implementan con E4/E5.
- **Documentado:** `docs/contracts/solar-processing-v1.md`, ADR-015 y
  `docs/runtime.md`; actualizados índice, API, arquitectura, dominio y plan.
  Contrato fija rutas previstas, entradas, errores, aislamiento público, sesión/
  CSRF internos, idempotencia HTTP por implementar y contratos lector/HSP/cálculo/
  precios para siguientes entregas. No altera JSON CFE v1.0 ni decisiones de RFC.
- **Validación:** suite completa 235 pruebas, 1112 aserciones; 18 nuevas de E1
  (85 aserciones) cubren after-commit/rollback, worker real sobre DB aislada,
  bloqueo, simulación, reintentos/envío fallido, permisos, proyecciones, limpieza
  de archivo al fallar auditoría y roundtrip migración preservando intakes.
  Pint, PHPStan, Composer validate/plataforma, TypeScript, check y build correctos;
  aislop 100 sin hallazgos en PHP soportado, formato aislop omitido por falta de
  php-cs-fixer en PATH y cubierto con Pint. Enlaces/diff revisados al cierre.
- **Instalación aislada:** Composer update --lock requirió red autorizada; sin
  cambios de versiones. Descarga limpia dev en /tmp sufrió DNS en sandbox y
  timeout GitHub aun fuera; se detuvo. Copia de caché de paquetes a /tmp permitió
  instalación limpia --no-dev --no-scripts de 103 paquetes sin red, con arranque
  Laravel y todas las migraciones en SQLite en memoria. No se copió .env/caché
  de aplicación ni se tocó vendor/base de la aplicación. No afirmar instalación
  limpia dev completada ni ejecución de GitHub Actions remota.
- **Límites:** migración preparada y probada, no aplicada a base compartida.
  Despliegue debe migrar antes de usar estas cargas. Quedan NASA E2, lector E3,
  API integrada E4, interfaces E5, precios/equipos/IA/propuestas y operación final.
  Aviso opcional fontaine del build, preexistente. Sin nuevas dependencias,
  frontend, commit, push o despliegue. No aplica skill especializada al backend.

## 2026-10-23 — Codex — Cierre efectivo de E1 antes de NASA

- **Autorización explícita:** usuario exige no dejar pendientes de E1 antes del
  siguiente módulo. Se cerraron migración local y verificación de instalación
  dev, sustituyendo las limitaciones de la entrada E1 anterior.
- **MySQL local:** migrate:status confirmó solo E1 pendiente; revisión del SQL
  con --pretend mostró únicamente tablas nuevas y FK nullable. Migración aplicada
  con --path específico y autorización de herramienta. Estado final: todas Ran,
  E1 batch 7. Sin cambios ni borrado de contactos, recibos o archivos existentes;
  no se ejecutaron seeders ni backfill ni migrate:fresh sobre la base local.
- **Instalación limpia completa:** descarga PHPStan fijado al commit del lock
  completada desde GitHub codeload tras ampliar timeout; ZIP íntegro y raíz de
  commit verificados. Archivo añadido solo a caché temporal con clave de URL
  del lock, sin cambiar fuente/versiones del proyecto. Composer instaló 171
  paquetes en carpeta vacía con caché temporal, incluyendo require-dev, sin
  copiar vendor anterior. Autoload/package discovery, arranque y migraciones
  SQLite en memoria correctos; 235 pruebas, 1112 aserciones también en instalación
  nueva. No se copió .env ni config cacheada; clave de prueba efímera solo en
  proceso, sin guardar secretos. Ninguna descarga quedó ejecutándose.
- **Validación local repetida:** 235 pruebas/1112 aserciones, Pint y PHPStan
  pasaron. Composer install del proyecto confirmó nada por instalar/actualizar.
  Aislop con vendor/bin en PATH: 100 y cero hallazgos, ahora con formato activo.
  Check/formato/enlaces y diff se verificaron al cierre. Build/TypeScript ya
  pasaron en E1, sin cambios frontend nuevos. CI remoto se ejecutará por su
  trigger normal; no se exige publicar cambios para validar este módulo.
- **Documentación:** plan global y backlog marcan E1 cerrada, siguiente E2;
  runtime y requisitos reflejan instalación/migración verificadas. Se conservan
  registros anteriores para trazabilidad. E3–E5 mantienen lector, endpoints HTTP,
  integración de formularios y notificaciones: no son pendientes ocultos de E1.
- **Estado:** fundamento E1 cerrado; se guarda junto al plan documental previo
  en commit local, sin push ni despliegue externo. Sin cambios de funcionalidades
  adicionales ni inicio de NASA. Sin skill especializada aplicable a este cierre.
- **Limpieza de herramientas:** la comprobación de php-cs-fixer generó una
  configuración inicial y entradas ignore automáticamente; se retiraron esos
  artefactos de esta comprobación para conservar Pint como formato del proyecto.
  No se cambiaron reglas aislop. Formato activo se verificó durante el escaneo;
  el cierre usa Pint y aislop con su PATH normal, sin configuración adicional.

## 2026-10-23 — Codex — E2 cerrada: HSP estatal NASA real

- **Autorizado e implementado:** usuario continúa E2. Cliente/parser NASA,
  proveedor HSP local tipado, pesos territoriales, versiones/evidencia,
  publicación atómica, caducidad, actualización y avisos internos. No se inició
  lector E3, API HTTP E4 ni cambios frontend; fórmulas solares intactas.
- **Fuentes reales verificadas:** NASA Climatology Regional RE,
  ALLSKY_SFC_SW_DWN, unidades kW-hr/m^2/day, API v2.10.1/SYN1DEG y período
  2001-01-01 a 2025-12-31. Respuesta solar real es malla 1°, aunque texto
  regional histórico menciona 0.5°. INEGI servicio vectorial: 32 entidades,
  Marco Geoestadístico diciembre 2025. Sin datos de prospectos ni credenciales.
- **Metodología:** inegi-2025-ceres-1deg-area-v1. Geometrías originales (islas y
  huecos), intersección celdas 1° y áreas EPSG:6933; 264 celdas únicas, 448
  intersecciones y 23 teselas NASA de 5°. Conservación de área con error relativo
  máximo 2.97145e-8; pesos por estado suman 1. Promedio ponderado de ANN y 12
  meses, usando ANN NASA directamente; conversión dimensional a h/día conserva
  valor numérico. No promedio de capital, corrección de techo o garantía física.
- **Datos/reproducción:** original INEGI comprimido y metadatos/atribución en
  resources/data/hsp; SHA original 170c3ea5ff472f08ece97970dc90ad4ba8c6f503e33859655f315091c105cc91.
  Generador scripts/hsp con Shapely 2.0.7, pyproj 3.6.1 y numpy 1.26.4 en venv
  temporal Python 3.9; PHP no depende de estas herramientas. Malla regenerada
  y formateada reproduce exactamente SHA del JSON publicado. Ruff/compilación
  Python correctos; caché Python dirigida a /tmp. No se reparan polígonos en silencio.
- **Persistencia:** cinco tablas hsp_catalog_versions/entries/sources/publications
  y hsp_refresh_alerts. Manifiesto completo, metadatos NASA, respuesta original
  y hash por tesela, pesos/valores, publicación/expiración por versión. Lectura
  local por clave INEGI 01–32 con valor/snapshot o ausencia explícita, sin red.
- **Política técnica:** publicación solo de cobertura completa, fuente/período
  uniformes, sin fill/unidades desconocidas/celdas faltantes ni regresión de
  período. Lock de actualización 3600 s, consultas secuenciales y dos intentos.
  Refresh mensual día 1 03:00 UTC y edad inicial 90 días configurables (1–365).
  Fallo conserva versión previa y vencimiento, persiste alerta interna y log;
  catálogo vencido se conserva como evidencia y bloquea datos para cálculos
  nuevos. No hay fallback a 5.0/tabla Syscom, correo al lead ni avisos externos.
- **Operado localmente:** migración aditiva revisada con pretend y aplicada en
  MySQL, batch 8; todas las migraciones Ran. Cache de configuración reconstruida.
  helios:hsp:refresh publicó 32 valores con 23 respuestas reales. Status verificó
  fuente, valores, publicación UTC y caducidad; schedule:list confirmó cron
  0 3 1 * *. Ejecución permanente del scheduler sigue perteneciendo a operación
  E10, no se instaló cron/daemon de sistema; actualizador ya ejecutado manualmente.
- **Áreas:** app/Domain/Hsp, Infrastructure/Hsp, Application/RefreshHspCatalog,
  modelos Hsp*, comandos consola, config/hsp.php, .env.example, provider,
  routes/console.php, migración E2, scripts/datos/fixtures y pruebas. Documentados
  metodología/operación en docs/hsp-catalog.md y datos/README; alineados ADR-014,
  RFC-001, plan, backlog, arquitectura, dominio, ingeniería, API/contrato y runtime.
- **Validación:** suite 269 pruebas/1261 aserciones (34 nuevas E2), PHPStan, Pint,
  TypeScript, check y build pasaron. Nuevas pruebas: ponderación independiente,
  respuesta NASA real grabada, unidades/fill/malla, cobertura, consulta sin red,
  snapshots/versiones, rollback, locks, 429, caducidad exacta, período mezclado/
  regresivo y fallos visibles. Enlaces, formato y diff verificados al cierre.
- **Aislop:** 100; único aviso no fixable de hardcoded-url en el default NASA
  de config/hsp.php. Falso positivo: URL pública canónica en configuración con
  override de operador, no URL específica de despliegue ni secreto. Se conserva
  legible, sin obfuscar ni desactivar reglas. PHP/Python revisados con Pint/Ruff.
- **Estado:** E2 cerrada con catálogo real disponible; siguiente E3 lector real
  y normalización. Sin nuevas dependencias Composer/npm, archivos/personas
  reales de clientes ni cambios de estado de sus expedientes. Commit local al
  cierre, sin push ni despliegue externo. Ninguna skill especializada aplica.

### 2026-10-23 — Codex: preparación de IA local para E3

- El usuario requiere ejecutar la IA en su propia PC, sin acceso a una API de
  IA de pago. Se prepara la integración local para el lector de recibos E3.
- Investigación: documentación oficial de Ollama confirma API local en
  localhost:11434 sin API key y entrada de imágenes en modelos con visión.
  Los PDF requerirán extracción de texto o renderizado previo según su contenido;
  los campos extraídos deben pasar la validación de CFE de Helios.
- Pendiente conocer sistema operativo, RAM, GPU/VRAM y si Helios correrá en el
  mismo equipo para elegir modelo y dar instrucciones concretas de instalación.
  No se ha instalado ni seleccionado un modelo ni implementado el lector.
- Áreas: solo memoria. Validación: consulta de documentación oficial de Ollama;
  sin cambios de código ni pruebas de inferencia. Ninguna skill especializada
  aporta valor a esta preparación.

### 2026-10-23 — Codex: equipo de inferencia separado de Helios

- Equipo confirmado por usuario: Windows, RAM 32 GB y RTX 3060; Helios correrá
  en otro equipo. Falta confirmar VRAM y conectividad entre ambos equipos.
- Documentación oficial de Ollama confirma soporte Windows/NVIDIA RTX 3060.
  Candidato para evaluar lectura: qwen3-vl:8b, modelo de visión de descarga
  aproximada de 6.2 GB; tamaño de descarga no equivale a consumo de VRAM.
- Se indican instalación de Ollama y consulta nvidia-smi en PowerShell. La
  configuración de acceso remoto queda pendiente de saber si comparten LAN o
  Helios estará en un servidor externo; no se abre un puerto público.
- Validación: fuentes oficiales Windows, GPU y ficha del modelo; diff sin
  errores de espacios. Solo memoria, sin instalación ni cambios de aplicación.

### 2026-10-23 — Codex: topología VPS y Ollama en Windows

- Usuario confirma Ollama 0.40.8, RTX 3060 con 12288 MiB y driver 610.88.
  Helios estará en un VPS y la inferencia en su PC Windows.
- Se propone probar qwen3-vl:8b local y conectar VPS/PC por red privada
  Tailscale. Propuesta pendiente de configuración, no conectividad ya operativa.
  Falta conocer sistema operativo del VPS y si Tailscale está instalado.
- Instrucciones locales: pull/run del modelo y ollama ps para verificar uso GPU.
  La calidad y memoria con recibos reales aún requieren evaluación. PC apagada
  o suspendida implica lector inaccesible; la integración deberá manejarlo.
- Validación: documentación oficial Ollama (FAQ/modelo) y Tailscale quickstart;
  sin pruebas en equipos del usuario, instalaciones ni cambios de aplicación.

### 2026-10-23 — Codex: desarrollo local antes de contratar VPS

- El usuario aclara que aún no tiene VPS: probará localmente y contratará el
  servidor cuando el proyecto esté listo. Sustituye la suposición de un VPS
  disponible; su conexión y despliegue quedan para la etapa de operación.
- Ollama sigue en la PC Windows. Por la aclaración anterior de equipos distintos,
  falta confirmar si Helios se probará en esa PC o en el equipo de desarrollo.
  En un único equipo se usa localhost; entre equipos se requiere conectividad
  privada. No se configura Tailscale ni se instala infraestructura en este turno.
- Áreas: memoria. Validación: revisión del contexto y git diff --check; sin
  cambios de aplicación, pruebas de inferencia ni skill especializada aplicable.

### 2026-10-23 — Codex: diagnóstico de imagen en Ollama Windows

- Usuario reporta archivo inaccesible, pero Test-Path devuelve True. Existencia
  no confirma lectura; tampoco está confirmado si el mensaje proviene del modelo.
- Se propone prueba PowerShell con ReadAllBytes y envío base64 al campo images
  de /api/chat para distinguir permisos reales de una ruta enviada solo como texto.
  JPG/PNG para esta prueba; PDF requiere conversión previa. No elevar permisos.
- Validación: documentación oficial Ollama Vision; prueba pendiente en la PC
  del usuario. Solo memoria, sin cambios al lector ni inferencia ejecutada aquí.

### 2026-10-23 — Codex: acceso desde Mac a Ollama Windows por SSH

- Usuario confirma que opera desde MacBook y requiere acceso remoto a la PC.
  Se proporcionan pasos para OpenSSH Server de Windows, conexión desde Mac y
  túnel local 127.0.0.1:11435 hacia localhost:11434 de la PC.
- Se asume misma red para esta prueba y se mantiene Ollama escuchando local;
  no se requiere Tailscale ni publicar el puerto de Ollama. Credencial SSH es
  contraseña de cuenta Windows, no PIN; no se solicita compartirla.
- Validación: instrucciones contrastadas con Microsoft Learn OpenSSH Windows;
  instalación, conexión y túnel pendientes de ejecución en PC del usuario.
  Solo memoria, sin cambios al código ni pruebas remotas realizadas.

### 2026-10-23 — Codex: timeout SSH con servidor escuchando

- Usuario verifica sshd Running y escucha IPv4 0.0.0.0:22, pero Mac agota
  tiempo antes de autenticación. Diagnóstico pendiente de red/firewall/IP;
  no hay evidencia de fallo de credenciales ni del modelo.
- Se indica confirmar IPv4 del adaptador activo, habilitar regla SSH limitada
  a LocalSubnet y repetir conexión. Si persiste, comprobar subredes, red de
  invitados y VPN. No desactivar firewall ni abrir puertos del router.
- Validación: documentación Microsoft OpenSSH y registro del usuario; cambios
  de red aún no ejecutados aquí. Solo memoria; git diff --check sin errores.

### 2026-10-23 — Codex: contraseña oculta en opciones Windows Hello

- Usuario sigue sin autenticar SSH y Windows no muestra opción Contraseña.
  Se explica que whoami no distingue cuenta Microsoft de local y se pide
  verificar Cuentas/Tu información antes de asumir origen de contraseña.
- Microsoft documenta ajuste de solo Windows Hello para cuentas Microsoft que
  oculta contraseña. Se indica comprobarlo bajo Configuración adicional;
  no se restablecen credenciales ni se elimina PIN. Claves SSH siguen siendo
  alternativa si no existe el ajuste o no hay contraseña utilizable.
- Validación: Microsoft Support, Opciones de inicio de sesión; solo memoria,
  sin cambios de cuenta, acceso remoto ni modificación de código.

### 2026-10-23 — Codex: alternativa por clave ante fallo de contraseña

- Windows muestra Contraseña configurada pero Cambiar falla; SSH sigue
  rechazando autenticación. No se atribuye causa sin evidencia ni se resetea
  la cuenta. Se propone clave Ed25519 dedicada desde Mac como alternativa.
- Instrucciones distinguen authorized_keys de usuario estándar y archivo
  administrators_authorized_keys de administradores, con ACL por SID para
  evitar nombres de grupos localizados. Solo se transfiere clave pública.
- Validación: Microsoft Learn OpenSSH key management; aún sin prueba SSH
  exitosa ni cambios de Windows realizados por este agente. Solo memoria.

### 2026-10-23 — Codex: clave SSH rechazada, diagnóstico pendiente

- Usuario reporta que SSH vuelve a pedir contraseña tras instalar clave.
  No se asume causa: comprobar oferta de clave, grupo administrador, nombre
  real del archivo (posible extensión .txt) y huella de clave pública en PC.
- Prueba Mac desactiva fallback a contraseña para ver resultado publickey;
  Windows consulta grupo por SID, archivos y huella con ssh-keygen.
- Validación: Microsoft OpenSSH configuración/gestión de claves; resultados
  pendientes del usuario. Solo memoria; sin nuevas modificaciones de Windows.

### 2026-10-23 — Codex: acceso SSH a PC de inferencia verificado

- Usuario confirma entrada por SSH y autoriza acceso del agente. Prueba remota
  whoami exitosa con autenticación por clave en modo BatchMode, sin contraseña.
  Restricción de red del sandbox requirió ejecución escalada autorizada.
- Consulta remota ollama list exitosa: qwen3-vl:8b (6.2 GB) y qwen3.5:4b
  (3.3 GB) instalados. Acceso operativo para continuar integración E3.
- No se ejecutó inferencia, se leyeron recibos ni se modificó configuración
  remota. Solo memoria; git diff --check verificado. Sin secretos registrados.

### 2026-10-26 — Codex: mem_save, punto de reanudación E3

- E1 y E2 terminadas; E3 (lector real de recibos y normalización) sigue sin
  implementar. Siguiente trabajo: revisar Git y documentos E3, comprobar acceso
  SSH vigente y preparar conexión de Helios a Ollama, conversión de PDF y
  extracción validada. No dar por implementada una API HTTP o túnel persistente.
- Desarrollo actual: Helios en MacBook, inferencia en PC Windows con 32 GB RAM,
  RTX 3060 de 12 GB, Ollama 0.40.8 y driver reportado 610.88. No hay VPS aún;
  se contratará al terminar pruebas locales. Configuración VPS/Tailscale diferida.
- Acceso SSH por clave ya verificado por el agente con autorización del usuario;
  consultar configuración local existente para reconectar sin registrar secretos.
  Windows requería archivo administrators_authorized_keys sin extensión .txt.
- Usuario confirmó extracción desde imagen con qwen3-vl:8b; pasar la ruta de un
  PDF como texto no adjunta su contenido. Helios debe preparar el PDF y enviar
  imágenes/texto al modelo. Cliente y asesor conservarán subida directa de PDF.
- Ollama también servirá para evaluar el Ingeniero de IA de E8, usando herramientas
  deterministas de cálculo/HSP/catálogo; capacidad y precisión del modelo aún no
  validadas para ese rol. Puede requerir modelo local distinto al del lector.
- Modelos vistos por SSH: qwen3-vl:8b y qwen3.5:4b. Falta medir precisión con
  recibos, uso GPU, tiempos y manejo de PC desconectada. No se ejecutó inferencia
  remota por el agente ni se estableció conexión de la aplicación a Ollama.
- Áreas: solo MEMORY.md. Validación: revisión del registro previo y conversación,
  git diff --check; sin pruebas de aplicación ni cambios remotos en este turno.
  Ninguna skill especializada aplica a este guardado de memoria.

### 2026-10-26 — Codex: base E3 implementada y evaluación Ollama local

- **Implementado:** contrato `FileReceiptReader` separado de las fixtures;
  adaptador Ollama local con esquema/prompt CFE versionados, imágenes base64,
  procedencia controlada por Helios, validación de tipos antes de DTOs y
  validadores CFE existentes. Distingue desconexión, timeout, generación
  incompleta, ilegibilidad y dato no impreso; fallos técnicos no gastan cargas.
- **PDF/imagen:** Poppler real (`pdfinfo`/`pdftoppm`), todas las páginas en orden,
  MIME comprobado, máximo 20 MiB/seis páginas/24 MP por imagen y 32 MiB de imágenes
  codificadas. Directorio temporal privado con limpieza en finally. Límite de
  inferencia configurable 1–300 s y lock 900 s. Solo endpoint HTTP loopback
  literal mediante túnel; sin redirecciones ni servicios de IA externos.
- **Persistencia:** `ReadReceiptFile` conserva respuesta original, JSON, modelo,
  versión, páginas, validación y normalización por intake, con unicidad y lock.
  Transacción de persistencia sin mantenerla abierta durante inferencia; repetir
  lectura devuelve la existente. `receipt_readings` y `receipt_reviews` ocultan
  contenido privado al serializarse. No se sobrescriben originales al corregir.
- **Continuidad:** `validated` requiere coherencia/estructura, normalización y
  evidencia de los cuatro campos críticos en páginas existentes. No se impone
  revisión humana universal. `partial` exclusivamente por opcionales no impresos
  puede validar con esos controles; se conserva clasificación original. Otros
  faltantes/advertencias quedan para revisión. No se publica propuesta en E3.
- **Corrección/recaptura:** `ReviewReceiptReading` verifica permisos, usuario
  activo, revisión esperada, procedencia y campos críticos, y guarda versiones
  con autor/motivo. Normalización conserva fechas/días reales e impresos,
  histórico y faltantes sin cambiar fórmulas Syscom. Entidad por coincidencia
  exacta con nombres INEGI, sin inferir coordenadas ni confirmar ubicación.
  `StoreSecondReceipt` prepara segunda carga CRM tras ilegibilidad, conserva
  expediente/prospecto/archivos, replay por hash y límite de dos; un recibo
  sustituido no puede aceptarse. Correo/enlace público/UI quedan en E4/E5.
- **Evaluación real de infraestructura, muestras sintéticas:** SSH y túnel
  temporal comprobados; no se leyeron recibos de clientes. qwen3-vl:8b: legible
  15/15 campos en 94.2 s; parcial 6/6 en 106.2 s; página en blanco produjo
  generación incompleta. Ensayos iniciales agotaron tiempo/tokens por razonamiento;
  uno intermedio completó 13/15 en 160.5 s. GPU verificada: 7.8 GB, 100% GPU.
- **Modelo inicial elegido:** qwen3.5:4b, ya instalado y con visión confirmada:
  legible 14/15 en 34.3 s (única diferencia: status partial por opcionales),
  parcial 6/6 en 25.5 s, blanco 5/5 en 42.8 s. Todos los valores esperados de
  transcripción correctos; expectativas no alteradas para esconder el status.
  Elección configurable por estabilidad/tiempo de estas muestras; no fallback
  automático. Se solicita think:false, sin asumir que el runtime lo respete.
- **Operación local:** migraciones aditivas `2026_10_26_000001` y `000002`
  revisadas con pretend y aplicadas en MySQL, batches 9 y 10; migrate:status
  confirmó todas Ran. Caché reconstruida, lector deshabilitado por defecto con
  modelo 4b configurado. Pruebas reales usaron configuración aislada y activación
  solo de proceso. Túnel de esta sesión cerrado al terminar; no se instaló daemon,
  no se modificó Ollama/SSH remoto ni se descargaron modelos.
- **Áreas:** Application de lectura/revisión/segunda carga, Domain/ReceiptData,
  Infrastructure/Receipts, modelos y migraciones, provider/config/.env.example,
  comandos `helios:receipts:read` y `helios:receipts:probe` con expectativas,
  prompt/esquema/fixtures sintéticos reproducibles y pruebas. Workflow CI añade
  poppler-utils; documentación alineada en lector, plan/backlog, arquitectura,
  contrato, runtime, requisitos y nota introductoria CFE, sin cambiar JSON v1.0.
- **Validación:** 299 pruebas/1352 aserciones en SQLite en memoria mediante
  APP_CONFIG_CACHE alterno; 30 pruebas nuevas cubren lectura/errores, PDF real,
  permisos, snapshots, revisiones, recaptura, replay y rollback de auditoría.
  PHPStan, Pint, Ruff/compilación Python y npm check correctos; aislop 100 sin
  hallazgos, formato/lint PHP/Python comprobados con herramientas aparte.
  Sin cambios frontend ni dependencias Composer/npm; sin build nuevo ni CI remoto.
- **Pendiente para cerrar E3:** evaluación con recibos CFE reales autorizados
  (legibles, parciales, ilegibles y multipágina), precisión/evidencia y tiempos
  representativos. Las muestras sintéticas no acreditan calidad de producción.
  E3 sigue en validación; no se inició E4/E5. `ReceiptProcessor`/job E1 conservan
  binding de bloqueo y timeout 60 s: E4 debe ajustar job/lock/retry-after antes de
  componer lector/HSP/motor. No hay propuesta ni extracción automática desde UI.
  Sin commit, push o despliegue; cambios previos de memoria conservados. No aplica
  skill especializada a este backend.

### 2026-10-26 — Codex: evaluación de cinco recibos reales y lector v2

- **Autorización y muestras:** usuario añadió cinco PDF en `receipts/` para
  probar el lector. Todos tienen dos páginas; cuatro tienen texto utilizable y
  uno una capa de texto corrupta, contrastada visualmente. Originales intactos;
  `/receipts/` añadido a .gitignore para no versionar documentos personales.
  No se crearon prospectos, intakes ni lecturas persistentes de estos clientes.
- **Referencia privada:** cotejo de tarifa, consumo actual y ambas fechas
  (20 campos críticos), fases/días no impresos y 55 filas históricas, comparando
  conjuntamente fechas y kWh sin depender del orden. Fuentes, referencias y
  respuestas completas quedaron solo en /private/tmp/helios-real-receipts-e3,
  directorio 0700 y archivos de datos 0600. No incorporar PII a Git, memoria ni
  fixtures. Informe anónimo en docs/receipt-reader-real-evaluation.md.
- **Hallazgos:** qwen3.5:4b v1 acertó 15/20 críticos y 44/55 filas; confundía
  TARIFA con NO. MEDIDOR contiguo, mezclaba costos informativos y saldos/pagos
  anteriores con cargos actuales y derivó días no impresos en un documento.
  Compartir fecha de corte entre históricos también disparaba falso traslape.
- **Implementado:** `ReceiptText` usa pdftotext con límite de 20 000 caracteres,
  páginas identificadas y fallos técnicos explícitos. Capa vacía/no reconocible
  se declara inutilizable y se conservan imágenes como entrada. Lector v2 añade
  texto utilizable a las imágenes, todo tratado como datos no confiables.
  Prompt v2 pide separar etiquetas, filas kWh, cargos, subtotales y saldos; prohíbe
  inferir fases de NO HILOS y días por fechas. Se conserva prompt v1 y JSON v1.0.
- **Corrección de dominio:** traslape exige intersección de duración positiva,
  coherente con fin-inicio; fechas de corte compartidas ya no son traslape,
  duplicados e intersecciones reales siguen advertidos. No se cambiaron fórmulas
  Syscom, umbrales ni regla de importes negativos para acomodar al modelo.
- **Segunda evaluación 4b v2:** 20/20 críticos, 50/55 filas históricas exactas;
  43.8/42.5/36.1/40.5/45.6 s. Los cinco resultados CFE quedaron needs_review,
  sin errores pero con advertencias. Persisten fechas históricas cambiadas y
  fases inferidas sin estar impresas en tres documentos: NO aprobar precisión
  productiva solo por acertar datos actuales o por superar estructura/coherencia.
- **Comparación 8b v2:** respuestas incompletas en los dos primeros documentos,
  150 s y 148 s. Se interrumpió el resto tras esos fallos; los otros tres no se
  cuentan como evaluados con 8b. Copia temporal de petición interrumpida retirada
  verificando hash contra muestras autorizadas; carpeta probes quedó vacía y
  túnel SSH de la sesión cerrado. Sin cambios remotos ni descargas de modelos.
- **Áreas:** .gitignore, ReceiptText/OllamaReceiptReader, configuración y
  .env.example (HELIOS_PDFTOTEXT), validador de coherencia, pruebas, prompt v2,
  documentación del lector e informe de evaluación. Cache local reconstruida;
  activación real sigue deshabilitada y modelo 4b configurable, sin fallback.
- **Validación:** 302 pruebas/1356 aserciones, PHPStan, Pint y npm check correctos;
  tres regresiones nuevas para fecha compartida/duplicada, texto adjunto y PDF
  vacío. Aislop 100, sin errores/fixables; aviso no fixable preexistente de tamaño
  en ReceiptCoherenceValidator.php (606 líneas), activado al tocar el archivo.
  No se desactivan reglas ni se realiza refactor general ajeno a la corrección.
- **Estado y siguiente trabajo:** E3 sigue abierta; no iniciar E4/E5 ni aceptar
  automáticamente estas extracciones como exactas. Propuestas todavía NO
  implementadas: cotejo de campos/históricos contra fuente, evidencia obligatoria
  para fases y evaluación de modelo/configuración suficientemente precisa.
  La evaluación real sustituye cualquier expectativa de suficiencia basada solo
  en las muestras sintéticas. Sin commit, push, despliegue ni nuevas dependencias
  Composer/npm. No aplica skill especializada a esta tarea de backend.

### 2026-10-26 — Codex: estado de carga confirmado para el usuario

- **Verificado:** configuración local efectiva devuelve intake disponible y
  lector real deshabilitado. La pantalla permite recibir/guardar PDF o imagen;
  todavía no dispara lectura Ollama ni cálculo/propuesta automática.
- **Validación:** arranque Laravel por CLI consultando solo indicadores booleanos
  y revisión de referencias del controlador/rutas; sin cambios de configuración,
  documentos de clientes ni pruebas de inferencia nuevas. Solo memoria.
- **Pendiente:** terminar precisión E3 y conectar el recorrido en E4/E5. No aplica
  skill especializada a esta comprobación de estado.

### 2026-10-26 — Codex: lectura con fuente y carga automática operativas

- **Autorización:** usuario pide continuar con precisión y conexión al formulario.
  Confirma que aún no tiene potencia de panel de referencia: resultado en kWp e
  inversor; cantidad de paneles, potencia seleccionada y microinversores null,
  sin asumir watts ni mostrar cero como cantidad/precio disponible.
- **Implementado — lector v3:** CfeTextFacts decodifica etiquetas contiguas,
  fechas españolas, consumo frente a lecturas acumuladas e histórico completo
  desde texto CFE reconocible. Exige encabezado y cuatro críticos, todas las
  filas candidatas y ausencia de contradicciones; fechas/números inválidos no
  se reparan. No toma NO HILOS como fases ni calcula días como si fueran impresos.
  ReceiptGrounding produce JSON derivado con evidencia/página, avisos explícitos
  SOURCE_RECONCILED y fuente verificada asignada por código. La respuesta Ollama
  íntegra se conserva; no se sustituye ni oculta. Fuente no verificable añade
  SOURCE_UNVERIFIED y queda para revisión. Tipos se comprueban antes de conciliar.
- **Continuidad:** no basta estructura/evidencia declarada por el modelo para
  validar automáticamente: exige fuente cotejada, coherencia y normalización.
  Conciliaciones de campos reconocidos no obligan a revisión por sí solas con
  fuente verificada; las demás advertencias CFE conservan su revisión obligatoria.
  Revisión humana explícita sigue disponible para imágenes o texto corrupto.
- **Verificado con originales, nueva inferencia v3:** cinco PDF, 30/30 campos
  cotejados (20 críticos y fases/días ausentes). Cuatro fuentes verificables,
  44/44 filas históricas correctas; quinto sin fuente verificable y una fila
  discrepante, explícitamente no aceptado automáticamente (global 54/55).
  Tiempos 51.0/42.2/36.2/40.8/45.5 s; los cinco conservan advertencias CFE en
  este lote. WebP en blanco sintético: failed, 5/5 expectativas, ocho segundos.
  Originales intactos y resultados personales solo en carpeta temporal privada;
  ninguna muestra real se incorporó a Git o a fixtures.
- **Carga y cola:** ReceiveReceipt conecta CRM y embebido a QueueReceiptProcessing
  tras guardar. Fallo de dispatch/configuración conserva archivo y expediente,
  registra estado/evento recuperables sin contenido privado de la excepción.
  Cola dedicada receipts, job 840 s, locks 900 s, retry_after 960 s. Rechaza cola
  síncrona o reserva insuficiente; no hace inferencia dentro del request ni de
  una transacción larga. Locks relacionados ordenados primero por expediente.
  composer dev incorpora worker receipts si el lector está habilitado.
- **Proceso real:** ReceiptProcessor usa ReadAndSizeReceipt en lugar del binding
  no disponible; se elimina esa clase obsoleta. Lectura/revisión, confirmación
  estatal del asesor, HSP NASA local vigente y motor compartido generan snapshot
  único por lectura/revisión en solar_calculations. Faltantes y fallos bloquean
  con motivos expresos; no producen resultado ficticio ni consumen recaptura.
- **Histórico anual:** AnnualReceiptHistory conserva todos los originales y
  selecciona los periodos consecutivos más recientes hasta 330–400 días reales,
  sin sumar dos años impresos como uno. Guarda índices/días/criterio; cobertura
  insuficiente usa actual. Mantiene fórmula Syscom de cantidad de periodos por
  duración actual y clamp 365; ambos recuentos quedan en snapshot. Incompatibilidad
  de cobertura, huecos, traslapes o incompletos requieren revisión, sin cambiar
  fórmulas implícitamente. La ausencia de watts solo afecta cantidades de equipos.
- **HTTP/CRM:** GET analysis y POST analyze/review/replacement bajo sesión,
  permisos, CSRF y throttling. Estado real con polling parcial queued/running;
  formulario de cotejo/edición de tarifa, consumo, fechas, estado, fases/días
  impresos, histórico e importes. Confirmación/nota/revisión esperada auditadas;
  tras confirmar reencola y produce resultado técnico. Edición invalida cálculo
  anterior hasta una versión nueva. Segunda carga en mismo expediente y máximo
  dos, sin ofrecer tercera. Se retira el selector de fixtures del flujo nuevo;
  tampoco se permite sustituir un análisis real por simulación vía endpoint.
  Casos simulados anteriores siguen identificados y separados.
- **Permisos/privacidad:** nuevas proyecciones solo muestran datos necesarios
  para cotejo/cálculo, sin respuesta cruda, archivo o contactos del recibo.
  ClientController deja de enviar payload completo de lecturas reales a Inertia.
  Usuario inactivo no puede recibir/ver/procesar recibos; default de User activo
  coincide con la base también en instancias nuevas. Público sigue sin sesión CRM,
  sin enlazar leads por contacto y sin exponer rutas internas por conocer el ID.
- **Operación local:** migración aditiva 000003 revisada/aplicada en MySQL, batch
  11; todas Ran. Catálogo HSP local vigente, verificado sin nueva consulta NASA.
  HELIOS_RECEIPT_READER_ENABLED=true en .env local, resto preservado, cache
  reconstruida, qwen3.5:4b y cola database/retry_after 960 confirmados. Túnel SSH
  y worker receipts iniciados y dejados activos para nuevas cargas del usuario.
  No son servicios permanentes de sistema; Windows/Ollama deben estar conectados.
  No se tocaron SSH/Ollama remotos ni se descargaron modelos.
- **Validación:** 322 pruebas/1442 aserciones, PHPStan, Pint, TypeScript, npm check
  y build correctos. Veinte pruebas nuevas: fuente/conciliación, anualidad/panel
  ausente, carga CRM/embebida, cola fallida sin perder archivo, fuente/ubicación,
  cálculo/snapshots/idempotencia, permisos/inactividad, revisión/continuación y
  bloqueo de simulación en análisis real. phpunit deshabilita lector por defecto
  incluso con .env local activo; pruebas habilitan fakes explícitos sin red real.
- **Navegador:** SQLite y datos sintéticos aislados: login, carga PDF desde
  formulario, queued, error de fechas con foco en resumen y valores conservados,
  confirmación/reencolado y cálculo visible. 375 px y horizontal sin overflow,
  oscuro/texto ampliado/reduced motion, botones 44 px; contraste muestreado 5.58:1
  claro y 6.61:1 oscuro, sin errores JS finales. Skill ui-ux-pro-max aplicada a
  estados/formularios con diseño existente. Se cerró únicamente la pestaña y
  servidor de prueba y se limpiaron dos archivos sintéticos, verificando hashes;
  no se crearon contactos en MySQL compartido ni se borraron archivos de clientes.
- **Áreas:** aplicaciones de recepción/cola/lectura/revisión/cálculo, fuentes y
  DTOs, motor/selección anual, modelos/migración, controllers/resources/policies,
  queue/provider/dev/entorno, rutas/contrato API, componentes/tipos/página prospecto
  y mensaje embebido, pruebas y documentación de análisis automático/lector/plan/
  backlog/runtime/arquitectura. Sin dependencias Composer/npm nuevas ni despliegue,
  commit o push. Aislop 100 con aviso no fixable preexistente de archivo grande
  en ReceiptCoherenceValidator (606 líneas); no se suprimieron reglas. Aviso
  fontaine opcional del build también preexistente.
- **Límites:** recorrido interno básico operativo y embebido con dispatch; no
  afirmar cierre total E4/E5. Pendientes página/enlaces públicos privados,
  ubicación más allá de entidad, bandeja/seguimiento/abandono, equipos, precio,
  Ingeniero IA comercial y PDF/correo/publicación de propuesta. La activación
  local con control de fuente y revisión sustituye el bloqueo anterior de
  integración; no implica precisión universal ni aceptación masiva del lote real.

## 2026-10-26 — Codex: cotizador asistido, PDF/OCR sin generación y API Syscom

- **Decisión del usuario:** construir cotizador asistido conservando extracción
  PDF/OCR a JSON. Indicó acceso a API Syscom, configuró las credenciales localmente
  y señaló su precio público sugerido. Se implementa referencia explícita
  `precio_lista` para borrador; no se confunde con precios de distribuidor/MAP.
  Falta confirmar comercialmente esa semántica por familia. Esto amplía la
  política anterior de tarifas propias, sin implementar aún kWp/panel/paquete.
- **Lectura implementada:** SourceReceiptReader y ReceiptSourceExtractor usan
  pdfinfo/pdftotext para texto digital; OCR Apple Vision local para texto no
  reconocible e imágenes JPEG/PNG/WebP. Binario Swift compilado en almacenamiento
  privado ignorado. Requiere Mac; no se instaló OCR en Windows ni se afirma
  portabilidad Linux. No hay dependencias nuevas Composer/npm ni API OCR pagada.
  Se preservan texto/páginas/cajas/confianza y evidencia; layout agrupa filas y
  selecciona kWh bajo Total periodo, no lectura del medidor. Límites: 20 MiB,
  seis páginas, imágenes 24 MP, timeout, validación de salida JSON y MIME real.
  Rutas de archivo resueltas antes de invocar procesos sin shell.
- **Revisión obligatoria:** toda extracción source queda sin aceptación automática;
  datos desconocidos nulos, sin inferir fases, días impresos, domicilio o dinero.
  Se conecta al formulario existente: confirmar fuente/estado y después calcular.
  La prueba recorre extracción real de PDF sintético, persistencia, revisión y
  cálculo con HSP sintética; paneles siguen pendientes sin potencia asumida.
  ReadReceiptFile registra identificador real del extractor, ya no siempre ollama.
  Lecturas anteriores no se reescriben: para probar el nuevo lector adjuntar un
  archivo nuevo al prospecto; las anteriores siguen corregibles manualmente.
- **Evaluación real:** cuatro PDFs por texto y uno por OCR, cinco muestras usadas
  para desarrollar, no holdout. 20/20 campos críticos (tarifa/inicio/fin/kWh),
  55/55 filas históricas (fechas/kWh); sin fases/días inventados. El OCR sí erró
  una lectura del medidor, por lo que no se afirma exactitud total. Arranque OCR
  observado ~27 s, final caliente ~1.1 s; no SLA. Resultados/referencias privados
  fuera de Git, sin crear expedientes reales. WebP blanco sintético produjo cero
  bloques. Exportación CLI comprobada: receipt-candidates/1, revisión true, 0600,
  creación exclusiva sin sobrescritura ni escrituras CRM.
- **Syscom real:** adaptador aislado de solo lectura vía API oficial OAuth
  client_credentials. Probe y ficha consultados con la cuenta configurada, sin
  imprimir secretos/tokens ni realizar pedidos. MXN, iva=false, inventarios y
  paginación; lote de hasta 20 productos. Token solo en memoria de consulta,
  refresco acotado tras 401, sin redirecciones, errores saneados. Claves de
  configuración documentadas en .env.example; credenciales reales solo en .env.
- **Cotizador implementado:** componente EquipmentQuotePanel dentro del recibo
  con cálculo. Buscar, agregar modelos, cantidades manuales, confirmar y guardar.
  Servidor reconsulta lote y usa precio_lista, ignora importes enviados por
  navegador. Guardados inmutables/versionados por expediente, UUID idempotente,
  vinculación a cálculo/revisión actual y actor; bloqueo case al persistir,
  red fuera de transacción y revalidación de usuario activo/alcance. Revisión
  cambiada o segundo recibo bloquean cálculo anterior. Solo acceso autenticado
  con permisos existentes de recibos, throttle/CSRF; sin rutas públicas.
- **Dinero/alcance:** centavos enteros, precio unitario half-up a dos decimales,
  cantidad y después IVA sobre subtotal half-up una vez; precio fuente conservado.
  HELIOS_QUOTE_VAT_RATE sigue pendiente de respuesta/configuración: IVA y total
  nulos, nunca cero supuesto. Instalación excluida visiblemente. Existencia
  insuficiente/desconocida explícitas; no reserva ni fecha prometida. Compatibilidad
  eléctrica siempre pendiente; no selección automática, PDF, email o publicación.
  Cotizaciones generales del sidebar aún no son un módulo independiente.
- **Persistencia/operación:** migración aditiva 000004 aplicada en MySQL local,
  crea equipment_quote_drafts sin borrar/modificar datos previos. .env local
  selecciona driver source y binario privado; configuración reconstruida. Worker
  receipts reiniciado y dejado activo con timeout/cola anteriores. No es servicio
  permanente. Ollama sigue como adaptador alternativo; no se alteró Windows/SSH.
- **Áreas:** Infrastructure/Receipts (extractor, layout, candidatos, reader),
  scripts/receipts (Swift y fixture PDF escapada/reproducible), comandos extract
  y probe Syscom, Infrastructure/Syscom, Domain/Quotes, CreateEquipmentQuoteDraft,
  modelo/migración/controlador/rutas de borrador, resource/tipos/componentes,
  provider/config/.env.example y documentación de lector, RFC, backlog e índice.
  phpunit fuerza caché de configuración aislada para no cargar caché MySQL local;
  lector deshabilitado y driver Ollama de pruebas, source se habilita explícito.
- **Validación:** 337 pruebas/1505 aserciones, PHPStan, Pint, npm check, types y
  build correctos; Ruff del generador también. 15 pruebas nuevas cubren fuente,
  columnas ambiguas, JSON/revisión/cálculo sin HTTP, permisos, exportación, OAuth,
  precio manipulable, IVA pendiente/explícito, snapshots, idempotencia y revisión
  obsoleta. Navegador SQLite aislado: búsqueda real, agregar/cantidad/guardar v1,
  total pendiente; 375 px, horizontal 812 px y escritorio, claro/oscuro, texto
  20 px/reduced motion, sin overflow, controles 44 px y cero errores JS. Contraste
  secundario muestreado 5.67:1 claro, 6.55:1 oscuro. Skills write-swift y ui-ux-pro-max
  aplicadas. Se cerró servidor/pestaña propios de prueba; solo borrador sintético
  en SQLite, ninguno creado en MySQL compartido. Sin commit/push/despliegue.
- **Aislop:** 100; aviso no fixable preexistente de ReceiptCoherenceValidator
  grande. Dos falsos positivos nuevos documentados: URL canónica Syscom no es
  URL de despliegue y client_credentials es el nombre OAuth del grant, no un ID
  de proveedor incrustado (client_id viene de configuración). Sin suprimir reglas.
  Swift no cubierto por Aislop: compilación/ejecución reales. Fontaine opcional
  del build sigue preexistente. Pendientes: IVA, semántica comercial lista/MAP,
  validación eléctrica, potencia/modelo para cantidades calculadas, pruebas
  independientes de más formatos, OCR portable, propuestas y entrega.

## 2026-10-26 — Codex: IVA del cotizador confirmado en 16%

- El usuario respondió 16 a la tasa solicitada. Se configuró
  HELIOS_QUOTE_VAT_RATE=16 en .env local y se reconstruyó la caché. Sustituye
  únicamente el pendiente de IVA de la entrada anterior: borradores nuevos
  calculan IVA de 16% y total; snapshots anteriores no se recalculan.
- Áreas: configuración local y docs/assisted-quote.md. Sin cambios de fórmula,
  datos comerciales existentes, instalación o política de publicación.
- Validación: configuración efectiva source/lector activo/binario OCR ejecutable
  y tasa 16 comprobadas sin exponer secretos. Prueba existente de IVA explícito
  16.00 verifica redondeo, aplicación única y versiones inmutables; pasó.
  Persisten validación eléctrica, semántica comercial lista/MAP y entrega.

## 2026-10-27 — Codex: documentación del proyecto en sidebar con Laravel Swagger

- **Solicitado e implementado:** Documentation del sidebar abre `/documentation`
  con L5-Swagger 11.2.1, compatible con Laravel 13. Guía en español de módulos,
  uso, arquitectura, PDF/OCR, contrato CFE, dimensionamiento, HSP, Syscom/IVA,
  seguridad, estados, recuperación y operación; referencia de 41 operaciones,
  29 paths y 22 esquemas de prospectos, proyectos, recibos, ingeniería, cotizador,
  embebido y ajustes/usuarios. Distingue JSON, Inertia, redirecciones y funciones
  aún pendientes, sin documentar `/api/v1` como implementada.
- **Decisión pendiente explícita:** el usuario rechazó el cotejo obligatorio
  universal del lector source. Se documenta ese comportamiento actual y su
  sustitución solicitada por revisión condicional como NO implementada. Esta
  tarea no cambia extracción, revisión, ubicación, cálculos o borradores.
- **Acceso/UI:** página, esquema y assets con web/auth/verified. Swagger de
  consulta, sin ejecutar requests de negocio, sin CDN ni validación externa.
  Vista Blade española con viewport, enlace de regreso y descarga JSON;
  filtrado y deep links nativos Swagger. Middleware DocumentationNavigation
  responde con navegación completa a solicitudes Inertia para evitar el modal
  de respuesta no Inertia al volver del login. Sidebar mantiene apertura en
  otra pestaña. No cambia permisos de negocio ni publica recibos o secretos.
- **Fuentes/operación:** atributos en app/OpenApi por módulos y esquemas;
  config/l5-swagger.php escanea solo esa carpeta. Generación CLI JSON/YAML en
  storage/api-docs, ignorados en Git, sin regenerar por request. composer setup
  incluye generación y composer docs:generate permite repetirla. Config local
  reconstruida y esquema generado. Documentación de mantenimiento en
  docs/swagger.md e índice actualizado.
- **Dependencias:** Composer añade L5-Swagger, swagger-ui, swagger-php y
  radebatz/type-info-extras; symfony/yaml existente pasa a dependencia runtime
  sin cambio de versión. No se actualizaron otros paquetes. Composer audit
  detectó aviso PREEXISTENTE en phpunit/phpunit 13.4.0–13.4.3:
  GHSA-9vw2-f4vp-x7gf (temporales compartidos/PHPT coverage). No corresponde a
  Swagger; pendiente actualización de PHPUnit, sin cambios ajenos en esta tarea.
- **Validación:** 339 pruebas/1568 aserciones antes de añadir la regresión de
  navegación Inertia; prueba focal final 2/66 correcta. PHPStan, Pint, TypeScript,
  build y composer validate correctos. Dos pruebas nuevas verifican autenticación,
  verificación, UI/assets, navegación Inertia, generación temporal JSON/YAML,
  seguridad pública/CRM, requisitos y existencia real de cada operación.
  Navegador con SQLite y usuario sintéticos aislados: login, Swagger, grupos,
  revisión con request schema, deep links y 375 px sin overflow. Sidebar compilado
  apunta a /documentation en otra pestaña. Se cerraron solo pestaña y servidor
  propios de prueba. Sin escrituras de clientes/recibos en MySQL.
  Skill ui-ux-pro-max aplicada a navegación; npm check y git diff --check correctos.
- **Límites:** generación requerida en despliegues; no hay despliegue, commit ni
  push. Se preservan cambios anteriores. Aislop final 100, solo tres avisos
  no fixables previos: tamaño de ReceiptCoherenceValidator y dos falsos positivos
  Syscom ya documentados. Build conserva aviso opcional fontaine preexistente.

## 2026-10-27 — Codex: comprobación de Engram

- Investigación solicitada: Engram está instalado en la Mac, ejecutable
  /opt/homebrew/bin/engram, versión 1.20.0 verificada mediante CLI. Su ayuda
  ofrece save/search/context y servidor MCP. En esta sesión no hay herramientas
  Engram ni mem_save expuestas; no aparece referencia Engram en la configuración
  Codex consultada. Instalación local y conexión MCP son estados distintos.
- Sin cambios de integración ni guardado en la base Engram. La documentación
  del proyecto permanece registrada en MEMORY.md. Solo memoria modificada;
  validación mediante command -v, version/help y listado de herramientas.

## 2026-10-27 — Codex: Engram registrado como MCP local

- Configuración implementada con autorización de escritura fuera del proyecto:
  codex mcp add engram -- /opt/homebrew/bin/engram mcp --tools=agent.
  Añade servidor global en ~/.codex/config.toml, preservando los demás ajustes.
  codex mcp get engram confirma enabled=true, transporte stdio y argumentos.
- La sesión actual todavía no expone herramientas Engram. Falta recargar el
  servidor desde la interfaz MCP del cliente y comprobar mem_save; no afirmar
  conexión efectiva ni memoria guardada en Engram. No se fuerza un proyecto
  global para evitar asignar memorias de otros repositorios a Helios.
- Skill OpenAI Docs aplicada: documentación oficial MCP consultada, configuración
  compartida CLI/desktop/extensión y reinicio desde ajustes MCP. Solo configuración
  global y esta memoria modificadas; sin cambios de aplicación ni pruebas nuevas.

## 2026-10-27 — Codex: cliente confirmado como terminal

- Usuario aclara que usa Codex en terminal, no app de Mac. Indicar salida y
  reanudación desde el repositorio para recargar MCP; `codex resume --help`
  confirma selector de sesiones y opción --last. Usar /mcp al reanudar para
  comprobar Engram. Conexión efectiva todavía pendiente; sin cambios de aplicación.

## 2026-10-27 — Codex: conexión Engram comprobada tras recarga

- **Verificado:** herramientas MCP Engram disponibles; mem_context consultado
  con proyecto explícito heliosapp devolvió sesiones y observaciones previas.
  Sustituye el pendiente de conexión de las entradas anteriores.
- **Alcance:** solicitud «setup» ambigua; se pidió distinguir entorno Helios
  de configuración Engram. No se ejecutó composer setup ni se alteró el entorno.
  Composer incluye regeneración de clave y migraciones; hay cambios locales
  previos y dependencias vendor/node_modules presentes.
- **Validación:** lectura de memoria, manifiestos y estado Git, consulta MCP
  efectiva. Solo memoria modificada; sin cambios de aplicación. Ninguna skill
  aporta valor concreto a esta comprobación de conexión y archivos locales.

## 2026-10-27 — Codex: setup de Engram validado con escritura y búsqueda

- **Confirmación:** el usuario aclara que setup se refiere a Engram.
- **Completado:** mem_save creó la observación 216 en heliosapp y mem_search
  la recuperó por título. Lectura y escritura MCP verificadas; sin conflictos
  pendientes en la respuesta. No se requiere otra instalación o recarga.
- **Áreas:** Engram y MEMORY.md; sin cambios de aplicación ni configuración.
  MEMORY.md conserva su obligatoriedad; Engram complementa la memoria local.
- **Validación:** mem_context previo, mem_save y mem_search reales. Skill
  OpenAI Docs aplicada para referencia MCP. Sin importar toda la memoria histórica
  ni copiar secretos; sin pendientes para esta comprobación de setup.

## 2026-10-27 — Codex: definición del proyecto consultada en Engram

- Consulta solicitada por el usuario: mem_search y mem_get_observation recuperan
  la definición arquitectónica #196 de Helios, fechada 2026-10-21: software
  comercial para instaladoras solares en México, instalación por empresa,
  dashboard, recibos CFE, ingeniería determinista, Syscom y presupuestos.
- Sin cambios de implementación. La memoria es histórica: sus fases y restricciones
  técnicas no sustituyen decisiones posteriores registradas en este archivo.
- Validación: lectura completa de la observación MCP; respuesta resume propósito,
  sin afirmar que todos los módulos descritos estén implementados. Sin pendientes.

## 2026-10-27 — Codex: contexto vigente sincronizado a Engram

- **Corrección solicitada e implementada:** actualizar el conocimiento del proyecto,
  no limitar setup a conexión. Guardadas #218 (definición/decisiones vigentes),
  #219 (implementación local y validaciones históricas), #220 (Syscom/IVA/límites
  y pendientes). Fuentes: esta memoria, plan y documentos de cotizador/análisis.
- **Historial:** #196 conserva su texto con aviso y título histórico que remiten
  a las tres nuevas memorias. Se distinguen propuestas, implementación y límites:
  revisión condicional solicitada aún pendiente, source hoy obliga revisión;
  equipos manuales, precio_lista, IVA local 16, sin compatibilidad/PDF/entrega.
- **Validación:** mem_save devolvió IDs sin conflictos; mem_search recuperó
  definición vigente y cotizador. Sin secretos, recibos ni datos personales.
  No se reejecutaron pruebas de aplicación: solo actualización de memorias.
- **Convención:** registrar también en Engram las nuevas decisiones y cierres,
  además de MEMORY.md obligatorio. Sin sincronizador automático instalado.

## 2026-10-27 — Codex: mem_save explícito del cierre Engram

- Usuario solicita mem_save. Guardado en Engram el cierre de actualización con
  referencias #218–220 y aviso histórico #196, bajo setup/engram-context-current.
- Validación: mem_save confirmado por MCP; sin cambios de aplicación ni secretos.
  Se mantiene actualización futura de ambos registros sin sincronizador automático.

## 2026-10-27 — Codex: auditoría del dashboard, propuestas sin implementar

- **Solicitado:** auditar con frontend-design y apple-design; consultar al usuario
  antes de aplicar cambios. También se leyó ui-ux-pro-max para criterios táctiles.
- **Hallazgos:** Dashboard.vue es una bienvenida estática sin métricas, pendientes
  ni actividad; ruta Inertia sin datos operativos. Texto dice que extracción
  Ollama está pendiente pese a lectores source/ollama implementados. Secciones
  max-w-2xl limitan el uso del área disponible en escritorio. Sidebar conserva
  Repository hacia starter Laravel, etiquetas inglesas y módulos deshabilitados.
  Heading genera h2 sin h1 en esta portada; trigger lateral de 28 px y botones
  de 36 px merecen áreas táctiles mayores. Sidebar anima ancho sin alternativa
  explícita de movimiento reducido en ese componente.
- **Propuesta, no implementada:** resumen operativo solar con acción principal,
  pendientes y prospectos recientes; métricas solo con datos reales autorizados;
  limpiar textos/navigation y ajustar jerarquía, tacto y movimiento reducido.
  Conservar Instrument Sans, superficies neutras y acento corporativo.
- **Validación:** lectura de portada, layout, navegación, tokens, botones, rutas
  y binding de lectores; referencia oficial Apple HIG Layout y búsqueda local
  de touch targets. URL heliosapp.test/dashboard redirige a login: sin inspección
  visual autenticada, contraste medido ni pruebas de interacción. No se otorga
  puntuación visual ni se afirma validación móvil. Solo memoria modificada.
- **Pendiente:** usuario elige alcance y autoriza implementación; revisión visual
  autenticada para confirmar espaciado, estados claro/oscuro y responsive.

## 2026-10-27 — Codex navigation_polish: navegación del dashboard operativo

- **Implementado:** navegación principal renombrada a Resumen, Documentación en
  español y retirada de Repository del starter y módulos futuros deshabilitados.
  Se preservan Dimensionador, permisos, proyectos internos y /documentation.
- **Accesibilidad:** trigger de 44 px con aria-expanded y nombre en español;
  filas principales de 44 px, cierre explícito del menú móvil mediante SheetClose
  y cierre al elegir destino principal. El contenido móvil usa flex-1/min-h-0
  para conservar desplazamiento al añadir la cabecera de cierre.
- **Movimiento:** ancho/posición del sidebar y animación del panel móvil respetan
  movimiento reducido; sin cambios globales de botones ni del overlay compartido.
- **Archivos:** AppSidebar.vue, NavMain.vue, ui/sidebar/Sidebar.vue y
  ui/sidebar/SidebarTrigger.vue. Skills frontend-design, apple-design y
  ui-ux-pro-max leídas y aplicadas; búsqueda touch target pertinente.
- **Validación:** vp check --fix focal y vue-tsc --noEmit correctos. Aislop focal
  no produjo hallazgos, pero reportó cero archivos soportados y sin puntuación;
  no representa una auditoría efectiva de estos Vue. Revisión en navegador queda
  para integración con la portada operativa; no se añadieron pruebas espejo.

## 2026-10-27 — Codex (agente dashboard_tests): cobertura del resumen operativo

- **Implementado:** ampliación de `tests/Feature/DashboardTest.php` de dos a diez
  pruebas. Verifica aislamiento de métricas y listas sin permisos, con solo
  recibos, con solo prospectos y con owner inactivo; usuarios viewer obtienen
  acciones de lectura. Comprueba capacidades de captura para owner activo.
- **Datos:** exclusión de prospectos eliminados y expedientes simulados,
  agrupación de estados de revisión/procesamiento, conteo de expedientes con
  borrador vigente sin multiplicar versiones y retorno a pendientes cuando
  existe un cálculo posterior al borrador. Listas acotadas a seis pendientes y
  cinco prospectos; atención precede a selección de equipos y se ordena por
  antigüedad, mientras los prospectos recientes muestran altas más nuevas.
- **Validación:** diez pruebas / 148 aserciones correctas mediante SQLite aislado
  de PHPUnit; Pint, sintaxis PHP y `git diff --check` correctos. Aislop focal
  no reportó hallazgos, pero indicó cero archivos soportados y sin puntuación:
  no equivale a una calificación de calidad del código PHP.
- **Límites:** validación del contrato Inertia y permisos del servidor; la
  apariencia e interacciones corresponden a la revisión de integración del
  agente principal. Sin cambios de producción en esta subtarea; no aplica una
  skill especializada de frontend a pruebas PHP sin cambios de interfaz.

## 2026-10-27 — Codex: identidad visual aportada para retomar auditoría

- **Corrección del usuario:** la auditoría con frontend-design y apple-design
  debe centrarse en diseño; el avance funcional del dashboard no la resuelve.
- **Referencia recibida:** PDF «Diagnóstico Técnico y Áreas de Oportunidad -
  HELIOS» en Downloads únicamente para paleta, e imagen del logo con variantes
  sobre verde oscuro y fondo claro. No se adopta el contenido técnico del PDF.
- **Verificado:** primera página renderizada y colores planos muestreados:
  verde #08221B, dorado #C69C53, fondo #F9F9F8, blanco #FFFFFF,
  superficie cálida #F3F0E8 y texto secundario #354F47. La imagen muestra
  acabados metálicos del símbolo; no se infieren códigos oficiales de esos brillos.
- **Skills:** frontend-design y apple-design leídas para orientar revisión visual.
- **Áreas/validación:** solo MEMORY.md; PDF inspeccionado visualmente y muestreo
  RGB local. Sin cambios de interfaz ni pruebas de aplicación. Pendiente auditoría
  visual y propuestas para elección del usuario antes de implementar.

## 2026-10-27 — Codex: auditoría visual y rediseño Helios autorizado

- **Autorización:** usuario pide ejecutar auditoría y cambiar el diseño con
  apple-design. Sustituye la aprobación pendiente de la auditoría anterior y
  la preferencia histórica por azul, usando su PDF únicamente como paleta.
- **Auditoría/implementado:** identidad azul y Laravel sustituidas por verde
  #08221B, dorado #C69C53, superficies claras/marfil y tema oscuro verde con
  acciones doradas claras. Sidebar de marca, cabecera sticky translúcida,
  jerarquía de títulos h1/h2, espaciado adaptable y paneles sin sombras repetidas.
  Botones, inputs y selects principales de 44 px; respuesta al pulsar, foco y
  movimiento/transparencia reducidos. Banda de métricas verde/dorada. Portada
  del starter sustituida por composición Helios con accesos existentes. Login
  con panel de marca y textos españoles. Formulario embebido alineado en color.
  Footer Documentación corregido para contrastar sobre verde.
- **Marca:** JPG original copiado a public/images/helios-brand.jpg sin modificar;
  SVG muestra una ventana del símbolo, sin inventar trazados. Wordmark Georgia
  es composición de interfaz, no se afirma tipografía oficial. El fondo original
  queda visible; un SVG maestro transparente sigue siendo mejora de asset.
- **Áreas:** resources/css/app.css; AppLogo/Icon, AppSidebarHeader, Heading,
  NavFooter, componentes UI Button/Input/Select/Card, DashboardMetrics,
  AuthSimpleLayout, Welcome, Login, app.ts, embed/cfe y docs/design/theme.md.
  No se alteraron fórmulas, permisos, estados ni lógica de negocio. Se conservaron
  cambios locales anteriores. Skills frontend-design, apple-design y ui-ux-pro-max
  aplicadas; recomendación automática de landing del buscador descartada para
  la composición operativa, conservando identidad explícita del usuario.
- **Validación:** types:check, check:fix, build y diff --check correctos;
  18 pruebas de Dashboard/Authentication/Registration, 165 aserciones. Aislop
  100/100; tres avisos no fixables previos (tamaño ReceiptCoherenceValidator y
  URL/ID Syscom), sin errores ni advertencias fixables. Build conserva aviso
  opcional fontaine. No se instalaron dependencias.
- **Navegador:** instancia aislada; portada y login en 1440/375 px y ambos temas.
  Dashboard, prospectos, dimensionador y perfil comprobados con SQLite/usuario
  sintéticos existentes, 16 combinaciones sin overflow horizontal. Capturas
  inspeccionadas de dashboard escritorio claro/móvil oscuro y dimensionador.
  Se esperaron transiciones de color antes de capturas finales oscuras. Sin
  escrituras de negocio en MySQL. Navegador propio cerrado al finalizar script.
- **Límites:** no se afirma inspección visual individual de cada ruta, Swagger
  conserva interfaz especializada y otras páginas heredan componentes/tokens.
  No hay commit, push ni despliegue. Auditoría y decisiones en docs/design/theme.md.

## 2026-10-27 — Codex: oscuro negro con acentos verdes

- **Preferencia e implementación:** usuario rechaza oscuro verde y plantea negro
  con contraste verde. Fondo #090909, sidebar #101010, paneles #171717,
  bordes neutros y texto claro; verde suave #8AC5A1 para acciones, enlaces,
  foco y métricas. Selección lateral verde discreta. Sustituye solo el tema
  oscuro de la entrada anterior; claro preservado. Marca activa usa token
  sidebar-primary para seguir cada variante. Logo original conserva dorado.
- **Áreas:** resources/css/app.css y docs/design/theme.md; sin lógica de negocio.
  Skills apple-design/frontend-design/ui-ux-pro-max ya leídas aplicadas a contraste.
- **Validación:** npm check/build y git diff --check correctos. Navegador aislado
  portada/login en 1440/375 y claro/oscuro: sin overflow, fondo oscuro RGB 9/9/9;
  captura móvil de login inspeccionada. Aislop conserva 100 y tres avisos previos
  no fixables backend. Aviso opcional fontaine previo. Sin pruebas nuevas por
  cambio exclusivo de tokens; sin revisión autenticada nueva en este ajuste.

## 2026-10-27 — Codex: mem_save explícito de preferencia oscura

- Usuario solicita mem_save. Guardada en Engram la preferencia vigente y el
  cierre implementado: oscuro negro/carbón con acentos verdes, logo dorado y
  modo claro preservado, bajo design/dark-mode.
- Validación: respuesta MCP de mem_save confirmada. Sin cambios de aplicación;
  resultados de comprobaciones conservados en la entrada anterior.

## 2026-10-27 — Codex: revisión de estado ADR/RFC

- **Consulta:** estado de decisiones y especificaciones. Inventario: ADR-001–011
  dentro de architecture.md, ADR-012–015 separados; RFC-001 consolidado y
  RFC-002 Google/contornos pospuesto. Aceptación no equivale a implementación.
- **Hallazgos documentales:** ADR-012 conserva sección histórica sin NASA activa;
  ADR-014 ya registra NASA E2 implementado. ADR-013 conserva etapa mock y no
  refleja completamente revisión/entidad manual actual. RFC-001 sección 14 aún
  presenta lector mock como estado actual pese a source/Ollama implementados.
  project-plan conserva tema azul y pendientes de dashboard ya superados.
- **Estado contrastado:** motor/NASA/fundamento de expediente implementados;
  ubicación manual parcial, geocodificación pospuesta; cotizador asistido con
  selección manual y borradores internos. Pendientes revisión condicional source,
  selección IA, validación eléctrica, publicación/PDF/correo/acceso cliente.
- **Áreas/validación:** lectura ADR/RFC, architecture, project-plan, guías assisted
  y automatic-receipt-analysis, memoria vigente. Solo memoria actualizada;
  no se reejecutaron pruebas ni se reescribieron decisiones. Pendiente reconciliar
  documentos con implementación sin marcar el RFC completo como cerrado.

## 2026-10-27 — Codex: reconciliación ADR/RFC y ADR-016 de diseño

- **Implementado en documentación:** ADR-012 distingue primera entrega histórica
  de cálculo actual con NASA; ADR-013 separa entidad confirmada manualmente de
  coordenadas/mapa/geocodificación pendientes; ADR-015 refleja dispatch/lector
  vigentes y tiempos 840/900/960 s contrastados con job/configuración.
- **RFC-001/plan:** inventario actualizado a lectura real source/Ollama, revisión
  versionada, cálculo/snapshot NASA, cotizador manual Syscom y borradores con
  precio_lista/IVA local16. Retiradas afirmaciones actuales de solo mock, sin
  NASA o sin dispatch. E3–E7 parciales, E8/E9/E10 sin cerrar. Revisión condicional
  source solicitada sigue pendiente; no se declara API pública ni entrega completa.
- **ADR-016 creado:** identidad Helios, tokens, claro verde/dorado y oscuro negro
  neutro con acentos verdes, tipografía, accesibilidad y marca original; alcance
  implementado y límites de validación explícitos. Sustituye azul y oscuro verde
  anteriores. Índice, arquitectura y guía de tema enlazados y reconciliados.
- **Áreas:** docs/adrs/012,013,015,016; RFC-001; project-plan; architecture;
  README y design/theme. RFC-002 conserva posposición y ADR-014 cierre E2.
- **Validación:** lectura de servicios ReceiveReceipt/ReadAndSizeReceipt,
  ProcessReceipt/config queue y guías vigentes; enlaces locales, diff y aislop.
  Sin pruebas de aplicación nuevas por cambio documental ni modificaciones de
  lógica, configuración o decisiones funcionales. Sin skills adicionales: tarea
  documental, basada en decisiones y auditoría de skills ya registradas.

## 2026-10-27 — Codex: inventario Git previo a publicación

- Consulta del usuario sobre pendientes de subir. Rama main, remoto origin
  FcoGuadarrama/heliosapp. Fetch real completado con permiso: main conserva
  tres commits locales por delante y ninguno remoto por incorporar.
- Commits pendientes: b4e7d16 motor/diseño de cotización, c9b222c fundamentos
  de expediente compartido, 36eea1d catálogo NASA HSP versionado.
- Working tree: 66 archivos rastreados modificados, uno eliminado y 113 nuevos
  (conteo expandido por archivo); nada en staging. Incluye lectura/OCR, análisis,
  cotizador Syscom, Swagger, dashboard/diseño, migraciones/pruebas y ADR/RFC.
- Validación: status, diff stat/cached, log, fetch, rev-list y diff --check.
  Sin commit, push o cambios de aplicación. Solo este registro de investigación.
  No se volvió a ejecutar la suite completa para esta consulta.

## 2026-10-27 — Codex: revisión, validación y preparación de publicación Git

- **Autorizado:** revisar/organizar todos los cambios restantes, validar, crear
  commits y subirlos al remoto. Revisión de rutas, autorización de análisis y
  cotizador, snapshots/precios/IVA, límites PDF, configuración/CI y archivos nuevos.
  Sin patrones de claves privadas/tokens conocidos en archivos de texto a incluir;
  .env y recibos reales permanecen ignorados. No es auditoría de seguridad integral.
- **Corregido:** formato en README/plan/ADR-016 y contratos de listas del dashboard:
  array_values sobre resultados mapeados mantiene listas explícitas y elimina
  dos errores PHPStan, sin suprimir reglas ni ampliar tipos.
- **Validación completa:** 347 pruebas/1716 aserciones correctas tras corrección;
  Pint, PHPStan, npm check, TypeScript, build, composer validate --strict,
  generación Swagger y diff --check correctos. Aislop 100, tres avisos backend
  no fixables previos. Build mantiene aviso opcional fontaine. No se cambió .env
  ni se limpió configuración operativa para correr pruebas (PHPUnit usa SQLite).
- **Organización:** 12cb14b recibos/cálculo/cotizador/resumen; 9b43574 Swagger;
  f8da60f identidad visual. Cuarto commit documental incluye guías, ADR/RFC,
  plan y esta memoria. Además se publicarán los tres commits anteriores pendientes
  de motor/fundamentos/NASA. No se reescribe historial ni se usa force push.
- **Publicación:** el push y cotejo de HEAD con origin/main se ejecutan después
  de este registro; esta entrada no afirma éxito anticipado. Si fallan, se
  registrará el impedimento. La publicación del repositorio no es despliegue.
