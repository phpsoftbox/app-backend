<?php

declare(strict_types=1);

use App\Http\Action\HealthAction;
use App\Http\Action\HomeAction;
use App\Http\Action\LoginAction;
use App\Http\Action\LogoutAction;
use App\Runtime\Environment;
use PhpSoftBox\Profiler\Http\ProfilerReportHandler;
use PhpSoftBox\Router\RouteCollector;

return static function (RouteCollector $routes): void {
    $routes->get('/', HomeAction::class)->name('home');
    $routes->get('/health', HealthAction::class)->name('health');

    $routes->post('/auth/login', LoginAction::class)->name('auth.login');
    $routes->post('/auth/logout', LogoutAction::class)->middleware('auth')->name('auth.logout');

    // Эндпоинты профайлера отдают трассы запросов (SQL, параметры) без авторизации — только в dev и при включённом
    // профайлере; путь — PROFILER_ENDPOINT, как у клиента профайлера.
    if (Environment::detect() === Environment::DEV && env('PROFILER_ENABLED', '0') === '1') {
        $endpoint = rtrim((string) env('PROFILER_ENDPOINT', '/__profiler'), '/');

        $routes->get($endpoint . '/api/traces', ProfilerReportHandler::class)->name('profiler.traces');
        $routes->get($endpoint . '/api/traces/{trace}', ProfilerReportHandler::class)->name('profiler.trace');
    }
};
