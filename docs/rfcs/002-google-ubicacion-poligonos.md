# RFC-002 — Integración Google para ubicación y contornos de edificios

- Fecha: 2026-10-22.
- Estado: borrador pospuesto por decisión posterior del usuario; no bloquea RFC-001.
- Implementación y pruebas con Google: pendientes.
- Relacionado: [RFC-001](001-cotizacion-ia-ubicacion-hsp.md) y
  [ADR-013](../adrs/013-ubicacion-desde-recibo-cfe.md).

## Objetivo

Preparar Google Cloud e integrar ubicación y contornos de edificios para Helios.
La solicitud inicial fue preparar conexión real y consola Cloud. Después el
usuario pospuso Google/polígonos; prevalece esa decisión. Se conserva la guía
como referencia futura, sin activar servicios ni acreditar cobertura.
El [plan global](../project-plan.md) mantiene esta ampliación separada del
cálculo con HSP estatal; una vía manual de ubicación debe permitir avanzar.

## Alcance inicial propuesto

- Maps JavaScript API para mostrar mapa y permitir confirmar ubicación.
- Geocoding API v4 para resolver dirección y consultar contornos mediante
  SearchDestinations, cuando estén disponibles.
- Verificar un caso de prueba público en México antes de comprometer cobertura.
- Conservar ubicación y geometría fuera del contrato JSON CFE v1.0.
- Permitir confirmar/corregir el punto y manejar ausencia de contorno.

El contorno representa una construcción; no acredita límites del predio,
propiedad, superficie instalable ni resistencia estructural. El análisis de
techos mediante Solar API es una ampliación por evaluar, no requisito para
obtener el contorno. No cambia el catálogo HSP estatal NASA del RFC-001.

## Preparación en navegador

1. Crear o seleccionar proyecto de desarrollo en Google Cloud.
2. Asociar facturación y configurar presupuesto con alertas.
3. Habilitar Maps JavaScript API y el servicio indicado por la guía oficial de
   configuración de Geocoding v4.
4. Configurar cuotas disponibles para las pruebas. Las alertas de presupuesto
   no constituyen un límite automático de gasto.
5. Crear credencial web restringida a Maps JavaScript API y a los orígenes
   concretos de desarrollo; definirlos antes de guardar restricciones.
6. Preparar autenticación separada para llamadas desde servidor a Geocoding.
   Resolver restricción por IP según el entorno o autenticación OAuth;
   no exponer esa credencial en el frontend ni compartirla en conversación.

## Decisiones técnicas pendientes

- Confirmar contrato, disponibilidad y coste de SearchDestinations en el proyecto.
- Determinar correspondencia entre domicilio, edificio y geometría devuelta.
- Definir criterios del semáforo con señales reales y casos de prueba.
- Revisar condiciones de uso, almacenamiento y visualización de geometrías.
- Concretar configuración, credenciales, cuotas, tiempos de espera y errores.
- Documentar tratamiento de dirección/coordenadas y ajustar la prohibición
  actual del contrato CFE antes de enviar domicilios reales de leads a Google.
  La preparación se prueba con datos públicos de referencia.

## Criterios de aceptación propuestos

- Mapa visible con credencial restringida y ubicación confirmable.
- Geocodificación y contorno probados con evidencia de respuesta real.
- Ausencia de cobertura o error no produce geometrías inventadas.
- Credencial de servidor ausente del bundle y respuestas públicas.
- Datos del lead limitados a lo necesario; no enviar recibo, nombre ni RPU.
- Costes, cuotas y restricciones documentados antes de habilitar uso real.

## Referencias

- [Configurar Maps JavaScript API](https://developers.google.com/maps/documentation/javascript/get-api-key).
- [Configurar Geocoding v4](https://developers.google.com/maps/documentation/geocoding/get-api-key-v4).
- [Contornos y migración a SearchDestinations](https://developers.google.com/maps/documentation/geocoding/guides-v3/building-attributes).

Este borrador no acredita que se haya creado proyecto, asociado facturación,
generado credenciales o modificado la aplicación.
