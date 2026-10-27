# Identidad visual de Helios

Decisión formal en [ADR-016](../adrs/016-identidad-y-sistema-visual.md).

## Decisión vigente — 2026-10-27

El usuario autorizó aplicar un rediseño visual con apple-design y la identidad
aportada en el PDF de Helios y la imagen de su logo. Esta decisión sustituye
la elección de azul del 22 de octubre. El contenido técnico del PDF no es
fuente de requisitos para esta intervención.

## Auditoría y correcciones

| Hallazgo                                              | Cambio aplicado                                                                |
| ----------------------------------------------------- | ------------------------------------------------------------------------------ |
| Azul y símbolo Laravel ajenos a la identidad recibida | Paleta Helios y símbolo original presentado mediante ventana SVG sobre la foto |
| Superficies sin una jerarquía visual propia           | Sidebar verde estable, contenido claro y paneles con bordes discretos          |
| Títulos de página pequeños y sin h1 compartido        | Heading genera h1 por defecto y h2 en secciones pequeñas                       |
| Controles de 36 px                                    | Botones, campos y selectores principales de 44 px                              |
| Portada pública del starter                           | Portada Helios con acceso y registro existentes                                |
| Autenticación sin identidad                           | Panel de acceso con marca y tipografía coherentes                              |
| Métricas con el mismo peso que el resto               | Banda verde con cifras en dorado claro                                         |
| Cabecera sin continuidad al desplazar                 | Cabecera fija translúcida con alternativa opaca                                |
| Movimiento reducido incompleto                        | Alternativa global sin desplazamientos ni transiciones prolongadas             |

## Tokens y composición

- Base: verde `#08221B`, dorado `#C69C53`, blanco suave `#F9F9F8`, blanco
  `#FFFFFF`, marfil `#F3F0E8`, texto secundario `#53665F`.
- El dorado de marca se reserva para detalles. En oscuro las acciones usan
  verde suave `#8AC5A1` sobre negro/carbón; el texto principal es `#F4F4F5`.
- Instrument Sans permanece para interfaz y datos. Georgia se usa solamente
  para la palabra HELIOS; no se presenta como la fuente original del logotipo.
- Sidebar verde en claro y negro en oscuro; opción activa con fondo y marca
  lateral según el acento de cada tema.
- Contenido hasta 1280 px, márgenes 16/28/40 px y espaciado consistente.
- Paneles de formularios y tablas usan tokens semánticos. Radios suaves sin
  sombras repetidas. Las tablas conservan desplazamiento horizontal local.
- Controles compartidos con foco visible, respuesta al pulsar y estados deshabilitados.
- Movimiento breve para responder a acciones; sin entradas decorativas automáticas.
- Respetar preferencias de movimiento y transparencia reducidos.

## Marca recibida

`public/images/helios-brand.jpg` conserva la imagen original sin alteración.
AppLogoIcon presenta el símbolo mediante un viewBox; AppLogo añade un nombre
accesible y composición tipográfica para tamaños de navegación. La imagen tiene
fondo, por lo que se presenta como una pieza con superficie propia. No se ha
inventado un vector ni afirmado que el JPG sea un archivo maestro transparente.
Un SVG oficial permitiría sustituir esa ventana sin cambiar los componentes.

## Alcance

Tema global, estructura autenticada, encabezados, controles compartidos, banda
del dashboard, portada, acceso y formulario embebido. Prospectos, ingeniería,
proyectos, usuarios y ajustes heredan el sistema compartido. No se cambian
cálculos, permisos, estados ni comportamiento de negocio. Swagger conserva su
interfaz especializada. Las pantallas no inspeccionadas visualmente se distinguen
en MEMORY.md; heredar el tema no equivale a una auditoría individual de cada ruta.

## Ajuste de modo oscuro — 2026-10-27

El usuario rechazó las superficies verdes en oscuro. Esta decisión sustituye
solo la variante oscura anterior: fondo negro neutro `#090909`, sidebar
`#101010`, paneles carbón `#171717`, bordes `#353535` y texto `#F4F4F5`.
Verde suave `#8AC5A1` para acciones, enlaces, foco y cifras; selección lateral
con verde contenido. El dorado permanece en el logo, no domina los controles.
El modo claro conserva su paleta. La marca activa lateral sigue el token del
sidebar en ambos temas.
