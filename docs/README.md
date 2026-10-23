# Documentación de Helios

## Por dónde continuar

[Alcance, estado y orden de entrega](project-plan.md) contiene el inventario
comprobado, dependencias E0–E10, bloqueos y criterios de cierre de todo el
producto. Es la entrada de trabajo para continuar por partes.

## Función de cada documento

| Documento                                                     | Qué define                                                           |
| ------------------------------------------------------------- | -------------------------------------------------------------------- |
| [Requisitos](product-requirements.md)                         | Producto, usuarios, límites y reglas generales                       |
| [RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md)            | Comportamiento completo de recibo a propuesta, precios y seguimiento |
| [Plan global](project-plan.md)                                | Estado real, orden recomendado y aceptación por entrega              |
| [Backlog](development-backlog.md)                             | Plan histórico F1–F6, con remisión al orden vigente                  |
| [Arquitectura](architecture.md)                               | Capas, límites, seguridad y ADR-001 a ADR-011                        |
| [ADR-012](adrs/012-motor-syscom.md)                           | Fórmulas Syscom y motor propio                                       |
| [ADR-013](adrs/013-ubicacion-desde-recibo-cfe.md)             | Ubicación del suministro y confirmación                              |
| [ADR-014](adrs/014-hsp-nasa-power.md)                         | Catálogo estatal NASA y trazabilidad                                 |
| [API](api-design.md)                                          | Consumidores asesor/embebido y contrato por cerrar                   |
| [Modelo de dominio](domain-model.md)                          | Persistencia existente frente a diseños históricos por adaptar       |
| [Contrato CFE](cfe-receipt-schema.md)                         | JSON interno v1.0 y validación; no es formulario público             |
| [Ingeniería](solar-engineering.md)                            | Parámetros, fuentes y motor manual actual                            |
| [Referencia Syscom](syscom-calculator-reverse-engineering.md) | Evidencia de fórmulas; no acredita integración ni precios reales     |
| [Tema](design/theme.md)                                       | Sistema visual existente                                             |
| [RFC-002](rfcs/002-google-ubicacion-poligonos.md)             | Google/contornos pospuesto, separado del camino crítico              |
| [Acta de junta](meetings/2026-10-22-cotizacion-ia.md)         | Registro histórico, no especificación vigente                        |

## Cómo interpretar cambios y pendientes

Fundamentos E1: [ADR-015](adrs/015-expediente-y-procesamiento-compartido.md),
[contrato v1](contracts/solar-processing-v1.md) y
[runtime/migración](runtime.md).

Las instrucciones explícitas del usuario y decisiones aceptadas posteriores
prevalecen sobre diseños históricos. RFC-001 consolida comportamiento; el plan
ordena implementación sin cambiar las decisiones aceptadas. Los ADR no implican
que exista código. Verificar repositorio y actualizar el inventario al cerrar
cada entrega.

Se conservaron las rutas y números de los RFC/ADR para mantener enlaces e
historial. El título de RFC-001 ahora describe el flujo completo. Las partes
históricas quedan identificadas; no se reescribe el PDF de la junta.
