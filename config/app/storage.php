<?php

declare(strict_types=1);

return [
    'default' => env('APP_STORAGE_DISK', 'local'),
    'disks'   => [
        'local' => [
            'driver'  => 'local',
            'baseUrl' => env('APP_STORAGE_URL', '/storage'),
        ],
    ],
];
