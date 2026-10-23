# Fuentes y derivados de HSP

Fuente: INEGI, Marco Geoestadístico, diciembre de 2025.
URL: https://gaia.inegi.org.mx/wscatgeo/v2/geo/mgee/
Descargado 2026-10-23. `inegi-states-2025.geojson.gz` conserva bytes originales
comprimidos y metadatos; SHA-256 descomprimido:
`170c3ea5ff472f08ece97970dc90ad4ba8c6f503e33859655f315091c105cc91`.

`state-grid-v1.json` es transformación de Helios: intersecciones de áreas INEGI
con malla NASA de 1°, ponderadas en EPSG:6933. No es un promedio publicado ni
avalado por INEGI. No contiene HSP, costos ni datos de clientes.

Original y derivados se usan bajo los
[términos de libre uso de INEGI](https://www.inegi.org.mx/inegi/terminos.html),
con atribución y explicación de la transformación. Los valores NASA se obtienen
y conservan en la base durante actualización, fuera de este directorio.
Generador: `scripts/hsp/build_sampling.py`. Metodología y reproducción:
[catálogo HSP](../../../docs/hsp-catalog.md).
