<?php

declare(strict_types=1);

return [
    'dev_server' => env('VITE_DEV_SERVER', 'https://vite.domain.local'),
    'manifest' => 'public/build/manifest.json',
    'hot_file' => 'public/hot',
    'build_base' => '/build',
    'entrypoints' => [
        'resources/js/app.tsx',
    ],
];
