<?php

return [
    'name' => env('APP_NAME', 'Environment-Manager'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:80'),
    'timezone' => env('APP_TIMEZONE', 'Europe/Berlin'),
    'locale' => env('APP_LOCALE', 'de'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => 'file',
    ],
];
