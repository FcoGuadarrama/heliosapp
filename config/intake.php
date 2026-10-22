<?php

return [
    'enabled' => env('HELIOS_INTAKE_ENABLED', false),
    'privacy_version' => env('HELIOS_PRIVACY_VERSION', ''),
    'privacy_text' => env('HELIOS_PRIVACY_TEXT', ''),
    'privacy_url' => env('HELIOS_PRIVACY_URL', ''),
    // Exact HTTPS origins only. No paths, wildcards, credentials or CORS headers.
    'frame_ancestors' => array_filter(explode(',', (string) env('HELIOS_EMBED_ORIGINS', ''))),
    'max_file_kb' => 10240,
    'token_ttl_seconds' => 1800,
    'minimum_fill_seconds' => 2,
];
