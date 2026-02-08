<?php

declare(strict_types=1);

use App\Http\Action\Admin\DashboardAction;
use PhpSoftBox\Router\RouteCollector;

return static function (RouteCollector $routes): void {
    $routes
        ->group(static function (RouteCollector $routes): void {
            $routes->get('/', DashboardAction::class)->name('dashboard');
        })
        ->prefix('/admin')
        ->namePrefix('admin')
        ->apply();
};
