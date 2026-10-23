# ADR-012 — Motor determinista basado en Syscom

- Fecha: 2026-10-22
- Estado: aceptado por decisión explícita del responsable de producto.
- Amplía: ADR-003, ADR-004 y ADR-005 de [arquitectura](../architecture.md).

## Contexto

Existe un [reverse engineering](../syscom-calculator-reverse-engineering.md)
del calculador real de Syscom, con fórmulas y evidencia del bundle publicado.
Exigir otra metodología impediría reproducir la referencia elegida por producto.

## Decisión

Adoptar esa referencia como fuente de verdad para fórmulas, constantes,
redondeos, dimensionamiento y selección. Mantener las correcciones documentadas:
híbrido solo admite inversor híbrido; microinversores se cuentan por módulos
admitidos. El Ingeniero de IA conduce el análisis y selecciona equipos reales
usando herramientas de cálculo y validación; no inventa ni modifica cálculos.

HSP será una entrada trazable. La tabla estatal pertenece al comportamiento de
referencia; una fuente NASA explícita cambia esa entrada y se identifica como tal.
No exige sustituir fórmulas por PVWatts ni por fórmulas generadas por un modelo.

## Consecuencias

Implementar desde cero en Helios según el reverse engineering, por preferencia
explícita del responsable de producto. No copiar ni adaptar el PHP externo.
Las 110 pruebas reportadas de ese proyecto no validan esta nueva implementación.
La primera entrega usa Syscom como primer adaptador de catálogo sin precios.
El dominio es independiente del proveedor: no exige ser comprador de Syscom.
El Ingeniero de IA compara candidatos validados mediante herramientas; la base
técnica omite precio y versiona sus criterios, según el
[RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md).
Versionar motor y parámetros, y congelarlos con los resultados. No extender esta
adopción a tarifas, compensación o impuestos que la referencia no documenta.

## Alternativas descartadas

Inventar fórmulas con IA o bloquear el motor Syscom hasta integrar NASA/PVWatts.

## Validación prevista

Primera entrega: pruebas independientes de interconexión, consumo histórico,
fronteras de redondeo y cantidad de microinversores por módulos admitidos.
Aislado, híbrido y banco de baterías quedan para ampliaciones posteriores.

## Primera implementación — 2026-10-22

Motor puro en `app/Domain/Solar`, versión `syscom-interconnection-v1`, con captura
manual interna en `/engineering/interconnection`. No copia el PHP externo.
Implementa dimensionamiento energético y conteo; no selección automática de
modelos ni validación eléctrica completa. HSP se captura explícitamente como
entrada de prueba; no hay tabla predeterminada ni integración NASA activa.
No genera ni publica cotizaciones comerciales. El flujo completo del RFC sigue
pendiente. Ver [guía de uso](../solar-engineering.md#5-primera-entrega-de-dimensionamiento).
