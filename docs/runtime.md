# Runtime y verificación de fundamentos

E1 — 2026-10-23.

E2: migración `2026_10_23_000002_create_hsp_catalog` aplicada localmente;
configuración cacheada reconstruida y primer catálogo real de 32 entidades
publicado. Consulta/actualización y frecuencia en [operación HSP](hsp-catalog.md).

E3 en validación: Poppler y túnel SSH local para Ollama; requisitos y comandos en
[lector de recibos](receipt-reader.md). Las dos migraciones E3 del 2026-10-26
se aplicaron en MySQL local tras revisar SQL y probarlas en SQLite en memoria.
El cache alterno de pruebas evita ejecutar la suite contra MySQL local.

Cierre verificado: migración E1 aplicada en MySQL local, sin migraciones
pendientes. Instalación vacía con 171 paquetes (incluidas dependencias dev),
scripts de autoload/discovery, migraciones aisladas y 235 pruebas correctos.
Descarga PHPStan del commit fijado completada desde GitHub codeload; archivo
verificado y cacheado en /tmp para instalar offline con el lock sin modificar.
No se copió el vendor anterior ni configuración/credenciales de la aplicación.
GitHub Actions remoto se ejecutará por su trigger normal, no se afirma ejecutado.

## Requisitos

PHP >=8.4.1, Composer 2 y Node compatible con Vite 8 (Node 22.12+ si se usa 22).
El manifiesto declara PHP ^8.4.1 y el workflow CI usa PHP 8.4; el lock conserva
las versiones de dependencias existentes. Las extensiones exactas se validan
con `composer check-platform-reqs --lock`.

CI instala dependencias/build con SQLite en memoria y ejecuta las comprobaciones;
no requiere una base MySQL de aplicación ni ejecuta migraciones contra datos
compartidos. El workflow remoto se verifica en CI; pasar localmente no demuestra
que se haya ejecutado GitHub Actions.

## Migración E1

Archivo `database/migrations/2026_10_23_000001_create_solar_cases.php`:
crea expedientes, ejecuciones y eventos; agrega FK nullable al intake existente.
No cambia JSON CFE, contactos, archivos ni estados de import; no hace backfill.

Antes de aplicarla en una instalación real: respaldo, revisar conexión efectiva,
`php artisan migrate --pretend`, y ejecutar `php artisan migrate` en la ventana
operativa autorizada. No usar `migrate:fresh` sobre datos compartidos. El rollback
conserva intakes/archivos, pero elimina auditoría y ejecuciones E1: preservar su
respaldo si se requiere conservar ese historial.

## Comprobaciones locales aisladas

```bash
composer validate --strict --no-check-publish
composer check-platform-reqs --lock
npm run check
npm run types:check
npm run build
APP_CONFIG_CACHE=/tmp/helios-test-no-config-cache php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --no-progress --debug
```

El cache alterno evita que una configuración MySQL cacheada invalide las
variables SQLite de phpunit.xml. Build debe terminar antes de la suite que
renderiza vistas: reemplaza archivos que esas pruebas leen. No requiere borrar
caché/configuración del entorno compartido. Aislop revisa los cambios por separado.

Una instalación de Composer en carpeta temporal con manifiesto/lock y
`--no-scripts` verifica resolución y descarga limpia, sin modificar vendor del
proyecto ni ejecutar setup/migraciones. No sustituye la prueba completa de
instalación/actualización de producto prevista en E10.

## Cola E1

La carga usa `ReceiveReceipt` y encola automáticamente si el lector está habilitado.
El procesamiento real y los endpoints internos están conectados; ver
[operación y límites actuales](automatic-receipt-analysis.md). Worker de desarrollo:

```bash
php artisan queue:work database --queue=receipts --tries=3 --timeout=840 --sleep=2
```

No iniciarlo contra una base compartida sin revisar sus trabajos y migraciones.
La prueba de integración ejecuta worker sobre la base aislada de pruebas.
Conservar retry_after 960 s, timeout 840 s y locks 900 s compartidos entre
workers. Fallos de lector, fuente, revisión, estado o HSP devuelven bloqueos
explícitos; solo se conserva cálculo tras datos comprobados/aceptados. No produce
correos ni PDF ni consume otra carga por un fallo técnico.

Workers/scheduler permanentes, monitoreo, retención, SMTP y restauración completa
se cierran en E10. El contrato de estados y límites está en
[procesamiento v1](contracts/solar-processing-v1.md).
