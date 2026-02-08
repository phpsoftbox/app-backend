<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Feature\Auth\Command\Login\LoginHandler;
use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\Auth\Manager\AuthManager;
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
