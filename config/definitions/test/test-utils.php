<?php

declare(strict_types=1);

use App\Path;
use PhpSoftBox\Config\Config;
use PhpSoftBox\TestUtils\Database\DatabaseReloader;
use PhpSoftBox\TestUtils\Database\DatabaseReloaderConfig;
use PhpSoftBox\TestUtils\Fixture\FixtureRunner;
use Psr\Container\ContainerInterface;

use function PhpSoftBox\Container\factory;

return [
    DatabaseReloaderConfig::class => factory(static function (ContainerInterface $container): DatabaseReloaderConfig {
        $config   = $container->get(Config::class);
        $database = (array) $config->get('database', []);
        $testing  = (array) $config->get('database.testing', []);

        $rawConnections = is_string($testing['connections'] ?? null) ? (string) $testing['connections'] : 'default';
        $connections    = explode(',', $rawConnections)
                |> (static fn ($x) => array_map(static fn (string $name): string => trim($name), $x, ))
                |> (static fn ($x) => array_filter($x, static fn (string $name): bool => $name !== ''))
                |> array_values(...);

        $path = $container->get(Path::class);

        return DatabaseReloaderConfig::fromDatabaseConfig(
            databaseConfig: $database,
            connectionNames: $connections === [] ? ['default'] : $connections,
            testSuffix: is_string($testing['suffix'] ?? null) ? (string) $testing['suffix'] : '_autotests',
            dumpDirectory: $path->cachePath('test-dumps'),
            keepDumpFiles: (bool) ($testing['keep_dumps'] ?? false),
            mode: is_string($testing['mode'] ?? null) ? (string) $testing['mode'] : 'dump',
        );
    }),

    DatabaseReloader::class => factory(
        static fn (ContainerInterface $container): DatabaseReloader => new DatabaseReloader(
            $container->get(DatabaseReloaderConfig::class),
        ),
    ),

    FixtureRunner::class => factory(static fn (): FixtureRunner => new FixtureRunner()),
];
