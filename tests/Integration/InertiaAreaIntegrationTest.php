<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Inertia\AdminSharedDataProvider;
use App\Inertia\WebSharedDataProvider;
use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\Http\Message\ServerRequest;
use PhpSoftBox\Inertia\Area\AreaSharedDataProviderRegistry;
use PhpSoftBox\Inertia\InertiaConfig;
use PhpSoftBox\Router\RouteCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration')]
#[CoversClass(InertiaConfig::class)]
#[CoversClass(AreaSharedDataProviderRegistry::class)]
#[CoversClass(WebSharedDataProvider::class)]
#[CoversClass(AdminSharedDataProvider::class)]
#[CoversClass(RouteCollector::class)]
#[CoversMethod(InertiaConfig::class, 'areas')]
#[CoversMethod(InertiaConfig::class, 'defaultArea')]
#[CoversMethod(InertiaConfig::class, 'ssrEnabled')]
#[CoversMethod(AreaSharedDataProviderRegistry::class, 'share')]
#[CoversMethod(WebSharedDataProvider::class, 'area')]
#[CoversMethod(WebSharedDataProvider::class, 'share')]
#[CoversMethod(AdminSharedDataProvider::class, 'area')]
#[CoversMethod(AdminSharedDataProvider::class, 'share')]
#[CoversMethod(RouteCollector::class, 'getNamedRoutes')]
final class InertiaAreaIntegrationTest extends IntegrationTestCase
{
    /**
     * Проверяем, что AppBackend регистрирует web/admin Inertia areas с разными SSR-настройками.
     *
     * @see InertiaConfig::areas()
     * @see InertiaConfig::defaultArea()
     * @see InertiaConfig::ssrEnabled()
     */
    #[Test]
    public function testInertiaConfigDefinesWebAndAdminAreas(): void
    {
        $config = self::container()->get(InertiaConfig::class);
        $areas  = $config->areas();

        $this->assertTrue($config->ssrEnabled());
        $this->assertSame('web', $config->defaultArea());
        $this->assertArrayHasKey('web', $areas);
        $this->assertArrayHasKey('admin', $areas);
        $this->assertTrue($areas['web']->ssr());
        $this->assertFalse($areas['admin']->ssr());
        $this->assertSame('web', $areas['web']->shared()['app']['area']);
        $this->assertSame('admin', $areas['admin']->shared()['app']['area']);
    }

    /**
     * Проверяем, что area shared providers дополняют props для web и admin областей.
     *
     * @see AreaSharedDataProviderRegistry::share()
     * @see WebSharedDataProvider::share()
     * @see AdminSharedDataProvider::share()
     */
    #[Test]
    public function testAreaSharedDataProvidersExposeNavigation(): void
    {
        $registry = self::container()->get(AreaSharedDataProviderRegistry::class);

        $web   = $registry->share('web', new ServerRequest('GET', 'https://example.test/'));
        $admin = $registry->share('admin', new ServerRequest('GET', 'https://example.test/admin'));

        $this->assertSame('/', $web['web']['navigation'][0]['href']);
        $this->assertSame('/health', $web['web']['navigation'][1]['href']);
        $this->assertSame('/admin', $admin['admin']['navigation'][0]['href']);
    }

    /**
     * Проверяем, что AppBackend загружает web и admin маршруты из отдельных файлов.
     *
     * @see RouteCollector::getNamedRoutes()
     */
    #[Test]
    public function testRouteCollectorLoadsWebAndAdminRoutes(): void
    {
        $routes = self::container()->get(RouteCollector::class);
        $named  = $routes->getNamedRoutes();

        $this->assertArrayHasKey('home', $named);
        $this->assertArrayHasKey('admin.dashboard', $named);
        $this->assertSame('/', $named['home']->path);
        $this->assertSame('/admin/', $named['admin.dashboard']->path);
    }
}
