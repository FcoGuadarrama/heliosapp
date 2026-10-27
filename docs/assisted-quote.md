# Cotizador asistido y extracción de fuente

Estado local, 2026-10-26: PDF → texto/OCR → JSON CFE → revisión → cálculo
técnico → selección manual de equipos Syscom → borrador interno versionado.
No se publica ni envía al cliente. No hay selección eléctrica automática.

## Uso en el CRM

1. Adjuntar un recibo nuevo al prospecto. El archivo queda privado y se encola.
2. Cotejar tarifa, fechas, consumo e histórico con el PDF descargable. Confirmar
   la entidad de instalación y guardar una revisión con motivo.
3. Tras el cálculo, abrir **Cotizador asistido · borrador interno** en ese recibo.
4. Buscar modelos en Syscom, agregarlos e indicar cantidades. Confirmar la
   selección y guardar. El servidor consulta otra vez los precios en una petición
   por lote: los importes del navegador no son la fuente del borrador.
5. Cada guardado nuevo congela otra versión. Repetir el mismo envío no la duplica.

Las lecturas anteriores se conservan; cambiar de lector no las reescribe.
Los errores de extracción son editables antes del cálculo. La potencia de panel
sigue sin configurarse: no se inventa una cantidad de módulos. Las cantidades
comerciales son explícitas del asesor, pendientes de ingeniería.

## Extracción local

`HELIOS_RECEIPT_READER_DRIVER=source` selecciona `SourceReceiptReader`.
`pdfinfo` limita el PDF a seis páginas/20 MiB. `pdftotext -layout` conserva el
texto digital cuando reconoce encabezados CFE; si no, se ejecuta el binario OCR
configurado. PNG/JPEG/WebP van directamente al OCR, con límite de 24 MP.
No se usa Ollama para completar campos ausentes en este modo.

