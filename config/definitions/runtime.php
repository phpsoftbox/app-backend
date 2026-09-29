<?php

declare(strict_types=1);

use PhpSoftBox\Auth\Authorization\PermissionCheckerInterface;
use PhpSoftBox\Container\Container;
use PhpSoftBox\Container\Reset\ServicesResetter;
use PhpSoftBox\Cookie\CookieQueue;
use PhpSoftBox\Database\Connection\ConnectionManagerInterface;
use PhpSoftBox\Inertia\Inertia;
use PhpSoftBox\Inertia\Page\Breadcrumbs;
use PhpSoftBox\Inertia\Page\PageMeta;
use PhpSoftBox\Inertia\Page\Tabs;
use PhpSoftBox\Orm\Contracts\EntityManagerInterface;
use Psr\Container\ContainerInterface;

use function PhpSoftBox\Container\factory;

return [
    // Сброс состояния между задачами долгоживущего процесса (воркер очереди, HTTP-воркер): вызывайте
    // $container->get(ServicesResetter::class)->reset() после каждой задачи/запроса. Сбрасываются только уже созданные
    // сервисы: реализующие ResetInterface и перечисленные здесь.
    ServicesResetter::class => factory(static fn (ContainerInterface $container): ServicesResetter => new ServicesResetter(
        $container->get(Container::class),
        [
            ConnectionManagerInterface::class => 'clearWarmup',
            EntityManagerInterface::class     => 'clear',
            PermissionCheckerInterface::class => 'reset',
            CookieQueue::class                => 'flush',
            Inertia::class                    => 'reset',
            Breadcrumbs::class                => 'clear',
            PageMeta::class                   => 'clear',
            Tabs::class                       => 'clear',
        ],
    )),
];
