# ADR-013 — Resolver la ubicación desde el domicilio del suministro CFE

- Autor: Francisco Guadarrama <ingfranciscoguadarrama@gmail.com>.
- Fecha: 2026-10-22
- Estado: flujo aceptado; entidad manual implementada parcialmente; coordenadas, proveedor y semáforo real pendientes.
- Relacionado: [RFC-001](../rfcs/001-cotizacion-ia-ubicacion-hsp.md).

## Contexto

El contrato CFE v1.0 ya incluye `service.service_address`, con calle, colonia,
municipio, estado y código postal opcionales. No contiene coordenadas. La IA
puede transcribir lo visible pero una dirección extraída no confirma el sitio.

## Decisión

Usar el domicilio del suministro como candidato de ubicación. Conservar extracción
original, evidencia y correcciones por separado. Resolver coordenadas mediante
un adaptador de geocodificación o captura manual en mapa. Usar semáforo basado
en señales del proveedor: verde acepta automáticamente; amarillo solicita
confirmación y rojo solicita corrección o colocación del punto directamente
en el mismo formulario embebido, sin correo para esa acción. No mostrar porcentajes de confianza de IA. Definir y probar
criterios al elegir proveedor antes de habilitar aceptación automática. Registrar
señales, política versionada, aceptación automática o humana, origen, precisión,
actor y fecha; la confianza declarada por el modelo no equivale a precisión geográfica.

No usar domicilio fiscal ni inventar coordenadas. Una coincidencia de código
postal o municipio se muestra como aproximación y requiere revisión. Si hay
ambigüedad, pedir corrección o selección manual sin elegir el primer resultado.
La localización se almacena fuera del JSON CFE; no se cambia su versión 1.0.

## Etapa de implementación actual

Actualización de secuencia 2026-10-23: Google/contornos permanece pospuesto según
RFC-002 y el [plan global](../project-plan.md). El cierre real debe incluir una
vía de confirmación/corrección manual de ubicación y entidad sin presentar las
fixtures geográficas como verificadas. No se exige proveedor pago para avanzar.

### Etapa inicial simulada (histórico)

Por decisión del usuario, simular la consulta a Google con fixtures explícitas
para verde, amarillo, rojo y fallo técnico mientras el lector de archivos sea
mock. No exigir credenciales ni facturación ni realizar llamadas reales. Los
resultados simulados no acreditan ubicación ni generan propuestas reales.
Elección definitiva, criterios reales e integración se revisarán al abordar
la lectura de archivos con IA. La definición de esos servicios no bloquea esta etapa.

## Estado vigente — 2026-10-27

La revisión del recibo permite confirmar/corregir la entidad de instalación.
`ReadAndSizeReceipt` exige `location_status=state_confirmed` y código de entidad
antes de consultar HSP. Esto confirma una entidad para ingeniería estatal;
no acredita coordenadas, precisión del domicilio ni implementación del mapa.
Los lectores reales source/Ollama ya existen; su incorporación no habilitó
geocodificación externa. Google/contornos continúa pospuesto por RFC-002.

## Privacidad

Se conserva la prohibición vigente de enviar domicilios a terceros. Un proveedor
externo necesita una decisión explícita posterior sobre su uso y tratamiento de
datos; queda deshabilitado por defecto. La captura manual es alternativa inicial.
No enviar recibos, nombres ni RPU a geocodificadores. Controlar acceso a dirección,
coordenadas y evidencia con los permisos del prospecto.

## Consecuencias y alternativas

Añade confirmación de ubicación y auditoría. Evita depender de que todos los recibos
contengan un domicilio completo. Se descarta deducir coordenadas con IA o utilizar
la capital del estado como ubicación implícita. Cambiar el sitio invalida la
entidad y entrada HSP estatal aplicables para nuevos cálculos, conservando el historial.

## Validación prevista

Dirección parcial, ausencia de domicilio, múltiples resultados, corrección manual,
confirmación y permisos. No hacer llamadas externas con datos reales en pruebas.