En Mac el OCR usa [Apple Vision](https://developer.apple.com/documentation/vision/vnrecognizetextrequest),
en español/inglés, nivel accurate, sin corrección lingüística. Conserva página,
texto, confianza y cajas normalizadas con origen superior izquierdo. La confianza
no es una probabilidad calibrada de exactitud ni habilita aceptación automática.
Las filas se agrupan por posición; el consumo se busca bajo el encabezado
**Total periodo**, separado de lecturas del medidor. Ambigüedad deja el candidato
sin completar. No se calcula un dato para presentarlo como impreso.

Compilación local (requiere herramientas Swift de Apple):

```sh
mkdir -p storage/app/private/tools
swiftc scripts/receipts/vision_ocr.swift -o storage/app/private/tools/helios-vision-ocr
```

Configurar `HELIOS_RECEIPT_OCR_BINARY` con la ruta absoluta del binario, después
`php artisan config:cache` y reiniciar el worker receipts. Vision necesita acceso
a los servicios de macOS; el sandbox del agente lo bloquea. El worker local se
ha iniciado fuera de ese sandbox mediante aprobación. Esto no instala un servicio
permanente. Para Linux/Windows falta un adaptador OCR equivalente: este binario
es de macOS, no se presenta como portable ni como instalado en la PC Windows.

Exportación privada independiente, sin escribir en el CRM:

```sh
php artisan helios:receipts:extract /ruta/recibo.pdf --output=/directorio/privado/nuevo.json
# --ocr fuerza OCR para evaluar incluso un PDF con texto.
```

El archivo se crea con permisos 0600 y no sobrescribe uno existente. Contiene
datos del recibo: no subirlo a Git. Es `receipt-candidates/1`, con fuente, páginas,
texto y candidatos. La aplicación deriva de ahí el contrato CFE 1.0.
Todo resultado source permanece sujeto a revisión, incluso si todos los campos
están presentes; importes, domicilios y fases desconocidos quedan vacíos.

Evaluación de cinco muestras reales, sin guardar expedientes: cuatro con texto
digital y una con OCR por codificación de fuente dañada. Comparadas con las
referencias privadas: **20/20 campos críticos** (tarifa, inicio, fin, kWh),
**55/55 filas históricas** (fechas y kWh), sin inventar fases ni días impresos.
No se evaluó la exactitud de todos los caracteres, importes ni otros formatos;
el OCR sí erró en una lectura del medidor. Son muestras usadas para desarrollar,
no un conjunto independiente ni garantía de precisión universal. Primer arranque
OCR observado ~27 s; ejecución final caliente ~1.1 s. No es un SLA.

## Syscom y precio

Acceso servidor por la [API oficial OAuth](https://developers.syscom.mx/guide),
credenciales `SYSCOM_CLIENT_ID` y `SYSCOM_CLIENT_SECRET` en `.env`. Tokens en
memoria durante la consulta, sin enviarlos al navegador ni imprimirlos. URLs
oficiales fijas y sin redirecciones; errores externos se presentan saneados.

```sh
php artisan helios:syscom:probe
```

Consulta real de catálogo y ficha verificada con la cuenta configurada. Se pide
`moneda=mxn`, `iva=false`, inventario y paginación; el lote admite hasta 20 IDs.
Los campos de distribuidor, MAP y lista siguen separados. Este borrador usa
**`precio_lista`** como referencia solicitada por el usuario; no confunde ese
campo con costo de compra ni sustituye un precio ausente por otro. Falta validar
comercialmente que sea la referencia pública deseada para cada familia/modelo.
El contenido HTML del proveedor no se renderiza como HTML.

La indicación del usuario de usar la referencia pública Syscom amplía la política
anterior de tarifas propias por kWp/panel/paquete. Es un modo de borrador por
equipos, sin convertir todavía las demás modalidades en funcionalidades.

Dinero en centavos: precio unitario redondeado half-up a dos decimales, después
cantidad, después IVA sobre subtotal una sola vez. Se conserva el precio fuente
original. `HELIOS_QUOTE_VAT_RATE` es un porcentaje explícito; vacío deja IVA y
total pendientes, nunca cero implícito. En el entorno local se configuró 16% por indicación del usuario; borradores
anteriores conservan su snapshot. Configuración fiscal a cargo del operador;
el asesor no altera la tasa por petición. **La instalación no está incluida. Se
cotiza por separado.** No se suman envío ni conceptos no seleccionados.

La existencia desconocida y la insuficiente se distinguen y se conservan en la
versión. No se reserva stock. La compatibilidad siempre figura pendiente. No se
promete cobertura técnica por agregar productos. Cambiar revisión o sustituir
recibo impide crear un borrador con el cálculo anterior. No hay PDF comercial,
correo, enlace público, compra o pedido automático.

## Persistencia y acceso

Migración aditiva `000004`: `equipment_quote_drafts`, vinculada al expediente,
cálculo y actor, con versión, UUID de envío y snapshot de precios/stock/totales.
Bloqueo del expediente para numerar versiones y revalidar cálculo vigente.
Consultas al proveedor fuera de la transacción. Se exige revisión humana previa.
Solo usuario activo con permiso y alcance del recibo; no hay rutas públicas.

Rutas autenticadas adicionales:

- `GET /intakes/{intake}/catalog?query=…&page=…`
- `POST /intakes/{intake}/quote-drafts`

`ReceiptAnalysis` proyecta únicamente el último borrador del cálculo actual.
La lista de compra y otros precios de cuenta no se devuelven al navegador.

## Validación y límites técnicos

337 pruebas/1505 aserciones; PHPStan, Pint, TypeScript, check y build. Se verificó
en navegador aislado con SQLite: búsqueda real, selección, guardado, IVA pendiente,
375 px, horizontal y escritorio, oscuro/claro y texto ampliado, sin overflow ni
errores JS. No se crearon cotizaciones para clientes reales durante las pruebas.

Aislop 100: permanece el aviso previo de tamaño de ReceiptCoherenceValidator.
Dos avisos nuevos se consideran falsos positivos: URL canónica de la API (es
estable, no una URL de despliegue) y `client_credentials` marcado como ID fijo
(es el nombre del grant OAuth; el client_id real viene de configuración).
No se deshabilitaron reglas. Swift no está cubierto por Aislop: se compiló y
ejecutó su binario. El aviso opcional fontaine del build sigue preexistente.
