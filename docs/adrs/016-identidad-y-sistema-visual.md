# ADR-016 — Identidad Helios y sistema visual compartido

- Fecha: 2026-10-27.
- Estado: aceptado por instrucción explícita del responsable de producto;
  implementado en el tema y componentes compartidos, con revisión visual parcial.
- Relacionado: [tema](../design/theme.md), [plan](../project-plan.md).
- Sustituye: preferencia histórica por azul y primera variante oscura con fondos
  verdes. No modifica reglas funcionales del RFC-001.

## Contexto

El responsable de producto pidió una auditoría de diseño con frontend-design y
apple-design. La primera revisión desvió el alcance hacia funcionalidad; se
corrigió el enfoque a composición, jerarquía, tipografía, superficies y estados.
Después autorizó implementar el rediseño con la identidad de Helios.

Se aportaron un PDF solo como referencia cromática y una imagen del logo con
variantes clara y oscura. El contenido técnico del PDF no se adopta como requisito.
El usuario rechazó posteriormente el modo oscuro teñido de verde y pidió una
base negra con contraste verde.

## Decisión

Centralizar colores y superficies en tokens semánticos de `resources/css/app.css`.
Los componentes compartidos consumen esos tokens en lugar de reproducir paletas
por pantalla. Mantener la preferencia de apariencia del usuario.

| Uso                  | Claro                    | Oscuro                              |
| -------------------- | ------------------------ | ----------------------------------- |
| Fondo                | Blanco suave `#F9F9F8`   | Negro neutro `#090909`              |
| Paneles              | Blanco `#FFFFFF`         | Carbón `#171717`                    |
| Texto principal      | Verde profundo `#08221B` | Blanco suave `#F4F4F5`              |
| Acciones principales | Verde profundo `#08221B` | Verde suave `#8AC5A1`               |
| Sidebar              | Verde profundo `#08221B` | Negro `#101010`                     |
| Acento de marca      | Dorado `#C69C53`         | Dorado del logo; verde en controles |

La variante oscura utiliza superficies neutras; reservar verde para controles,
enlaces, foco, cifras y selección puntual. El texto de los botones verdes claros
es oscuro. No usar dorado como texto de bajo contraste sobre superficies claras.

Instrument Sans permanece para interfaz y datos. La composición HELIOS usa
Georgia como aproximación tipográfica de interfaz, sin atribuirle condición de
fuente oficial del logotipo. Mantener títulos de página h1, secciones h2,
anchos de lectura contenidos y jerarquía mediante tamaño, peso y espaciado.

Separar navegación, contenido y paneles con superficies y bordes discretos.
Cabecera fija con translucidez moderada y alternativa opaca. Evitar sombras
repetidas y animaciones decorativas. Controles principales compartidos de 44 px,
foco visible y respuesta al pulsar. Respetar movimiento y transparencia reducidos.

Conservar el JPG original en `public/images/helios-brand.jpg`. El símbolo se
presenta mediante una ventana SVG sobre esa imagen, sin inventar trazados ni
alterar el archivo recibido. Su fondo forma parte de la pieza actual.

## Alcance implementado

Tema global, sidebar, cabecera, títulos, botones, campos, selectores y tarjetas
compartidos; presentación de métricas, portada, acceso y formulario embebido.
Prospectos, ingeniería, proyectos, usuarios y ajustes heredan el sistema.
No se añaden capacidades de negocio por esta decisión. Swagger conserva su
interfaz especializada. La configuración de colores por empresa no está
conectada a este sistema como una capacidad nueva.

## Validación y límites

Las comprobaciones registradas incluyen formato/lint, TypeScript, compilación y
pruebas focales de dashboard/autenticación. El rediseño inicial tuvo revisión en
navegador con datos sintéticos para dashboard, prospectos, dimensionador y perfil,
en 1440/375 px y claro/oscuro, sin desbordamiento horizontal.

El ajuste posterior a negro se verificó en portada y acceso en ambos tamaños y
apariencias. No se afirma una nueva revisión autenticada de todas las rutas con
esa variante ni una auditoría completa de contraste o accesibilidad. Las cifras
y alcances de cada ejecución están en MEMORY.md; esta documentación no repite
esas pruebas ni acredita despliegue.

## Consecuencias y trabajo restante

- Los futuros componentes deben conservar tokens, jerarquía y estados comunes.
- Un SVG oficial transparente permitiría mejorar el asset sin reconstruir el logo.
- Completar revisión visual individual de pantallas y estados restantes,
  incluyendo contraste, teclado y navegación móvil de la variante negra.
- Los cambios de identidad posteriores deben actualizar este ADR o sustituirlo
  explícitamente, junto con la guía de tema; la aprobación del diseño no cierra
  los requisitos funcionales o de operación del producto.

## Alternativas descartadas

Conservar identidad azul del starter, fondos verdes en modo oscuro y aplicar
acabados metálicos del logo a todos los controles. También se descarta convertir
la auditoría visual en ampliación de funcionalidades sin una instrucción de producto.
