<?php

declare(strict_types=1);

return [
    'connections' => [
        'default' => 'main',
        'main' => [
            'dsn' => env('APP_DB_MAIN_DSN', 'mariadb://app:app@mariadb:3306/app'),
            'readonly' => false,
        ],
        'search' => [
            'dsn' => env('APP_DB_SEARCH_DSN', 'postgres://app:app@postgres:5432/app'),
            'readonly' => false,
        ],
    ],
    'migrations' => [
        'basePath' => env('APP_DB_MIGRATIONS_BASE', 'database/migrations'),
        'paths' => [
            // 'main' => 'database/migrations/main',
        ],
    ],
];
