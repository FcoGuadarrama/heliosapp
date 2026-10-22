# Helios App — API pública

> v0.2 · **Diferida a la Fase 6.** El producto del MVP es el dashboard web.

## Estado

No se construye ahora el sistema de API keys, cuotas ni facturación por solicitudes.

## Qué se hace hoy para no pagarlo después

La única preparación necesaria es arquitectónica, y ya está decidida en
`docs/architecture.md` (ADR-002): **la lógica de negocio vive en servicios de aplicación**,
nunca en controladores Inertia ni en componentes Vue.

Consecuencia práctica para quien implemente cualquier caso de uso del MVP:

- Un caso de uso recibe datos ya validados y devuelve un resultado serializable.
- No conoce la sesión, ni `request()`, ni Inertia.
- El controlador web valida, autoriza y delega. Nada más.

Con eso, la API de F6 expone los mismos casos de uso sin tocar los motores de ingeniería,
proveedores ni presupuestos.

## Alcance previsto para F6

REST versionada (`/api/v1`), autenticación de consumidores externos, documentación
OpenAPI, control de acceso por permisos, límites de uso y webhooks cuando sean necesarios.
El diseño detallado se escribe al llegar la fase, con los casos de uso ya estabilizados.
