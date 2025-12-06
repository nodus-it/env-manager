<?php

use Illuminate\Support\Str;

return [
    'driver' => 'database',
    'lifetime' => 60,
    'expire_on_close' => false,
    'encrypt' => false,
    'table' => 'sessions',
    'lottery' => [2, 100],
    'cookie' => Str::slug((string) env('APP_NAME', 'env-manager')).'-session',
    'path' => '/',
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
];
