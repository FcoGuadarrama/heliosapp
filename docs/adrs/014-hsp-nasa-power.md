# ADR-014 — Catálogo estatal de HSP derivado de NASA POWER

- Fecha: 2026-10-22
- Estado: catálogo estatal y actualización periódica aceptados; metodología e implementación pendientes.
- Relacionado: [RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md), [ADR-012](012-motor-syscom.md), [ADR-013](013-ubicacion-desde-recibo-cfe.md).
- Sustituye: propuesta previa de consulta NASA puntual por instalación de este ADR.

## Contexto

Las fórmulas Syscom reciben HSP como entrada. El usuario decide obtener el recurso
solar de NASA para mantener un catálogo por estado de México, actualizado
periódicamente, sin consultar NASA por cada lead. Ubicación y geocodificación
siguen aportando contexto y permiten resolver la entidad de la instalación.

## Decisión

Publicar versiones de un catálogo para las 32 entidades, con procedencia NASA.
Consultar desde procesos de actualización separados del análisis del lead.
El parámetro candidato es `ALLSKY_SFC_SW_DWN`, comunidad `RE`; validar contrato
y unidades con respuestas reales antes de habilitar el catálogo.

Definir y documentar período climatológico, puntos o malla y método de agregación
geográfica antes de obtener valores representativos por estado. Una consulta
puntual o a la capital no constituye por sí sola un promedio estatal.
Frecuencia de actualización y caducidad siguen pendientes de decisión.

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
Política de caducidad pendiente; no asumir vigencia ilimitada.

El fallo de NASA durante actualización no obliga a pedir datos otra vez al lead.
La tabla Syscom permanece como referencia para pruebas; un modo alternativo
explícito sigue pendiente de autorización de producto.

## Validación prevista

Respuesta real del contrato, conversión, método de agregación, cobertura de
entidades, publicación versionada, snapshots y fallo de actualización.
La integración no está implementada ni existen cifras NASA verificadas en Helios.

## Fuente técnica

[NASA POWER Climatology API](https://power.larc.nasa.gov/docs/services/api/temporal/climatology/).
La investigación previa verificó documentación, no una respuesta real del endpoint.
