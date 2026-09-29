<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\Auth\Authorization\PermissionCheckerInterface;
use PhpSoftBox\Container\Reset\ServicesResetter;
use PhpSoftBox\Cookie\CookieQueue;
use PhpSoftBox\Cookie\SetCookie;
use PhpSoftBox\Database\Connection\ConnectionManagerInterface;
use PhpSoftBox\Inertia\Inertia;
use PhpSoftBox\Inertia\Page\Breadcrumbs;
use PhpSoftBox\Inertia\Page\PageMeta;
use PhpSoftBox\Inertia\Page\Tabs;
use PhpSoftBox\Orm\Contracts\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ServicesResetterIntegrationTest extends IntegrationTestCase
{
    /**
     * Проверим, что хук сброса скелета собирается и вызывает методы сброса у созданных сервисов фреймворка: карта не
     * ссылается на несуществующие методы.
     *
     * @see ServicesResetter::reset()
     */
    #[Test]
    public function resetsFrameworkServices(): void
    {
        $container = self::container();
        $container->get(ConnectionManagerInterface::class);
        $container->get(EntityManagerInterface::class);
        $container->get(PermissionCheckerInterface::class);
        $container->get(Inertia::class);
        $container->get(Breadcrumbs::class);
        $container->get(PageMeta::class);
        $container->get(Tabs::class);
        $container->get(CookieQueue::class)->queue(SetCookie::create('stale', 'value'));

        $container->get(ServicesResetter::class)->reset();

        self::assertSame([], $container->get(CookieQueue::class)->flush());
    }
}
