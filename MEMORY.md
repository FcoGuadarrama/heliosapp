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
