<?php

declare(strict_types=1);

namespace App\Tests\Support;

use PhpSoftBox\TestUtils\Database\DatabaseReloader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function env;

abstract class IntegrationTestCase extends TestCase
{
    private static ?ContainerInterface $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ((string) env('TEST_DB_RELOAD', '0') === '1') {
            self::container()->get(DatabaseReloader::class)->reloadAll();
        }
    }

    protected static function container(): ContainerInterface
    {
        if (self::$container === null) {
            self::$container = require __DIR__ . '/../../config/container.php';
        }

        return self::$container;
    }
}
