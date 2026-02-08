<?php

declare(strict_types=1);

use PhpSoftBox\Application\Application;
use PhpSoftBox\Application\Middleware\BodyParserMiddleware;
use PhpSoftBox\Application\Middleware\CorsMiddleware;
use PhpSoftBox\Application\Middleware\MethodOverrideMiddleware;
use PhpSoftBox\Auth\Middleware\AuthMiddleware;
use PhpSoftBox\Cookie\CookieMiddleware;
use PhpSoftBox\Inertia\Middleware\InertiaMiddleware;
use PhpSoftBox\Inertia\Middleware\InertiaShareMiddleware;
use PhpSoftBox\Session\CsrfMiddleware;
use PhpSoftBox\Session\SessionMiddleware;

return static function (Application $app): void {
    $app->alias('auth', AuthMiddleware::class);
    $app->alias('csrf', CsrfMiddleware::class);

    $app->middlewareGroup('web', [
        CookieMiddleware::class,
        SessionMiddleware::class,
        CsrfMiddleware::class,
    ]);

    $app->middlewareGroup('api', [
        CorsMiddleware::class,
        BodyParserMiddleware::class,
    ]);

    $app->add(MethodOverrideMiddleware::class);
    $app->add(BodyParserMiddleware::class);
    $app->add(CorsMiddleware::class);
    $app->add(CookieMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(CsrfMiddleware::class);
    $app->add(InertiaShareMiddleware::class);
    $app->add(InertiaMiddleware::class);
};
