<?php

declare(strict_types=1);

use App\Path;
use PhpSoftBox\Config\Config;
use PhpSoftBox\Database\Configurator\DatabaseFactory;
use PhpSoftBox\Database\Configurator\DatabaseFactoryInterface;
use PhpSoftBox\Database\Connection\ConnectionManager;
use PhpSoftBox\Database\Connection\ConnectionManagerInterface;
use PhpSoftBox\Database\Migrations\MigrationsConfig;
use PhpSoftBox\Database\Profiler\DatabaseProfilerCollector;
use PhpSoftBox\Orm\ConnectionEntityManagerFactory;
use PhpSoftBox\Orm\ConnectionEntityManagerRegistry;
use PhpSoftBox\Orm\Contracts\ConnectionEntityManagerFactoryInterface;
use PhpSoftBox\Orm\Contracts\EntityAwareEntityManagerRegistryInterface;
use PhpSoftBox\Orm\Contracts\EntityManagerInterface;
use PhpSoftBox\Orm\Contracts\EntityManagerRegistryInterface;
use PhpSoftBox\Orm\Contracts\EntityRuntimeRegistryInterface;
use PhpSoftBox\Orm\UnitOfWork\EntityRuntimeRegistry;
use PhpSoftBox\Profiler\ProfilerInterface;
use Psr\Container\ContainerInterface;

use function PhpSoftBox\Container\autowire;
use function PhpSoftBox\Container\factory;
use function PhpSoftBox\Container\get;

return [
    DatabaseFactoryInterface::class => factory(static function (ContainerInterface $container): DatabaseFactoryInterface {
        $config = $container->get(Config::class)->get('database', []);

        return new DatabaseFactory(
            (array) $config,
            profiler: $container->get(ProfilerInterface::class),
            profilerCollector: $container->get(DatabaseProfilerCollector::class),
        );
    }),

    ConnectionManagerInterface::class => autowire(ConnectionManager::class)
        ->constructor(get(DatabaseFactoryInterface::class)),

    EntityRuntimeRegistryInterface::class => factory(
        static fn (): EntityRuntimeRegistryInterface => new EntityRuntimeRegistry(),
    ),

    EntityManagerRegistryInterface::class => factory(
        static fn (ContainerInterface $container): EntityManagerRegistryInterface => new ConnectionEntityManagerRegistry(
            connections: $container->get(ConnectionManagerInterface::class),
            runtimeRegistry: $container->get(EntityRuntimeRegistryInterface::class),
        ),
    ),

    EntityAwareEntityManagerRegistryInterface::class => get(EntityManagerRegistryInterface::class),

    ConnectionEntityManagerFactoryInterface::class => factory(
        static fn (ContainerInterface $container): ConnectionEntityManagerFactoryInterface => new ConnectionEntityManagerFactory(
            connections: $container->get(ConnectionManagerInterface::class),
            runtimeRegistry: $container->get(EntityRuntimeRegistryInterface::class),
        ),
    ),

    EntityManagerInterface::class => factory(
        static fn (ContainerInterface $container): EntityManagerInterface => $container
            ->get(EntityManagerRegistryInterface::class)
            ->default(),
    ),

    MigrationsConfig::class => factory(static function (ContainerInterface $container): MigrationsConfig {
        $config     = (array) $container->get(Config::class)->get('database', []);
        $migrations = is_array($config['migrations'] ?? null) ? $config['migrations'] : [];
        $path       = $container->get(Path::class);

        $defaultConnection = null;
        if (is_array($config['connections'] ?? null) && is_string($config['connections']['default'] ?? null)) {
            $defaultConnection = (string) $config['connections']['default'];
        }

        $basePath = is_string($migrations['basePath'] ?? null)
            ? (string) $migrations['basePath']
            : 'database/migrations';
        $basePath = rtrim($path->migrationBasePath($basePath), '/');

        $overrides = [];
        if (is_array($migrations['paths'] ?? null)) {
            foreach ($migrations['paths'] as $connection => $value) {
                if (!is_string($connection) || $connection === '') {
                    continue;
                }
                if (!is_string($value) || $value === '') {
                    continue;
                }

                $overrides[$connection] = $basePath . '/' . trim($value, '/');
            }
        }

        return new MigrationsConfig($basePath, $overrides, $defaultConnection);
    }),
];
