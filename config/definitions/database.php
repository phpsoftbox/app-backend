<?php

declare(strict_types=1);

use App\Path;
use PhpSoftBox\Config\Config;
use PhpSoftBox\Database\Configurator\DatabaseFactory;
use PhpSoftBox\Database\Configurator\DatabaseFactoryInterface;
use PhpSoftBox\Database\Connection\ConnectionManager;
use PhpSoftBox\Database\Connection\ConnectionManagerInterface;
use PhpSoftBox\Database\Migrations\MigrationsConfig;
use Psr\Container\ContainerInterface;

use function DI\autowire;
use function DI\factory;
use function DI\get;

return [
    DatabaseFactoryInterface::class => factory(static function (ContainerInterface $container): DatabaseFactoryInterface {
        $config = $container->get(Config::class)->get('database', []);

        return new DatabaseFactory((array) $config);
    }),

    ConnectionManagerInterface::class => autowire(ConnectionManager::class)
        ->constructor(get(DatabaseFactoryInterface::class)),

    MigrationsConfig::class => factory(static function (ContainerInterface $container): MigrationsConfig {
        $config = (array) $container->get(Config::class)->get('database', []);
        $migrations = is_array($config['migrations'] ?? null) ? $config['migrations'] : [];
        $path = $container->get(Path::class);

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
