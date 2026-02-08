<?php

declare(strict_types=1);

use PhpSoftBox\Inertia\Inertia;
use PhpSoftBox\Request\Request;
use PhpSoftBox\Router\RouteCollector;

return static function (RouteCollector $routes): void {
    $routes->get('/', static function (Request $request, Inertia $inertia) {
        return $inertia->render('Home', [
            'title' => 'PhpSoftBox',
        ]);
    });
};
