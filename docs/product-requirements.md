# Helios App — Requisitos de producto

> v0.3 · 2026-10-22 · Flujo de prospectos y recibos. Conserva single-tenant de v0.2 y supersede la entrada JSON pública de esa versión.
> Fuente de verdad: documento maestro de producto y arquitectura entregado por el
> propietario el 2026-10-21.

> Revisión 2026-10-23: las decisiones del RFC-001 y el
> [plan de entregas](project-plan.md) actualizan el alcance y orden históricos
> de este documento. El plan distingue implementación real de pendientes.

## 1. Qué es

Software **comercial de ingeniería fotovoltaica asistida por IA** para empresas que
venden e instalan sistemas solares en México. Helios **no vende paneles**: vende el
software con el que la instaladora automatiza diagnóstico, dimensionamiento, selección de
equipos, presupuestación y propuestas.

El diferenciador es un **ingeniero solar virtual autónomo** que diseña una solución
usando **componentes reales obtenidos de distribuidores externos**.

## 2. Modelo de comercialización — SINGLE TENANT

Helios App se **instala en la infraestructura de cada empresa compradora**.

**No hay arquitectura SaaS multiempresa centralizada en el MVP.** Cada comprador tiene:
aplicación Laravel independiente, base MySQL propia, sus usuarios, su configuración
comercial y técnica, sus credenciales de proveedores, su historial y su almacenamiento.

Consecuencias técnicas obligatorias:

- **Prohibido** `company_id` en las tablas de negocio.
- **Prohibido** Global Scopes de multiempresa.
- **Prohibido** paquetes de tenancy (`stancl/tenancy` y similares).
- La configuración de la empresa es un **registro único** (singleton), no una entidad con
  muchas instancias.
- La instalación, configuración, actualización, migración y respaldo deben ser sencillas.
- El licenciamiento comercial se implementa después; no bloquea el MVP.

## 3. El CRM es el producto

Prioridad: **recibo → prospecto → JSON interno Ollama/mock → validación → ingeniero con
motores deterministas y proveedores reales → presupuesto configurable → cotización → seguimiento**.
`Client` representa al prospecto. Un proyecto es un recurso interno opcional: subir un
recibo nunca obliga a crear un proyecto manualmente. La API de negocio compartida
sirve tanto al asesor como al formulario embebido; el CRM es su consumidor interno.
No es un constructor de landings ni un gestor de webs. Una plataforma de API
para consumidores externos con claves y webhooks sigue siendo ampliación posterior.

Navegación prioritaria: **Dashboard / Mis prospectos / Cotizaciones / Proveedores /
Costos y márgenes / Usuarios**. Los módulos aún no implementados se señalan pendientes;
no se construyen pantallas funcionales ficticias para simularlos.

El catálogo histórico de módulos se conserva como contexto:

La interfaz se entrega por recorridos sobre el contrato compartido; no se termina
todo el frontend antes del cálculo ni se difiere toda la API hasta F6. Módulos
históricos (las reglas vigentes de cotización excluyen instalación):

| Módulo                | Contenido                                                                                                                     |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| **Panel principal**   | Resumen de proyectos, cotizaciones, pendientes, diagnósticos, actividad reciente, indicadores del negocio                     |
| **Clientes**          | Registro, contacto, ubicaciones de instalación, historial de proyectos y propuestas                                           |
| **Proyectos solares** | Crear/editar, cliente asociado, ubicación, datos energéticos, diagnósticos, diseños preliminares, equipos, presupuestos       |
| **Ingeniero IA**      | Análisis, diagnóstico energético, sistema recomendado, alternativas, justificación, advertencias, pendientes para instalación |
| **Proveedores**       | Distribuidores, credenciales API, productos, precios, existencias, fichas técnicas, sincronización y caché                    |
| **Presupuestos**      | Costos, porcentajes y márgenes, mano de obra, materiales, servicios, impuestos, cotizaciones, historial de modificaciones     |
| **Configuración**     | Empresa, usuarios y permisos, parámetros comerciales y técnicos, proveedores, plantillas de propuestas, IA                    |

Interfaz moderna, clara y profesional, con identidad visual de Helios y energía solar.
Prioridad: usabilidad, rapidez y visualización clara de resultados técnicos.
**No construir un CRM genérico complejo**: los módulos administrativos sirven al flujo
comercial y técnico de proyectos solares.

