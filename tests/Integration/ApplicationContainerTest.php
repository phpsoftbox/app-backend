<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Feature\Auth\Command\Login\LoginHandler;
use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\Auth\Authorization\PermissionCheckerInterface;
use PhpSoftBox\Auth\Manager\AuthManager;
use PhpSoftBox\Auth\Remember\RememberRestoreMiddleware;
use PhpSoftBox\Orm\Contracts\ConnectionEntityManagerFactoryInterface;
use PhpSoftBox\Orm\Contracts\EntityManagerRegistryInterface;
use PhpSoftBox\Orm\Contracts\EntityRuntimeRegistryInterface;
use PhpSoftBox\Storage\Contracts\StorageInterface;
use PhpSoftBox\TestUtils\Database\DatabaseReloader;
use PhpSoftBox\TestUtils\Database\DatabaseReloaderConfig;
use PhpSoftBox\TestUtils\Fixture\FixtureRunner;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class ApplicationContainerTest extends IntegrationTestCase
{
    public function testContainerResolvesApplicationServices(): void
    {
        $container = self::container();

        $this->assertInstanceOf(AuthManager::class, $container->get(AuthManager::class));
        $this->assertInstanceOf(LoginHandler::class, $container->get(LoginHandler::class));
        $this->assertInstanceOf(PermissionCheckerInterface::class, $container->get(PermissionCheckerInterface::class));
        $this->assertInstanceOf(RememberRestoreMiddleware::class, $container->get(RememberRestoreMiddleware::class));
        $this->assertInstanceOf(DatabaseReloader::class, $container->get(DatabaseReloader::class));
        $this->assertInstanceOf(FixtureRunner::class, $container->get(FixtureRunner::class));
        $this->assertInstanceOf(StorageInterface::class, $container->get(StorageInterface::class));
    }

    public function testDatabaseReloaderUsesTestEnvironmentConfig(): void
    {
        $config = self::container()->get(DatabaseReloaderConfig::class);

        $this->assertNotEmpty($config->connections);
        $this->assertSame('default', $config->connections[0]->name);
        $this->assertSame('dump', $config->mode);
    }

    public function testOrmManagersUseSharedRuntimeRegistry(): void
    {
        $container       = self::container();
        $runtimeRegistry = $container->get(EntityRuntimeRegistryInterface::class);
        $managerRegistry = $container->get(EntityManagerRegistryInterface::class);
        $managerFactory  = $container->get(ConnectionEntityManagerFactoryInterface::class);

        $this->assertSame($runtimeRegistry, $managerRegistry->runtimeRegistry());
        $this->assertSame($runtimeRegistry, $managerFactory->runtimeRegistry());
    }

    public function testStorageUsesLocalStorageDirectory(): void
    {
        $storage = self::container()->get(StorageInterface::class);
        $path    = 'tests/storage-check.txt';

        try {
            $storage->put($path, 'ok');

            $this->assertTrue($storage->exists($path));
            $this->assertSame('ok', $storage->read($path));
            $this->assertSame('/storage/tests/storage-check.txt', $storage->url($path));
        } finally {
            $storage->delete($path);
        }
    }
}
