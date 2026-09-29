<?php

declare(strict_types=1);

return [
    'enabled'      => env('PROFILER_ENABLED', '0') === '1',
    'driver'       => env('PROFILER_DRIVER', 'file'),
    'storage_path' => env('PROFILER_STORAGE_PATH', 'local/profiler'),
    // Хранилище трасс ограничено: сверх лимита и старше max_age_seconds трассы удаляются.
    'max_traces'      => (int) env('PROFILER_MAX_TRACES', '500'),
    'max_age_seconds' => (int) env('PROFILER_MAX_AGE_SECONDS', '86400'),
    'endpoint'        => env('PROFILER_ENDPOINT', '/__profiler'),
    'collectors'      => [
        'container' => [
            'enabled'        => true,
            'trace_resolves' => env('PROFILER_CONTAINER_TRACE_RESOLVES', '0') === '1',
        ],
        'router' => [
            'enabled' => true,
        ],
        'database' => [
            'enabled' => true,
        ],
    ],
];
