# Helios App — Requisitos de producto

> v0.2 · 2026-10-21 · **Supersede la v0.1**, que asumía SaaS multiempresa.
> Fuente de verdad: documento maestro de producto y arquitectura entregado por el
> propietario el 2026-10-21.

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

## 3. El dashboard es el producto estrella

La primera prioridad es la **interfaz web**, no la API. Módulos:

| Módulo | Contenido |
|---|---|
| **Panel principal** | Resumen de proyectos, cotizaciones, pendientes, diagnósticos, actividad reciente, indicadores del negocio |
| **Clientes** | Registro, contacto, ubicaciones de instalación, historial de proyectos y propuestas |
| **Proyectos solares** | Crear/editar, cliente asociado, ubicación, datos energéticos, diagnósticos, diseños preliminares, equipos, presupuestos |
| **Ingeniero IA** | Análisis, diagnóstico energético, sistema recomendado, alternativas, justificación, advertencias, pendientes para instalación |
| **Proveedores** | Distribuidores, credenciales API, productos, precios, existencias, fichas técnicas, sincronización y caché |
| **Presupuestos** | Costos, porcentajes y márgenes, mano de obra, materiales, servicios, impuestos, cotizaciones, historial de modificaciones |
| **Configuración** | Empresa, usuarios y permisos, parámetros comerciales y técnicos, proveedores, plantillas de propuestas, IA |

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

| Estado | Significado |
|---|---|
| `preliminary_complete` | Propuesta generada, con supuestos y verificaciones documentadas |
| `conditioned` | Hay datos pendientes que afectan el diseño; se enumeran |
| `requires_professional_review` | No hay certeza suficiente, o existe un riesgo técnico relevante |

**R4 — Autonomía limitada.** Una propuesta *preliminar* puede generarse sin intervención.
Un diseño ejecutivo destinado a instalación queda sujeto a verificación física,
eléctrica, normativa y profesional.

**R5 — Reproducibilidad.** Cada cálculo guarda entradas, parámetros, fuente de datos,
supuestos, metodología, versión del motor, resultado y advertencias.

**R6 — Nunca ocultar inconsistencias** para producir artificialmente una propuesta
completa.

**R7 — Dato no disponible ≠ dato cero.** Si una API de proveedor no entrega un campo, el
adaptador lo marca como *no disponible*. Un `200 OK` de un tercero no garantiza que los
datos basten para una decisión técnica.

**R8 — Las credenciales de proveedores nunca llegan al navegador.**

## 5. Entrada de datos CFE: JSON

Helios recibe **información estructurada en JSON**. Después, Ollama interpretará recibos
en PDF o imagen y producirá *el mismo* esquema JSON. El desarrollo inicial usa documentos
JSON de prueba.

**No se construye un formulario de captura manual como flujo principal** (sí existe
corrección supervisada de campos puntuales).

El esquema contempla: versión, identificación del recibo, tarifa CFE, periodos de
facturación, consumo kWh, historial, importes, datos del suministro, advertencias de
extracción, campos faltantes, evidencia/referencia al documento origen y estado de
validación. **No todos los formatos de recibo traen los mismos campos.**

Validación obligatoria: tipos de dato, consumos negativos o inconsistentes, periodos
imposibles, obligatorios faltantes, JSON malformado, inconsistencias entre importes,
periodos y consumos; registro de advertencias; corrección supervisada.
**Un JSON válido no garantiza que la información del recibo sea correcta.**

## 6. Proveedores

Primer distribuidor: **Syscom México**, siguiendo su documentación oficial vigente.
La arquitectura admite otros distribuidores después. Cada adaptador declara **qué sabe
hacer**; no se asume paridad de funciones entre proveedores.

Cada cotización **congela** los productos, precios y especificaciones usados, con la
fecha de consulta. Antes de liberar una orden hay que **re-verificar** disponibilidad,
precio y vigencia técnica. No se sustituye un equipo por otro sin re-validar
compatibilidad.

## 7. Fases

| Fase | Entregable |
|---|---|
| **F1 Dashboard y fundamentos** | Laravel+Inertia+Vue, auth, roles, configuración de empresa, clientes, proyectos, navegación y dashboard funcional, recepción y validación de JSON CFE. *Una empresa instala Helios, entra y crea proyectos energéticos.* |
| **F2 Ingeniería y proveedores** | Motor energético y cálculos deterministas, adaptadores Syscom y Mock, catálogo técnico normalizado, validación de compatibilidad, selección de productos, estimación de producción |
| **F3 Presupuestos configurables** | Panel de costos, porcentajes y márgenes, mano de obra, materiales, perfiles comerciales, motor de presupuestos, impuestos, historial |
| **F4 Propuestas** | Generación, PDF, personalización de documentos, alternativas, historial y versiones |
| **F5 Ingeniero IA** | Ollama, lector de recibos, agente, herramientas, validación de los resultados del agente |
| **F6 API pública** | REST versionada, auth de consumidores, OpenAPI, control de acceso, límites de uso, webhooks |

Fuera de alcance inicial: modelado 3D, análisis estructural avanzado, integraciones CRM
particulares, aplicaciones móviles nativas, billing y cuotas.

## 8. Objetivo inmediato

**Helios App ejecutándose en el navegador, con dashboard funcional, gestión de proyectos
y recepción de JSON CFE.** La documentación y la infraestructura dejan de ser el foco.
