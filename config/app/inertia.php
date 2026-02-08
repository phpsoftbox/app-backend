<?php

declare(strict_types=1);

return [
    'inertia' => [
        'root_view' => 'resources/views/app.php',
        'root_id' => 'app',
        'version' => env('INERTIA_VERSION'),
        'shared' => [
            'app' => [
                'env' => env('APP_ENV', 'dev'),
            ],
        ],
    ],
];
