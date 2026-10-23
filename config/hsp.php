<?php

return [
    'endpoint' => env('HELIOS_NASA_POWER_ENDPOINT', 'https://power.larc.nasa.gov/api/temporal/climatology/regional'),
    'max_age_days' => (int) env('HELIOS_HSP_MAX_AGE_DAYS', 90),
    'refresh_cron' => env('HELIOS_HSP_REFRESH_CRON', '0 3 1 * *'),
    'http_timeout_seconds' => 60,
];
