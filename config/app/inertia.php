<?php

declare(strict_types=1);

return [
    'root_view' => 'resources/views/app.php',
    'root_id' => 'app',
    'version' => env('INERTIA_VERSION'),
    'ssr' => [
        'enabled' => env('INERTIA_SSR', false),
    ],
    'shared' => [
        'app' => [
            'env' => env('APP_ENV', 'dev'),
        ],
    ],
];
