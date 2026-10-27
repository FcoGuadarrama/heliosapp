# ADR-014 — Catálogo estatal de HSP derivado de NASA POWER

- Autor: Francisco Guadarrama <ingfranciscoguadarrama@gmail.com>.
- Fecha: 2026-10-22
- Estado: implementado E2; catálogo real de 32 entidades publicado y validado.
- Relacionado: [RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md), [ADR-012](012-motor-syscom.md), [ADR-013](013-ubicacion-desde-recibo-cfe.md).
- Sustituye: propuesta previa de consulta NASA puntual por instalación de este ADR.

Orden: E2 del [plan global](../project-plan.md). NASA no depende del lector de
recibos ni de Syscom; recibo real y catálogo válido se integran después en E4.

## Contexto

Las fórmulas Syscom reciben HSP como entrada. El usuario decide obtener el recurso
solar de NASA para mantener un catálogo por estado de México, actualizado
periódicamente, sin consultar NASA por cada lead. Ubicación y geocodificación
siguen aportando contexto y permiten resolver la entidad de la instalación.

## Decisión

Publicar versiones de un catálogo para las 32 entidades, con procedencia NASA.
Consultar desde procesos de actualización separados del análisis del lead.
El parámetro validado e implementado es `ALLSKY_SFC_SW_DWN`, comunidad `RE`, con
unidades y contrato comprobados mediante respuestas reales.

Definir y documentar período climatológico, puntos o malla y método de agregación
geográfica antes de obtener valores representativos por estado. Una consulta
puntual o a la capital no constituye por sí sola un promedio estatal.
La metodología implementada es `inegi-2025-ceres-1deg-area-v1`: ponderación de
intersecciones de celdas NASA nativas de 1° por área estatal INEGI, medida en
EPSG:6933. Período climatológico se toma de respuestas consistentes NASA (primer
catálogo real: 2001–2025). Actualización mensual a las 03:00 UTC del día 1 y
caducidad inicial 90 días, configurables. Son políticas técnicas de Helios.
Detalles, fuentes, evidencia, reproducción y operación en
[catálogo HSP](../hsp-catalog.md).

Normalizar a HSP mediante conversión dimensional:
`HSP (h/día) = irradiación diaria (kWh/m²/día) / 1 (kW/m²)`.
Verificar unidades antes de convertir; rechazar desconocidas, valores ausentes,
de relleno o inválidos. No confundir recurso horizontal estatal con medición
en el plano del panel ni corrección de sombras o inclinación.

Persistir entidad, fuente, parámetro, comunidad, período, puntos/malla, valores
y unidades originales, agregación y conversión versionadas, HSP, fecha y evidencia.
Cada propuesta congela entidad, HSP y versión usados, preservando las fórmulas Syscom.

## Actualización y fallos

Validar antes de publicar una versión. Si la actualización falla, mantener la
última válida y notificar internamente su fecha y el incidente. No alterar las
propuestas previas. Sin valor válido para una entidad, bloquear el cálculo
dependiente; no reemplazar silenciosamente por tabla Syscom ni default 5.0.
Vencimiento almacenado por versión: una actualización fallida no lo extiende.
Tras caducar, el proveedor local devuelve ausencia explícita y no habilita
cálculos nuevos, aunque conserve la evidencia histórica.

El fallo de NASA durante actualización no obliga a pedir datos otra vez al lead.
La tabla Syscom permanece como referencia para pruebas; un modo alternativo
explícito sigue pendiente de autorización de producto.

## Validación prevista

Respuesta real del contrato, conversión, método de agregación, cobertura de
entidades, publicación versionada, snapshots y fallo de actualización.
Implementado: cliente/parser NASA, malla reproducible, versiones/evidencia,
publicación transaccional, consulta local, comandos/schedule y avisos internos
persistidos. Probados contrato real, las 32 entidades y conservación ante fallos.
No interpreta archivos ni altera las fórmulas de interconexión.

## Fuente técnica

[NASA POWER Climatology API](https://power.larc.nasa.gov/docs/services/api/temporal/climatology/).
La verificación E2 del 2026-10-23 incluye respuestas reales y publicación local;
sustituye la limitación anterior de solo haber leído documentación.
