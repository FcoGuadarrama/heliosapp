# Tema de Helios

La aplicación utiliza Instrument Sans, superficies neutras y azul para las acciones principales. Los colores se definen en `resources/css/app.css`, con valores independientes para los modos claro y oscuro. Los componentes deben usar tokens semánticos, sin colores locales para fondos, bordes o texto.

## Estructura

- Sidebar: superficie sólida, borde lateral y separadores en marca y cuenta; opción activa resaltada incluso en páginas de detalle y edición.
- Cabecera: altura fija de 64 px y superficie de tarjeta.
- Contenido: `app-page` en el layout compartido, ancho máximo de 1280 px y márgenes interiores de 16, 24 y 32 px según el viewport. Las páginas no deben duplicar estos márgenes.
- Encabezados con acciones: `page-header`, apilado en pantallas pequeñas.

## Componentes

- `Heading` predeterminado para títulos de página; variante `small` para secciones de ajustes.
- `surface-panel` para grupos de campos y bloques de detalle: borde, fondo de tarjeta, radio de 8 px y espaciado adaptable.
- `data-table` para contenedores de tablas: superficie de tarjeta y desplazamiento horizontal local.
- `Button` o `buttonVariants` para acciones y enlaces con apariencia de botón. Evitar copiar listas de clases para recrearlos.
- Separación de 24 px entre bloques, 16 px entre campos y 8 px entre etiqueta y control.

Los estados de error deben reservar el color destructivo para errores y acciones destructivas. Los estados de negocio deben tener etiquetas comprensibles además del color.

## Alcance de esta revisión

Aplicado al layout autenticado, dashboard, prospectos, proyectos, recepción de recibos y contenedores de ajustes. La portada pública conserva su composición propia. Autenticación hereda los tokens globales.

## Paleta vigente — 2026-10-22

El usuario rechazó el verde de marca. Usar azul para acciones, foco y selección:
primario claro `hsl(221 83% 53%)` (aproximadamente `#2563eb`), primario oscuro
`hsl(213 94% 68%)` y selección con fondos azules suaves. Mantener superficies
neutras, Instrument Sans y texto legible en ambos modos. Las gráficas sustituyen
su serie verde por violeta. Mensajes e iconos de confirmación usan `text-primary`.
El PDF de junta usa cabecera azul marino, títulos azules y fondo neutro.
Esto sustituye la elección anterior de verde petróleo del agente.
