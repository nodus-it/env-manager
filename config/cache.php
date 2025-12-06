<?php

use Illuminate\Support\Str;

return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
    ],
    'prefix' => 'env-manager-cache',

];
