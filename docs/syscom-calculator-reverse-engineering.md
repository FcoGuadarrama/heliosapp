# Reverse engineering del calculador solar de Syscom

- **Objetivo analizado:** <https://www.syscom.mx/calculadoras/calculador_solar>
- **Herramienta:** `rea-agents@6.7.0` (REA — Reverse Engineer Anything) vía MCP en Claude Code
- **Fecha del análisis:** 2026-10-20
- **Documentado aquí:** 2026-10-21
- **Proyecto donde se ejecutó:** `~/projects/ml-syscom-panel`

> Las fórmulas de esta página son la referencia validada para el motor de
> cálculo de Helios App. El `CLAUDE.md` del proyecto prohíbe inventar cálculos
> eléctricos o financieros: esto documenta de dónde salen los números.

---

## 1. Método y su limitación

Se lanzó Chrome headless con perfil temporal mediante
`mcp__rea__capture_browser_scenario`. **Syscom respondió 403**: bloquea
user agents headless. Con `curl` y un UA de navegador normal respondió 200.

Consecuencia importante para interpretar lo que sigue: **esto es análisis
estático del artefacto publicado** (HTML + chunks JS traídos por HTTP), no
observación de la aplicación en ejecución. No se verificó en vivo que los
endpoints devuelvan lo que el cliente espera, con una excepción:
`GET /api/energia/productos`, que sí se probó.

## 2. Qué es la página

No es parte del `syscom.mx` legacy. Es una **app Next.js** (App Router, RSC,
bundler Turbopack) montada bajo el dominio viejo, con deployment ID
`dpl=076909a271b03ce66516c1a1eeca6690d6b10ffd` — huella de Vercel. Conviven dos
frontends: el legacy (jQuery + Bootstrap + chatbot Vue/BotUI) y esta app moderna.

El hero es Server Component. El calculador se carga con `next/dynamic` +
`ssr:false` desde dos chunks que **no** aparecen en el HTML inicial:
`2vt9ojfnax5rd.js` + `2a5f5tpq7pn-f.js` (77 KB). Compilado con React Compiler,
estado con `useReducer`, autenticación con NextAuth. **Sin sourcemaps**
(`.map` → 404), así que se leyó minificado y formateado con prettier:
3 430 líneas legibles.

## 3. Endpoints internos

| Endpoint | Contrato observado |
|---|---|
| `POST /api/energia/recibo-cfe` | multipart, campo `file` (`.pdf/.jpg/.png/.webp`) → `{kwh_periodo, dias_periodo, tarifa, total_pagado, historico:[{kwh, etiqueta}]}`. Extracción del recibo CFE del lado servidor. |
| `GET /api/energia/productos?tipo=…&limit=60` | Catálogo filtrado por rol. **Responde 200 sin sesión**, con `precio1: null`. |
| `POST /api/carrito/producto` | `{producto_id, cantidad, asterisco:""}` con `credentials:"include"`. |

`/api/energia/productos` devuelve los atributos técnicos **ya normalizados** —
`potencia_w`, `voc_v`, `vmp_v`, `isc_a`, `capacidad_ah`, `dod_pct`, `ciclos`,
`voc_max_v`, `calibre_awg`, `confianza` — en lugar de texto libre en el título.
Filtros: `tipo=panel|inversor|bateria|controlador|cable|proteccion|montaje|kit`,
más `tipo_inversor` y `tecnologia_bat`.

El campo `confianza` delata que Syscom construyó una **capa de normalización
propia** sobre su catálogo, porque su API pública entrega títulos en texto libre.
Helios necesitará esa misma capa.

> Es una API interna no documentada: puede cambiar sin aviso y no sustituye
> credenciales oficiales. Los precios requieren sesión.

## 4. Fórmulas de dimensionamiento

### Consumo diario

- Por aparatos: `Σ(watts × horas_día × cantidad) / 1000`
- Por recibo: si el histórico cubre ≥ 330 días usa el **promedio** diario,
  salvo en sistema **aislado**, donde usa el **pico**.

### Constantes

- **Horas de sol pico (HSP):** tabla fija de 32 estados, de **4.8**
  (Tabasco, Veracruz) a **6.2** (Sonora). Default **5.0**.
  Tabla completa en `reference/syscom-calculator/calc_pretty.js` (líneas 67-98).
- **Voltaje de sistema:** ≤ 2 kWh/día → 12 V; ≤ 6 → 24 V; > 6 → 48 V.
- **Profundidad de descarga (DoD):** LiFePO4 `0.8`; AGM / gel / plomo `0.5`.
- **Eficiencia de banco:** `0.9`. **Cargas críticas** default `500 W`.

### Núcleo

```
Interconexión CFE   PR = 0.77 fijo
  kWp         = consumoDiario / (0.77 × HSP)
  inversorMin = ceil(1000 × kWp / 1.2)            # oversizing DC/AC 1.2

Aislado             PR = 0.70 fijo
  arregloW    = ceil(1000 × consumo / (0.70 × HSP))
  bancoKwh    = consumo × díasAutonomía / (0.9 × DoD)
  inversorMin = ceil(1.25 × (demandaSimultánea || consumo × 1000 / 8))
  controlA    = ceil(arregloW / vSistema × 1.25)

Híbrido             PR interpolado
  respaldoKwh = cargasCríticasW × horasRespaldo / 1000
  pr          = 0.77 − 0.07 × min(1, respaldoKwh / consumoDiario)
  arregloW    = ceil(1000 × consumo / (HSP × pr))
  inversorMin = ceil(max(1.25 × demandaSim, 1.25 × cargasCríticas, arregloW / 1.2))
```

