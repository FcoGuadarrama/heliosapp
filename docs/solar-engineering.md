# Helios App — Ingeniería solar: parámetros y fuentes

> v0.2 · 2026-10-21
> **Este documento no contiene todavía ninguna fórmula ni constante verificada, y es
> intencional.** Helios no puede inventar parámetros fotovoltaicos ni reglas de CFE.
> Aquí se registra qué hace falta, de dónde debe venir y qué bloquea mientras no exista.

## 1. Parámetros que bloquean el cálculo

El servicio `ParameterGate` comprueba estos conjuntos antes de permitir cualquier cálculo.
Si falta alguno o no está marcado como verificado, el análisis **no entrega la cifra que
dependería de él** y el resultado baja a `conditioned` o `requires_professional_review`,
nombrando explícitamente el parámetro ausente.

| #   | Parámetro                               | Qué bloquea si falta                                | Origen requerido                                  |
| --- | --------------------------------------- | --------------------------------------------------- | ------------------------------------------------- |
| P1  | Irradiación por coordenadas             | Estimación de producción y todo el dimensionamiento | Fuente externa verificada (candidata: NASA POWER) |
| P2  | Metodología de estimación de producción | Producción anual y mensual, ahorro, retorno         | Referencia documentada (candidata: PVWatts V8)    |
| P3  | Modelo de pérdidas del sistema          | Producción realista                                 | Fuente técnica reconocida y citada                |
| P4  | Tarifas CFE vigentes y su estructura    | Costo actual, ahorro, retorno de inversión          | Publicación oficial de CFE / DOF                  |
| P5  | Esquema de compensación aplicable       | Cálculo del ahorro real                             | Normativa oficial vigente                         |
| P6  | Reglas fiscales y su vigencia           | Total de la cotización                              | Fuente fiscal oficial                             |

**Ninguno de estos valores debe tomarse de la memoria de un modelo de lenguaje.**

## 2. Candidatas a verificar

Antes de integrarse, cada fuente requiere revisión de: cobertura geográfica para México,
resolución espacial y temporal, latencia y límites de uso, condiciones de licencia para
uso comercial, estabilidad de la API y política de versiones.

- **NASA POWER** — propuesta como fuente inicial de irradiación.
- **PVWatts V8 (NREL)** — propuesta como referencia de estimación de producción.

Hasta completar esa revisión, ambas quedan como _candidatas_, no como decisión tomada.

## 3. Reglas de diseño del motor

1. **Coordenadas, no promedios regionales.** No se sustituye la irradiación de un sitio
   por un valor genérico por estado. Sin coordenadas, no hay estimación.
2. **Toda cifra declara su base.** Valor, unidad, fuente, supuestos y confianza viajan
   juntos. Una cifra sin procedencia es un defecto, no un resultado.
3. **Las verificaciones que requieren inspección física se enumeran como pendientes**, no
   se aproximan: medidas reales de techo, sombreado, estado del tablero, capacidad de la
   acometida, condición estructural.
4. **Dimensionamiento energético ≠ diseño ejecutable.** El motor produce lo primero. Lo
   segundo exige verificación profesional.
5. **Las validaciones eléctricas son deterministas y obligatorias**: compatibilidad
   módulo–inversor, límites de tensión y corriente, rangos MPPT, relación DC/AC,
   condiciones de temperatura, restricciones del fabricante y protecciones requeridas.
   Una configuración con verificaciones fallidas no es viable, sin excepción.
6. **Versionado.** El motor tiene versión semántica propia. Cada conjunto de parámetros
   tiene la suya. Ambas se guardan con cada resultado.

## 4. Reglas de presupuesto que sí están definidas

Son aritmética comercial, no ingeniería eléctrica, y no dependen de ninguna fuente externa.

**Markup sobre costo:**

```
Precio = Costo × (1 + porcentaje / 100)
```

**Margen bruto objetivo:**

```
Precio = Costo / (1 - margen / 100)        con margen < 100
```

No son lo mismo: un markup del 30% sobre un costo de 100 da 130; un margen bruto del 30%
da 142.86. El sistema debe validar que el margen objetivo sea menor a 100, impedir
divisiones por cero, operar con enteros de centavos y aplicar una política de redondeo
explícita y documentada en cada cotización.

Los porcentajes que use cada empresa son **configuración del comprador**. Helios no trae
valores recomendados ni predeterminados obligatorios.

## 5. Qué necesito del negocio

Para desbloquear P1–P6 hace falta, en este orden:

1. Decisión sobre la fuente de irradiación y confirmación de sus condiciones de uso.
2. Metodología de estimación de producción que Helios debe replicar, con su referencia.
3. Modelo de pérdidas a aplicar, con su fuente.
4. Tarifas CFE vigentes con su publicación oficial y fechas de vigencia.
5. Esquema de compensación aplicable a los proyectos que atiende la empresa.
6. Reglas fiscales aplicables y su vigencia.

Mientras tanto, el desarrollo del motor avanza: las estructuras, los contratos, el
versionado, la trazabilidad y las pruebas se construyen con los parámetros como datos
externos cargables. Lo que no avanza es la emisión de cifras.
