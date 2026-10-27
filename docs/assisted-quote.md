# Cotizador asistido y lectura IA

Estado actualizado, 2026-10-27: PDF/imagen → Ollama → JSON CFE → validación/revisión → cálculo
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

## Lectura de recibos con IA

`HELIOS_RECEIPT_READER_DRIVER=ollama` selecciona el lector IA existente.
El driver `source`, su OCR Apple Vision, binario Swift y comando de exportación
fueron eliminados. No existe fallback OCR local. Las lecturas ya almacenadas
conservan su procedencia histórica y no se reescriben.

PDF digital, PDF escaneado e imágenes JPEG/PNG/WebP se preparan mediante
`ReceiptImages`: valida archivos y renderiza todas las páginas PDF con Poppler
para enviarlas al modelo. `pdfinfo`/`pdftoppm` preparan el documento; no hacen OCR.
El adaptador Ollama existente también adjunta texto digital utilizable con
`pdftotext` y conserva su conciliación determinista; retirar esa autoridad de
extracción conforme al RFC-003 es trabajo separado, aún pendiente. No se afirma
que este cambio complete la lectura íntegra por IA especificada en ese RFC.

Configurar habilitación, URL loopback y modelo conforme al
[lector IA](receipt-reader.md). El modelo debe estar disponible en Ollama y, si
corresponde, el túnel y worker deben estar activos. No se asume disponibilidad
por haber cambiado el driver. Reiniciar workers tras cambios de configuración.

La comprobación aislada disponible es `helios:receipts:probe`; ejecuta lectura IA
real sin crear prospectos. No se ejecutó inferencia real durante la eliminación
del OCR. Las evaluaciones source anteriores son históricas y no acreditan la
precisión del lector IA actual.

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
