<?php

return [
    'name' => 'ENV Manager',
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Europe/Berlin',
    'locale' => 'de',
    'fallback_locale' => 'en',
    'faker_locale' => 'de_DE',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY','base64:VR1ldjPvxZsuH+IX0gZGZjY8FXV5laQFy4CxJpQ8XbU='),
    'maintenance' => [
        'driver' => 'cache',
        'store' => 'database'
    ],

];