## 4. Reglas no negociables

**R1 — La IA no inventa.** Ni cálculos eléctricos, energéticos o financieros, ni precios,
ni productos, ni especificaciones. Los cálculos son servicios deterministas. El agente
razona, compara y explica; el motor de ingeniería valida.

**R2 — Compatibilidad verificada.** El agente no puede seleccionar un producto
incompatible porque parezca conveniente. La viabilidad la decide el validador
determinista.

**R3 — Tres resultados posibles** para todo análisis:

| Estado                         | Significado                                                     |
| ------------------------------ | --------------------------------------------------------------- |
| `preliminary_complete`         | Propuesta generada, con supuestos y verificaciones documentadas |
| `conditioned`                  | Hay datos pendientes que afectan el diseño; se enumeran         |
| `requires_professional_review` | No hay certeza suficiente, o existe un riesgo técnico relevante |

**R4 — Autonomía limitada.** Una propuesta _preliminar_ puede generarse sin intervención.
Un diseño ejecutivo destinado a instalación queda sujeto a verificación física,
eléctrica, normativa y profesional.

**R5 — Reproducibilidad.** Cada cálculo guarda entradas, parámetros, fuente de datos,
supuestos, metodología, versión del motor, resultado y advertencias.

**R6 — Nunca ocultar inconsistencias** para producir artificialmente una propuesta
completa.

**R7 — Dato no disponible ≠ dato cero.** Si una API de proveedor no entrega un campo, el
adaptador lo marca como _no disponible_. Un `200 OK` de un tercero no garantiza que los
datos basten para una decisión técnica.

**R8 — Las credenciales de proveedores nunca llegan al navegador.**

## 5. Entrada de producto: PDF o imagen del recibo

Dos entradas del mismo flujo: formulario público `/embed/cfe`, embebible mediante iframe,
y carga autenticada `/intakes/create`, creando o seleccionando un prospecto explícitamente.
Actualmente se requieren nombre y al menos correo o teléfono para un prospecto nuevo, recibo PDF/JPEG/PNG/WebP
(hasta 10 MiB) y consentimiento con versión vigente. No se pide un proyecto.

El flujo objetivo confirmado exige correo en el embebido, junto al contacto y
recibo; no ofrece captura técnica manual. El asesor crea el prospecto y adjunta
el archivo. Ambos ejecutan el mismo procesamiento mediante la API. La respuesta
autorizada del asesor incluye costo de compra y precio de venta; el cliente
solo recibe precio de venta, excluyendo costos desde servidor. Ese flujo común
y el cambio de obligatoriedad del correo aún están pendientes de implementación.

Los archivos se guardan en almacenamiento privado; la descarga exige permisos del CRM.
La respuesta pública no muestra IDs ni datos de prospectos existentes. Una coincidencia
pública de correo/teléfono **no** da acceso ni vincula registros: se conserva una entrada
separada para revisión humana. El token de un solo uso evita repetir el mismo envío.
No se fusionan prospectos por hash de archivo ni por datos de contacto.
El detalle interno avisa de hasta cinco posibles duplicados activos por correo o teléfono
exactos, visibles solo con autorización sobre esos prospectos. Una coincidencia no
demuestra identidad. El asesor revisa los candidatos y selecciona explícitamente el
prospecto correcto en CRM para nuevas cargas; no hay fusión ni traslado automático de PII.

La recepción conserva el texto, versión, URL y fecha UTC del consentimiento. Deben configurarse
un aviso aprobado por el responsable y los orígenes HTTPS exactos permitidos para iframe.
Por defecto la recepción está deshabilitada y no admite framing. Los textos de demostración
son exclusivamente de prueba y no constituyen un aviso legal aprobado.

El **JSON CFE es un contrato interno** (`docs/cfe-receipt-schema.md`), producido después
por Ollama. E3 añade lectura local real para PDF/imagen, todavía en validación;
ver [estado del lector](receipt-reader.md). La interfaz actual ofrece una simulación
explícita con fixtures identificadas. Un archivo recibido por esa interfaz
queda `stored`, pendiente de extracción. Ejecutar una fixture produce `mock_validated`,
`mock_needs_review` o `mock_rejected`, conservando JSON original y validación CFE.
No hay cálculos ni cotizaciones automáticas derivados de datos ficticios.

