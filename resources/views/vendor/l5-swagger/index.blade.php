<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentationTitle }}</title>
    <link rel="stylesheet" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <style>
        body { margin: 0; background: #fafafa; font-family: system-ui, sans-serif; }
        .docs-nav { padding: 12px 20px; background: #fff; border-bottom: 1px solid #d1d5db; display: flex; flex-wrap: wrap; gap: 16px; align-items: center; }
        .docs-nav a { color: #115e59; min-height: 44px; display: inline-flex; align-items: center; }
        a:focus-visible, button:focus-visible { outline: 3px solid #0f766e; outline-offset: 3px; }
        .swagger-ui .wrapper { max-width: 1440px; }
        #swagger-ui { overflow-wrap: anywhere; }
    </style>
</head>
<body>
<nav class="docs-nav" aria-label="Documentación">
    <a href="{{ route('dashboard') }}">Volver a Helios</a>
    <strong>Documentación del proyecto</strong>
    <a href="{{ $urlToDocs }}">Descargar OpenAPI JSON</a>
</nav>
<main id="swagger-ui" aria-label="Referencia OpenAPI"></main>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script>
    window.onload = function () {
        window.ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: @json($urlToDocs),
            validatorUrl: null,
            deepLinking: true,
            filter: true,
            docExpansion: 'none',
            defaultModelsExpandDepth: 0,
            supportedSubmitMethods: [],
            presets: [SwaggerUIBundle.presets.apis],
            layout: 'BaseLayout'
        });
    };
</script>
</body>
</html>