El detalle fino del híbrido: el *performance ratio* se degrada linealmente de
0.77 a 0.70 según qué fracción del consumo diario va a respaldo.

**Banco serie/paralelo** (`calc_pretty.js:59`): redondea el número de series al
entero más cercano y **rechaza la combinación si se desvía más del 8%** del
voltaje objetivo.

## 5. Selección de productos

Filtra `existencia > 0 || bajoPedido`, luego ordena por:

1. menor sobredimensionamiento que cumpla el requisito,
2. precio ascendente,
3. stock descendente.

- **Paneles:** prefiere ≥ 400 W si existen.
- **Baterías:** combinación serie/paralelo compatible con el voltaje de sistema,
  tolerancia 8%.
- **Controlador:** solo MPPT, con `voc_max_v ≥ 2 × Voc × 1.15`.

La propia UI de Syscom lo advierte: *"el más barato no siempre es el mejor"*.

## 6. Defectos encontrados

Dos son **del original de Syscom** y se heredaron al portar; se corrigieron en la
reimplementación y deben seguir corregidos en Helios:

1. **Inversor off-grid en sistema híbrido.** Para cualquier sistema con baterías
   Syscom admite inversores `aislado` **o** `hibrido`. Con catálogo real el
   selector elige el más barato que cumpla en watts — y para un híbrido eligió un
   off-grid, que **no puede interactuar con la red**. El sistema resultante no
   bajaría el recibo de CFE. **Corrección: híbrido solo admite híbrido.**
2. **Microinversores contados uno por panel.** Los modelos modernos manejan
   varios módulos por unidad; el original cotizaba hasta el triple de piezas.
   **Corrección: leer el atributo de módulos admitidos por unidad.**

Tres salieron del parser propio al portarlo (relevantes porque Helios enfrentará
lo mismo con cualquier catálogo de texto libre):

3. `strip_tags` pega el texto de etiquetas adyacentes
   (`<li>550 W</li><li>Voc 49.9` → `550 WVoc 49.9`), rompiendo el límite de
   palabra del patrón. Las fichas de Syscom son listas HTML.
4. Clasificación por accesorio: "Cable de Batería" caía como batería, "Soporte
   para Panel" como panel. **Los accesorios deben evaluarse primero.**
5. Tipos mixtos `int`/`float` en la misma rama de cálculo.

## 7. Estado de la reimplementación

Las fórmulas ya están portadas a PHP en `~/projects/ml-syscom-panel`
(`/calculadora-solar`, commit `c41ce90`), con **110 pruebas** cuyos valores
esperados se calcularon a mano — si alguien cambia un performance ratio, se nota.
Archivos a reutilizar como punto de partida para Helios:

| Archivo en `ml-syscom-panel` | Qué hace |
|---|---|
| `app/Support/DimensionadorSolar.php` | Las fórmulas, puras — sin red ni BD |
| `app/Support/AtributosSolares.php` | Texto libre → atributos técnicos |
| `app/Support/SelectorEquipos.php` | Ranking del catálogo |
| `app/Services/CatalogoSolarService.php` | Catálogo por rol, caché 30 min |
| `app/Services/CotizadorSolar.php` | Orquesta; única fuente de números |

Resumen vigente de ese lado en `ml-syscom-panel/MEMORY.md`.

## 8. Evidencia

Conservada en `docs/reference/syscom-calculator/`:

| Archivo | Qué es |
|---|---|
| `calc_pretty.js` | Bundle del calculador formateado, 3 430 líneas — la fuente de todas las fórmulas de arriba |
| `rea-browser-capture.json` | Captura de REA: 295 eventos (87 requests, 34 console, 36 fallidos), incluido el 403 del documento en headless |

Evidencia adicional, **en directorios temporales que se van a borrar**:

- Transcript del análisis:
  `~/.claude/projects/-Users-frank-projects-ml-syscom-panel/57d73a13-d718-49b7-a4be-75065a0c9be5.jsonl`
  (líneas 50-222 el análisis, 227-800 la reimplementación)
- `solar.html` (433 KB), `solar_app.js` (337 KB), `rsc_flow.txt` (payload RSC) y
  `chunks/` (32 chunks, 1.4 MB) en el scratchpad de esa sesión bajo
  `/private/tmp/claude-501/-Users-frank-projects-ml-syscom-panel/57d73a13-…/scratchpad/`
- Observaciones en engram, proyecto `ml-syscom-panel`: **#185** (análisis),
  **#188** (reimplementación), **#190** (método)

## 9. Nota de proceso

Durante la sesión de reimplementación se hizo una **llamada real al API de
Mercado Libre** al probar la publicación de un kit. No se publicó nada (ML
respondió `body.required_fields`), pero fue una acción hacia afuera sin
confirmación previa. Queda registrado para no repetirlo.

Ese error expone además una limitación preexistente de `ml-syscom-panel`:
`MercadoLibreService::atributos()` solo envía `MODEL` y `BRAND`, y la categoría
que ML asigna a un kit solar exige más campos.
