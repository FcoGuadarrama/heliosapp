# Catálogo estatal HSP — metodología y operación E2

Implementado en E2. Parámetros de ingeniería basados en NASA POWER, no precios,
lectura de recibos ni diseño de techo. Ver [ADR-014](adrs/014-hsp-nasa-power.md).

## Fuentes comprobadas

- NASA POWER Climatology Regional API, parámetro `ALLSKY_SFC_SW_DWN`, comunidad
  `RE`, formato JSON. Respuesta real validada el 2026-10-23: API v2.10.1,
  fuente `SYN1DEG`, unidades `kW-hr/m^2/day`, climatología 2001-01-01 a
  2025-12-31. No requiere cuenta ni envía datos de prospectos.
- Fuente: INEGI, Marco Geoestadístico, diciembre de 2025; servicio de las 32
  Áreas Geoestadísticas Estatales. Archivo original y sus metadatos conservados
  en `resources/data/hsp/inegi-states-2025.geojson.gz`. El Marco es información
  geoestadística, no certificación de límites jurídicos de predios.

Las fuentes son públicas. La transformación y los promedios son elaboración
Helios, no valores estatales publicados/avalados por NASA o INEGI.

Referencias primarias:
[NASA Climatology API](https://power.larc.nasa.gov/docs/services/api/temporal/climatology/),
[resolución nativa NASA](https://power.larc.nasa.gov/docs/methodology/data/sources/),
[procesamiento temporal](https://power.larc.nasa.gov/docs/methodology/data/processing/),
[servicio vectorial INEGI](https://www.inegi.org.mx/servicios/catalogounico.html),
[libre uso INEGI](https://www.inegi.org.mx/inegi/terminos.html).

## Metodología v1

Identificador: `inegi-2025-ceres-1deg-area-v1`.

1. Usar polígonos/multipolígonos originales de INEGI en coordenadas geográficas,
   preservando islas y huecos. Rechazar geometrías inválidas; no repararlas en
   silencio ni simplificar la costa para acelerar cálculos.
2. Intersectar cada entidad con celdas de 1° × 1°, centradas en coordenadas
   semienteras, correspondientes a la malla solar nativa observada de NASA.
   La documentación regional histórica menciona 0.5°; la respuesta real solar
   v2.10.1 usa 1°. Cambiar esa malla exige revisar metodología, no interpolar.
3. Medir cada intersección en EPSG:6933, proyección de área equivalente, y
   normalizar su área por la suma territorial cubierta del estado. Así las
   celdas costeras, compartidas y de distinta latitud no pesan lo mismo por
   simple conteo. Nunca usar la capital o el centro estatal como promedio.
4. El generador verificó conservación del área: diferencia relativa máxima
   frente al polígono proyectado ≈2.98e-8, por debajo del límite 1e-4. La malla
   resultante tiene 264 celdas únicas y 448 intersecciones para las 32 entidades.
   Cada entidad suma pesos 1, con tolerancia numérica 1e-8 en ejecución.
5. Descargar 23 teselas de 5° × 5°, secuencialmente; cada respuesta solar actual
   cubre 25 celdas. Son solicitudes del actualizador, nunca por prospecto.
   Coordenadas fuera del país incluidas en una tesela no reciben peso estatal.
6. Validar contrato, coordenadas, unidades, fuente, 12 meses y `ANN`; rechazar
   valores fill, ausentes, no numéricos, negativos o fuera de rango, y cobertura
   estatal incompleta. No mezclar períodos/versiones de API entre teselas ni
   retroceder a un período climatológico terminado antes del catálogo vigente.
7. Para cada mes y `ANN`, aplicar `suma(peso_area × irradiación_celda)`.
   El anual utiliza directamente `ANN` de NASA; no se vuelve a promediar con
   otra regla de días/meses. Valores originales se conservan en evidencia.
8. Convertir dimensionalmente `kWh/m²/día ÷ 1 kW/m² = h/día`; el valor numérico
   se conserva. Unidades distintas se rechazan; no asumir que MJ o Wh son kWh.

Los pesos usan Shapely 2.0.7 / PROJ mediante pyproj 3.6.1, solo en herramienta
Python de generación. PHP no necesita Python, GEOS ni PROJ en producción.
La precisión guardada (seis decimales) es precisión de cálculo/almacenamiento,
no exactitud física del recurso. La celda de 1° limita la resolución, sobre todo
en entidades pequeñas; no equivale a medición del techo, radiación sobre panel
inclinado, sombras ni garantía de producción de una vivienda.

## Reproducción de la malla

```bash
python3 -m venv /tmp/helios-hsp-tools
/tmp/helios-hsp-tools/bin/pip install -r scripts/hsp/requirements.txt
/tmp/helios-hsp-tools/bin/python scripts/hsp/build_sampling.py
npx vp fmt resources/data/hsp/state-grid-v1.json
```

No se descargan polígonos nuevos por actualización NASA. Cambiar límite estatal,
resolución o algoritmo requiere revisar el identificador, regenerar y validar la
malla antes de publicar; no editar pesos a mano. El original tiene SHA-256
`170c3ea5ff472f08ece97970dc90ad4ba8c6f503e33859655f315091c105cc91`.
La malla incluye metadatos originales, hashes del original y archivo comprimido,
proyección, versiones de herramientas, área y pesos. El cargador verifica la
integridad del archivo comprimido y cobertura de las 32 claves INEGI.

## Persistencia, versiones y consulta

- `hsp_catalog_versions`: ejecución de actualización, estado, metodología,
  manifiesto completo y hash, metadatos NASA, publicación y caducidad.
- `hsp_catalog_sources`: consulta por tesela, respuesta original, hash y fecha.
- `hsp_catalog_entries`: 32 valores anuales, 12 mensuales y pesos de cada versión.
- `hsp_catalog_publications`: puntero singleton a la última versión publicada.
- `hsp_refresh_alerts`: aviso interno persistido de cada actualización fallida.

Primero se descarga y valida todo; entradas y puntero se publican juntos en
transacción. Un fallo no publica una mezcla parcial. Versiones previas no se
reescriben ni se elimina su evidencia al publicar otra.

`HspProvider::forState('22')` consulta solamente la base local. Devuelve valor con
fuente/período/versiones/fechas o motivo explícito de ausencia. Clave INEGI de dos
dígitos: normalizar el nombre del domicilio corresponde a E3/E4, no inferir una
entidad silenciosamente. `HspValue::snapshot()` congela los datos que utilizará
E4 en cada cálculo; actualizar catálogo no reescribe snapshots anteriores.

## Actualización y caducidad

Política técnica configurable inicial: actualización mensual, día 1 a las
03:00 UTC, y caducidad 90 días desde publicación validada. Son valores Helios,
no límites científicos impuestos por NASA ni garantías de disponibilidad.
`HELIOS_HSP_REFRESH_CRON` y `HELIOS_HSP_MAX_AGE_DAYS` permiten configurarlos;
la edad admitida es 1–365 días. Rehacer caché de configuración al cambiarlos.
Cambiar edad no extiende retroactivamente versiones ya publicadas.

```bash
php artisan helios:hsp:refresh
php artisan helios:hsp:status
php artisan helios:hsp:status 22
php artisan schedule:list
```

La programación está registrada en Laravel. La ejecución permanente del
scheduler en cada instalación se integra en la operación general E10; no se
instala un cron de sistema ni se declara un daemon activo desde este módulo.
El comando permite actualizar ahora y consultar sin esperar al scheduler.
Conservar caché con locks compartida: actualización única hasta 3600 s y mutex
scheduler 120 minutos. Las consultas son secuenciales, con timeout 60 s,
conexión 10 s y dos intentos; no generar ráfagas por solicitudes de leads.

Si falla, conservar la última versión y su caducidad original; registrar
failed/código, aviso interno y warning de aplicación. `helios:hsp:status` muestra
el último aviso y devuelve fallo si no hay catálogo o ya venció. El aviso interno
no es correo al lead ni notificación externa. Si hay versión anterior vigente,
la consulta sigue disponible; si venció, devuelve `catalog_expired` con valor
nulo. Sin catálogo/entidad válida no sustituir por 5.0 ni por tabla Syscom.

`HELIOS_NASA_POWER_ENDPOINT` es una opción de configuración de operador para
el endpoint regional; su valor por defecto es NASA. No se acepta desde un
formulario. Un proxy debe preservar el contrato y procedencia, no inventar datos.

## Verificación

Pruebas de fórmula ponderada con valores independientes, fixture NASA real,
unidades/fill/malla, 32 entidades, evidencia íntegra, consulta sin red, versión
anterior, fallo de red/almacenamiento, cobertura faltante, período mezclado,
caducidad exacta, lock y reintento de 429. Las pruebas usan HTTP fake y SQLite;
la publicación local de E2 utiliza respuestas reales, no esas fixtures.
