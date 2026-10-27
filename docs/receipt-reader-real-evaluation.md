# Evaluación de recibos reales — 2026-10-26

Este informe conserva la evaluación v1/v2. La actualización posterior v3,
cotejo de fuente y recorrido ya conectado están documentados en
[análisis automático](automatic-receipt-analysis.md); ese es el estado vigente.

Cinco PDF autorizados por el usuario en `receipts/`, de dos páginas cada uno.
Las muestras y los resultados con contenido personal no forman parte del
repositorio. Se evaluó Ollama en la PC del usuario por túnel SSH, sin crear
prospectos, modificar originales ni enviar documentos a un proveedor externo.

## Método

Se cotejan cuatro campos críticos por documento (tarifa, fechas inicial/final y
consumo actual), además de fases y días no impresos. En el histórico se exige
coincidencia simultánea de ambas fechas y consumo por fila, sin depender del
orden devuelto por el modelo. Hay 55 filas impresas entre los cinco archivos.

Cuatro PDF tienen texto extraíble utilizable como referencia. El quinto tiene
texto interno corrupto pese a ser legible visualmente; sus datos se contrastaron
con las imágenes. Las referencias y las respuestas completas se conservan solo
en `/private/tmp/helios-real-receipts-e3`, con directorio privado y archivos de
datos accesibles exclusivamente por el usuario. No se copian datos personales
a este informe ni a fixtures de pruebas.

## Resultados

| Configuración                                      | Campos críticos correctos | Filas históricas exactas | Observaciones                                                                               |
| -------------------------------------------------- | ------------------------- | ------------------------ | ------------------------------------------------------------------------------------------- |
| qwen3.5:4b, prompt v1, solo imágenes               | 15/20                     | 44/55                    | Tarifa confundida con etiqueta contigua; días calculados sin estar impresos en un documento |
| qwen3.5:4b, prompt v2, imágenes y texto utilizable | 20/20                     | 50/55                    | Todavía cambia fechas históricas e infiere fases no impresas en tres documentos             |
| qwen3-vl:8b, prompt v2                             | Sin resultado comparable  | Sin resultado comparable | Primeros dos documentos con respuesta incompleta, 150 s y 148 s                             |

La segunda pasada tardó 43.8, 42.5, 36.1, 40.5 y 45.6 segundos por documento.
Los cinco resultados quedaron en `needs_review`, sin errores de validación;
las advertencias de coherencia y el cotejo de precisión impiden dar E3 por cerrada.
Un resultado estructuralmente válido no acredita transcripción exacta.

## Correcciones implementadas a partir de las muestras

- Preparación con `pdftotext` además de renderizado. La capa de texto se adjunta
  como datos no confiables, con número de página, solo si tiene marcadores
  reconocibles. Texto vacío o no utilizable se declara como tal y la lectura
  continúa con imágenes; fallos del ejecutable se reportan como fallos técnicos.
- Prompt v2 pide distinguir TARIFA de la etiqueta contigua NO. MEDIDOR y establece
  que NO HILOS no acredita fases ni permite calcular días no impresos. Separa escalones kWh, costos
  informativos del mercado, cargos actuales, subtotales y saldos/pagos anteriores.
  El prompt v1 permanece conservado para trazabilidad de la primera evaluación.
- La comprobación de traslape usa intersección de duración positiva. Dos periodos
  consecutivos que comparten fecha de corte no se traslapan, de acuerdo con la
  duración `fin - inicio` ya usada por el dominio. Periodos duplicados o con
  intersección real siguen generando advertencia. No se cambiaron las fórmulas
  Syscom ni la regla que rechaza importes negativos de conceptos actuales.

## Estado

E3 sigue en validación. La mejora con texto no elimina los errores del modelo
pequeño; no se aprobó activación automática ni se inició E4/E5. El modelo 8B
tampoco completó los dos primeros documentos. Se interrumpió el resto de esa
comparación tras esos fallos; no se contabilizan los otros tres como probados.
Se limpiaron las copias temporales de prueba y se cerró el túnel de esta sesión.

Siguiente trabajo propuesto, no implementado: verificar los campos e históricos
contra la fuente para detectar discrepancias antes de aceptar, impedir fases sin
evidencia explícita y evaluar una configuración/modelo que complete la extracción
con precisión suficiente. Los resultados de este lote no justifican habilitar
generación automática ni cambiar fórmulas para acomodar una lectura incorrecta.

Validación de código: 302 pruebas/1356 aserciones, PHPStan, Pint y npm check.
Aislop: 100; conserva un aviso no fixable por tamaño del validador de coherencia
preexistente (606 líneas), ahora incluido en el escaneo por la corrección de
fechas contiguas. No se suprimieron reglas ni se refactorizó ese archivo completo
como parte de esta evaluación.
