<?php

declare(strict_types=1);

use PhpSoftBox\Config\Config;
use PhpSoftBox\Config\Path\PathInterface;
use PhpSoftBox\Container\Profiler\ContainerProfilerCollector;
use PhpSoftBox\Container\Profiler\ContainerProfilerExtension;
use PhpSoftBox\Database\Profiler\DatabaseProfilerCollector;
use PhpSoftBox\Database\Profiler\DatabaseProfilerExtension;
use PhpSoftBox\Profiler\Http\ProfilerReportHandler;
use PhpSoftBox\Profiler\Middleware\ProfilerMiddleware;
use PhpSoftBox\Profiler\Profiler;
use PhpSoftBox\Profiler\ProfilerInterface;
use PhpSoftBox\Profiler\ProfilerRegistry;
use PhpSoftBox\Profiler\ProfilerRegistryInterface;
use PhpSoftBox\Profiler\ProfilerStoreInterface;
use PhpSoftBox\Profiler\Store\FileProfilerStore;
use PhpSoftBox\Profiler\Store\InMemoryProfilerStore;
use PhpSoftBox\Router\Profiler\RouterProfilerCollector;
use PhpSoftBox\Router\Profiler\RouterProfilerExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function PhpSoftBox\Container\factory;
use function PhpSoftBox\Container\get;

return [
    ContainerProfilerCollector::class => factory(static function (ContainerInterface $container): ContainerProfilerCollector {
        $config = (array) $container->get(Config::class)->get('profiler.collectors.container', []);

        return new ContainerProfilerCollector(
            traceResolves: (bool) ($config['trace_resolves'] ?? false),
        );
    }),

    RouterProfilerCollector::class => factory(static fn (): RouterProfilerCollector => new RouterProfilerCollector()),

    DatabaseProfilerCollector::class => factory(static fn (): DatabaseProfilerCollector => new DatabaseProfilerCollector()),

    ProfilerRegistryInterface::class => factory(static function (ContainerInterface $container): ProfilerRegistryInterface {
        $config   = (array) $container->get(Config::class)->get('profiler.collectors', []);
        $registry = new ProfilerRegistry();

        if ((bool) ($config['container']['enabled'] ?? true)) {
            new ContainerProfilerExtension($container->get(ContainerProfilerCollector::class))->register($registry);
        }

        if ((bool) ($config['router']['enabled'] ?? true)) {
            new RouterProfilerExtension($container->get(RouterProfilerCollector::class))->register($registry);
        }

        if ((bool) ($config['database']['enabled'] ?? true)) {
            new DatabaseProfilerExtension($container->get(DatabaseProfilerCollector::class))->register($registry);
        }

        return $registry;
    }),

    ProfilerRegistry::class => get(ProfilerRegistryInterface::class),

    ProfilerStoreInterface::class => factory(static function (ContainerInterface $container): ProfilerStoreInterface {
        $config = (array) $container->get(Config::class)->get('profiler', []);
        $driver = (string) ($config['driver'] ?? 'file');

        if ($driver === 'memory') {
            return new InMemoryProfilerStore();
        }

        $path = $container->get(PathInterface::class)->createPath((string) ($config['storage_path'] ?? 'local/profiler'));

        return new FileProfilerStore($path);
    }),

    ProfilerInterface::class => factory(static function (ContainerInterface $container): ProfilerInterface {
        $config = (array) $container->get(Config::class)->get('profiler', []);

        return new Profiler(
            enabled: (bool) ($config['enabled'] ?? false),
            store: $container->get(ProfilerStoreInterface::class),
            registry: $container->get(ProfilerRegistryInterface::class),
        );
    }),

    Profiler::class => get(ProfilerInterface::class),

    ProfilerMiddleware::class => factory(static function (ContainerInterface $container): ProfilerMiddleware {
        return new ProfilerMiddleware($container->get(ProfilerInterface::class));
    }),

    ProfilerReportHandler::class => factory(static function (ContainerInterface $container): ProfilerReportHandler {
        return new ProfilerReportHandler(
            $container->get(ProfilerInterface::class),
            $container->get(ProfilerStoreInterface::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
        );
    }),
];
