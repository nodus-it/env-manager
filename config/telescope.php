<?php

use Laravel\Telescope\Http\Middleware\Authorize;
use Laravel\Telescope\Watchers;

return [
    'enabled' => (bool) env('APP_DEBUG', false),
    'path' => 'telescope',
    'driver' => 'database',
    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'sqlite'),
            'chunk' => 1000,
        ],
    ],
    'queue' => [],
    'middleware' => [
        'web',
        Authorize::class,
    ],
    'only_paths' => [
        // 'api/*'
    ],
    'ignore_paths' => [
        'livewire*',
        'nova-api*',
        'pulse*',
        '_boost*',
    ],
    'ignore_commands' => [
        //
    ],
    'watchers' => [
        Watchers\BatchWatcher::class => true,
        Watchers\CacheWatcher::class => [
            'enabled' => true,
            'hidden' => [],
            'ignore' => [],
        ],
        Watchers\ClientRequestWatcher::class => [
            'enabled' => true,
            'ignore_hosts' => [],
        ],
        Watchers\CommandWatcher::class => [
            'enabled' => true,
            'ignore' => [],
        ],
        Watchers\DumpWatcher::class => [
            'enabled' => true,
            'always' => false,
        ],
        Watchers\EventWatcher::class => [
            'enabled' => true,
            'ignore' => [],
        ],
        Watchers\ExceptionWatcher::class => true,
        Watchers\GateWatcher::class => [
            'enabled' => true,
            'ignore_abilities' => [],
            'ignore_packages' => true,
            'ignore_paths' => [],
        ],
        Watchers\JobWatcher::class => true,
        Watchers\LogWatcher::class => [
            'enabled' => true,
            'level' => 'error',
        ],
        Watchers\MailWatcher::class => true,
        Watchers\ModelWatcher::class => [
            'enabled' => true,
            'events' => ['eloquent.*'],
            'hydrations' => true,
        ],
        Watchers\NotificationWatcher::class => true,
        Watchers\QueryWatcher::class => [
            'enabled' => true,
            'ignore_packages' => true,
            'ignore_paths' => [],
            'slow' => 100,
        ],
        Watchers\RedisWatcher::class => true,
        Watchers\RequestWatcher::class => [
            'enabled' => true,
            'size_limit' => 64,
            'ignore_http_methods' => [],
            'ignore_status_codes' => [],
        ],
        Watchers\ScheduleWatcher::class => true,
        Watchers\ViewWatcher::class => true,
    ],
];
