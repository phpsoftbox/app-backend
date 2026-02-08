<?php

declare(strict_types=1);

use App\Http\Action\HealthAction;
use App\Http\Action\HomeAction;
use App\Http\Action\LoginAction;
use App\Http\Action\LogoutAction;
use PhpSoftBox\Router\RouteCollector;

return static function (RouteCollector $routes): void {
    $routes->get('/', HomeAction::class)->name('home');
    $routes->get('/health', HealthAction::class)->name('health');

    $routes->post('/auth/login', LoginAction::class)->name('auth.login');
    $routes->post('/auth/logout', LogoutAction::class)->middleware('auth')->name('auth.logout');
};
