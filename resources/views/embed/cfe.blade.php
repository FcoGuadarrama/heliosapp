<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Enviar recibo CFE · Helios</title>
    <style>
        :root { color-scheme: light; font-family: ui-sans-serif, system-ui, sans-serif; color: #18181b; background: #fff; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px 16px; }
        main { max-width: 560px; margin: 0 auto; }
        h1 { margin: 0 0 12px; font-size: 24px; line-height: 1.25; }
        p { line-height: 1.5; }
        .muted { color: #52525b; font-size: 14px; }
        form { display: grid; gap: 20px; margin-top: 24px; }
        .field { display: grid; gap: 8px; }
        label, legend { font-size: 14px; font-weight: 600; }
        input:not([type=checkbox]), button { min-height: 44px; width: 100%; font: inherit; }
        input:not([type=checkbox]) { border: 1px solid #a1a1aa; border-radius: 6px; padding: 10px 12px; background: #fff; color: #18181b; }
        input[type=file] { font-size: 14px; }
        input[type=checkbox] { width: 20px; height: 20px; flex-shrink: 0; }
        input:focus-visible, button:focus-visible, a:focus-visible { outline: 3px solid #2563eb; outline-offset: 3px; }
        button { border: 0; border-radius: 6px; background: #18181b; color: #fff; padding: 12px 16px; font-weight: 600; cursor: pointer; }
        .notice { border: 1px solid #d4d4d8; padding: 16px; border-radius: 6px; }
        .consent { display: flex; align-items: flex-start; gap: 10px; font-weight: 400; line-height: 1.5; }
        .errors { border: 1px solid #b91c1c; border-radius: 6px; padding: 16px; color: #991b1b; margin-top: 20px; }
        .errors ul { margin-bottom: 0; padding-left: 20px; }
        .error { color: #991b1b; margin: 0; font-size: 14px; }
        .trap { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
        a { color: inherit; }
    </style>
</head>
<body>
<main>
    @if ($submitted)
        <section class="notice" role="status" aria-labelledby="success-title">
            <h1 id="success-title">Recibo recibido</h1>
            <p>Tu solicitud se envió correctamente. El equipo revisará el archivo y se pondrá en contacto contigo.</p>
            <p class="muted">La recepción del archivo no implica una cotización ni un dimensionamiento.</p>
            <a href="{{ route('embed.cfe') }}">Enviar otra solicitud</a>
        </section>
    @else
        <h1>Solicita una cotización solar</h1>
        <p class="muted">Comparte tus datos de contacto y un recibo CFE en PDF o imagen para que el equipo revise tu solicitud.</p>
        @if (count($errors) > 0)
            <section class="errors" role="alert" aria-labelledby="error-title">
                <strong id="error-title">Revisa los datos de tu solicitud</strong>
                <ul>@foreach ($errors as $field => $messages)@foreach ($messages as $message)<li>{{ $message }}</li>@endforeach @endforeach</ul>
                <p class="muted">Por privacidad, vuelve a introducir los datos y selecciona el archivo.</p>
            </section>
        @endif
        <form action="{{ route('embed.cfe.store') }}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="form_token" value="{{ $formToken }}">
            <input type="hidden" name="privacy_version" value="{{ $privacyNotice['version'] }}">
            <div class="trap" aria-hidden="true"><label for="website">Sitio web</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <div class="field">
                <label for="name">Nombre</label>
                <input id="name" name="name" type="text" required maxlength="255" autocomplete="name" aria-invalid="{{ isset($errors['name']) ? 'true' : 'false' }}">
                @foreach ($errors['name'] ?? [] as $message)<p class="error">{{ $message }}</p>@endforeach
            </div>
            <p class="muted">Indica al menos un medio de contacto: correo o teléfono.</p>
            <div class="field">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" maxlength="255" autocomplete="email" aria-invalid="{{ isset($errors['email']) ? 'true' : 'false' }}">
                @foreach ($errors['email'] ?? [] as $message)<p class="error">{{ $message }}</p>@endforeach
            </div>
            <div class="field">
                <label for="phone">Teléfono</label>
                <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" aria-invalid="{{ isset($errors['phone']) ? 'true' : 'false' }}">
                @foreach ($errors['phone'] ?? [] as $message)<p class="error">{{ $message }}</p>@endforeach
            </div>
            <div class="field">
                <label for="receipt">Recibo CFE</label>
                <input id="receipt" name="receipt" type="file" accept="application/pdf,image/jpeg,image/png,image/webp" required aria-describedby="receipt-help" aria-invalid="{{ isset($errors['receipt']) ? 'true' : 'false' }}">
                <p id="receipt-help" class="muted">PDF, JPG, PNG o WebP. Máximo {{ config('intake.max_file_kb') / 1024 }} MB.</p>
                @foreach ($errors['receipt'] ?? [] as $message)<p class="error">{{ $message }}</p>@endforeach
            </div>
            <section class="notice" aria-label="Privacidad">
                <p class="muted">{{ $privacyNotice['text'] }}</p>
                @if ($privacyNotice['url'])<p><a href="{{ $privacyNotice['url'] }}" target="_blank" rel="noopener noreferrer">Consultar aviso de privacidad</a></p>@endif
                <label for="privacy_consent" class="consent"><input id="privacy_consent" name="privacy_consent" type="checkbox" value="1" required>Autorizo el tratamiento de mis datos personales y de mi recibo conforme al aviso de privacidad.</label>
                @foreach ($errors['privacy_consent'] ?? [] as $message)<p class="error">{{ $message }}</p>@endforeach
            </section>
            <p class="muted">El archivo se guardará de forma privada para su revisión. La extracción automática de PDF e imágenes está pendiente.</p>
            <button type="submit">Enviar solicitud y recibo</button>
        </form>
    @endif
</main>
</body>
</html>