La validación interna sigue comprobando estructura, coherencia, campos críticos y evidencia;
JSON válido no garantiza información correcta. Las correcciones posteriores se auditarán.

## 6. Proveedores

El **Ingeniero de IA** es responsable del análisis contextual y la selección de
los equipos para la instalación: considera datos útiles y validados del recibo,
ubicación geográfica y resultados de herramientas de cálculo, catálogo y validación.
La fuente de fórmulas Syscom no obliga a comprar equipos a Syscom. Las empresas
usuarias pueden trabajar con otros proveedores; los contratos del dominio deben
permitir evolución sin acoplar fórmulas al cliente Syscom. No se exige implementar
infraestructura multiproveedor ahora. Los criterios de selección se detallan en el
[RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md).

Primer distribuidor: **Syscom México**, siguiendo su documentación oficial vigente.
La arquitectura admite otros distribuidores después. Cada adaptador declara **qué sabe
hacer**; no se asume paridad de funciones entre proveedores.

Cada cotización **congela** los productos, precios y especificaciones usados, con la
fecha de consulta. Antes de liberar una orden hay que **re-verificar** disponibilidad,
precio y vigencia técnica. No se sustituye un equipo por otro sin re-validar
compatibilidad.

### Estimación de equipos e instalación separada

Decisión de producto: perfiles de estimación por kWp, por panel o por paquete,
siempre **sin instalación**. El configurador de presupuestos del CRM debe explicar
junto a los importes que se capturan precios sin IVA y sin instalación. Toda cotización,
vista de propuesta, PDF y resumen económico debe indicar visiblemente:
**“La instalación no está incluida. Se cotiza por separado.”**
No presentar importes como total instalado ni instalación con valor cero. El
Ingeniero IA aplica perfiles configurados mediante herramientas deterministas.
La cotización debe desglosar **subtotal, IVA y total**, sin incluir instalación.
Reglas y alcance en [RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md).

## 7. Fases

Tabla histórica. El orden de ejecución vigente es E0–E10 del
[plan del proyecto](project-plan.md): NASA y lector preceden el recorrido
automático; API y frontend se entregan por partes. No se amplía el alcance por
conceptos históricos de mano de obra, markup o márgenes sin reglas aprobadas.

| Fase                              | Entregable                                                                                                                                                                                                                                                     |
| --------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **F1 Dashboard y fundamentos**    | Laravel+Inertia+Vue, auth, roles, configuración de empresa, clientes, proyectos, navegación y dashboard funcional, recepción privada de PDF/imagen y validación del JSON interno CFE. _Una empresa instala Helios, entra y recibe prospectos con sus recibos._ |
| **F2 Ingeniería y proveedores**   | Motor energético y cálculos deterministas, adaptadores Syscom y Mock, catálogo técnico normalizado, validación de compatibilidad, selección de productos, estimación de producción                                                                             |
| **F3 Presupuestos configurables** | Panel de costos, porcentajes y márgenes, mano de obra, materiales, perfiles comerciales, motor de presupuestos, impuestos, historial                                                                                                                           |
| **F4 Propuestas**                 | Generación, PDF, personalización de documentos, alternativas, historial y versiones                                                                                                                                                                            |
| **F5 Ingeniero IA**               | Ollama, lector de recibos, agente, herramientas, validación de los resultados del agente                                                                                                                                                                       |
| **F6 API pública**                | REST versionada, auth de consumidores, OpenAPI, control de acceso, límites de uso, webhooks                                                                                                                                                                    |

Fuera de alcance inicial: modelado 3D, análisis estructural avanzado, integraciones CRM
particulares, aplicaciones móviles nativas, billing y cuotas.

## 8. Objetivo inmediato

E1 (contrato común, expediente y ejecución coherente) está cerrada y verificada.
Sigue NASA y lector real para llegar a una API que calcule desde el recibo. Conectar asesor y
embebido al mismo flujo. Cada entrega debe ser utilizable y comprobable; el
objetivo completo incluye presupuesto, equipos, Ingeniero IA, entrega y operación,
con los criterios del [plan del proyecto](project-plan.md).
