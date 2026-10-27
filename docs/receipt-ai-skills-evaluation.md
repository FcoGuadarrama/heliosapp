# Skills para lector CFE e Ingeniero IA

Evaluación documental: 2026-10-27, Codex. No es un benchmark del lector ni
una decisión de cambiar su implementación. El lector se especifica ahora en
[RFC-003](rfcs/003-lectura-cfe-pdf-ia.md), borrador pendiente de revisión técnica.

## Resultado

| Skill                                                                      | Resultado                                           | Aplicación y límites                                                                                                                                                                                          |
| -------------------------------------------------------------------------- | --------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| anthropics/skills: pdf                                                     | Instalada en Codex                                  | Apoyo para inspeccionar PDF, extraer texto/tablas y preparar escaneos. No interpreta automáticamente el contrato CFE.                                                                                         |
| wshobson/agents: llm-evaluation                                            | Instalada en Codex                                  | Método para comparar modelos/prompts y detectar regresiones. Sus ejemplos son ilustrativos, con funciones por implementar; no constituyen un evaluador listo.                                                 |
| claude-office-skills/skills: smart-ocr                                     | Evaluada, no instalada                              | Ejemplos PaddleOCR sin versión fijada; API, códigos de idioma y resultados requieren comprobación. El ejemplo PDF usa temporales predecibles sin limpieza garantizada ante errores. No aporta validación CFE. |
| PaddlePaddle/PaddleOCR: paddleocr-text-recognition y paddleocr-doc-parsing | Alternativas oficiales identificadas; no instaladas | La guía publicada requiere PaddleOCR 3.7.0+ y token, con servicio oficial por defecto. Evaluar despliegue y tratamiento de datos antes de usar recibos privados.                                              |
| wshobson/agents: langchain-architecture                                    | No instalada                                        | Patrones de herramientas y estado, pero orientados a Python/LangChain. No hay decisión de introducir ese stack en Laravel.                                                                                    |

Las instalaciones contienen instrucciones y recursos para el agente de desarrollo.
No se instalaron motores OCR, modelos ni dependencias de ejecución; tampoco se
modificó el lector activo de Helios. Las skills estarán disponibles en el próximo
turno de Codex.

## Evaluación propuesta del lector

Decisión actualizada: evaluar lectura íntegra por IA con Ollama en desarrollo.
No invertir en OCR local ni comparar Apple Vision/PaddleOCR como candidatos de
producción. Mantener contrato JSON CFE e interfaz común para integrar después
el proveedor de IA de producción elegido. La skill pdf apoya preparación e
inspección, no selecciona un motor OCR para el producto.

1. Preparar referencias privadas cotejadas visualmente: PDF digital, PDF
   escaneado, fotografías y capturas; incluir rotación, desenfoque, recortes,
   varias páginas y documentos que no sean CFE. Separar desarrollo y evaluación.
2. Comparar exactamente tarifa, inicio, fin y consumo; evaluar cada fila del
   histórico por fechas y kWh. Medir omisiones, filas adicionales y datos
   inventados. Distinguir cero impreso, ausencia y dato calculado.
3. Comprobar evidencia por campo y página. Un JSON válido o confianza declarada
   por OCR/modelo no demuestra que el dato esté correcto.
4. Medir exactitud por campo, recibos completamente correctos, cobertura de
   aceptación automática, errores entre los aceptados, revisión y rechazo.
   Desglosar por tipo/calidad de entrada, con numeradores y denominadores.
5. Registrar latencia y fallos técnicos por etapa, versiones y configuración.
   Un timeout no debe clasificarse como ilegibilidad. No publicar datos privados
   en logs, fixtures ni repositorio.
6. Definir con producto los umbrales de aceptación y el volumen representativo
   antes de habilitar aceptación automática. No se fijan cifras arbitrarias aquí.

Para extracción numérica usar comparación con referencias verificadas; métricas
de similitud de redacción o un segundo modelo como juez no reemplazan ese cotejo.

## Evaluación propuesta del Ingeniero IA

El lector entrega datos y evidencia; el Ingeniero utiliza esos datos validados,
HSP y catálogo para seleccionar equipos. Evaluar por separado la extracción y
la decisión, para identificar dónde aparece un error.

El agente debe invocar cálculo y validadores deterministas y referenciar equipos
reales del catálogo. Los casos de evaluación deben cubrir datos insuficientes,
atributos eléctricos ausentes, incompatibilidad, falta de stock y errores del
proveedor. La justificación debe corresponder a las herramientas ejecutadas;
una explicación convincente no acredita compatibilidad.

La compatibilidad automática y selección IA siguen pendientes en Helios. Esta
evaluación no las implementa ni habilita publicación, PDF o correo.

## Fuentes inspeccionadas

- [PDF, fuente original](https://github.com/anthropics/skills/blob/main/skills/pdf/SKILL.md).
- [LLM Evaluation, fuente original](https://github.com/wshobson/agents/blob/main/plugins/llm-application-dev/skills/llm-evaluation/SKILL.md).
- [Smart OCR, fuente original](https://github.com/claude-office-skills/skills/blob/main/smart-ocr/SKILL.md).
- [Skills oficiales de PaddleOCR](https://www.paddleocr.ai/main/en/version3.x/integrations/skills.html).

Validación realizada: instalación confirmada por el instalador, lectura de los
SKILL.md instalados y revisión documental de alternativas. No se ejecutaron
OCR, inferencias ni pruebas sobre recibos durante esta evaluación.
