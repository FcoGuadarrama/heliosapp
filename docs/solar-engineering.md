# Helios App — Ingeniería solar: parámetros y fuentes

> v0.3 · 2026-10-22
> **Fuente de verdad para los cálculos:**
> [reverse engineering del calculador Syscom](syscom-calculator-reverse-engineering.md).
> Decisión confirmada por el responsable del producto. Sustituye los requisitos
> anteriores que impedían dimensionar sin coordenadas o sin NASA POWER/PVWatts.

## 1. Base de cálculo adoptada

El motor debe reproducir las fórmulas, constantes, valores predeterminados,
redondeos y selección de equipos documentados en la referencia Syscom:
consumo diario, HSP por estado (default 5.0), PR por tipo de sistema,
dimensionamiento de paneles, inversor, baterías y controlador.
Se conservan las correcciones documentadas: híbrido solo con inversor híbrido
y microinversores según módulos admitidos por unidad.

La IA utiliza este motor determinista; no genera fórmulas ni altera constantes.
El bundle conservado en `docs/reference/syscom-calculator/calc_pretty.js`
es la evidencia para resolver detalles de las fórmulas resumidas.

## 2. Alcance de los parámetros pendientes

NASA POWER se adopta como origen de un catálogo HSP por estado, actualizado
periódicamente, sin consulta por cada instalación. Usar la entidad de ubicación
para resolver su valor y congelar versión y procedencia en la propuesta.
La ubicación continúa aportando contexto al Ingeniero de IA. Período, agregación
geográfica, frecuencia y caducidad quedan pendientes; no tratar una capital como
promedio estatal. Mantener la última versión válida si falla una actualización,
con aviso interno; si no hay valor válido, señalar el bloqueo sin fallback silencioso.
El diseño vigente se detalla en [ADR-014](adrs/014-hsp-nasa-power.md).
PVWatts sigue siendo una posible ampliación futura.
La referencia no define tarifas CFE vigentes, compensación ni reglas fiscales.
Esos datos se requieren únicamente para las cifras que dependan de ellos:
ahorro, retorno e impuestos, respectivamente. Su ausencia no bloquea el
dimensionamiento documentado ni el cálculo de costos y márgenes configurados.
Un futuro `ParameterGate` debe comprobar las dependencias de cada cálculo,
aceptando las constantes y valores predeterminados de la referencia adoptada.

## 3. Reglas de diseño del motor

1. **Fidelidad a Syscom.** Usar HSP por estado y los valores predeterminados
   documentados. Las coordenadas no son requisito para este dimensionamiento.
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

## 5. Primera entrega de dimensionamiento

Disponible en **Dimensionador** del menú o `/engineering/interconnection`, con
sesión y permiso `receipts.import`. No necesita IA, Google, llamadas de red ni
migraciones nuevas. Usa el motor puro `app/Domain/Solar`, implementado desde cero.

Capturar consumo en kWh, días, HSP manual de prueba y watts de un panel.
Opcionalmente agregar histórico y capacidad de módulos por microinversor.
Los campos están vacíos inicialmente: no se suponen HSP ni datos de un recibo.

La calculadora funciona sin API ni cuenta de proveedor. El resultado comienza
con una lista de necesidades: cantidad y watts de los paneles, potencia DC total
y un inversor central con capacidad AC mínima en kW. Si se captura capacidad de
módulos por microinversor, muestra su cantidad como alternativa al inversor
central, no como equipo adicional. La potencia mínima no constituye una selección
de modelo comercial ni acredita compatibilidad. Estructura, cableado y
protecciones requieren el diseño posterior de la instalación; no se calculan
baterías en esta entrega de interconexión.

Ejemplo sintético: 600 kWh, 60 días, HSP 5 y panel de 550 W producen 10 kWh/día,
2.5974025974 kWp requeridos, 5 paneles, 2.75 kWp seleccionados e inversor mínimo
de referencia de 2165 W. Con dos módulos por microinversor se cuentan tres unidades;
esto no acredita su potencia o compatibilidad eléctrica.

El histórico usa la misma duración del período actual para todas sus entradas,
conforme a `calc_pretty.js` líneas 3223–3256. Menos de 330 días usa el consumo
actual; desde 330 usa promedio histórico. Si la cobertura excede 400 días se
normaliza a 365. Registrar cada período una sola vez; el motor no agrega el actual
al histórico automáticamente ni dispone de fechas para detectar duplicados.
El mínimo de inversor se calcula sobre kWp requeridos, antes de redondear paneles.

Esta pantalla solo dimensiona y no guarda resultados, importa archivos, selecciona
equipos del catálogo, comprueba existencias o calcula precios. Cambiar una entrada
retira el resultado anterior hasta volver a calcular. Se identifican la base de
consumo, cobertura histórica y versión del motor. No habilita cotizaciones reales
desde HSP o entradas de prueba.

Pendientes: catálogo Syscom, HSP estatal NASA validado, validadores eléctricos,
Ingeniero IA, perfiles comerciales, snapshots, publicación y entrega del RFC-001.
Precios configurados forman parte del objetivo comercial, aunque esta primera
entrega técnica aún no los calcule.

## 6. RFC y decisiones relacionadas

El diseño de integración se detalla en [RFC-001](rfcs/001-cotizacion-ia-ubicacion-hsp.md)
y los [ADR-012](adrs/012-motor-syscom.md),
[ADR-013](adrs/013-ubicacion-desde-recibo-cfe.md) y
[ADR-014](adrs/014-hsp-nasa-power.md). La tabla Syscom corresponde al modo de
referencia; NASA es el origen del catálogo estatal decidido para la primera entrega.
Las políticas pendientes están identificadas en esos documentos.
