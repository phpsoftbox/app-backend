<?php

declare(strict_types=1);

use App\Path;
use App\Runtime\Environment;
use PhpSoftBox\Container\ContainerBuilder;
use PhpSoftBox\Container\Profiler\ContainerProfilerCollector;
use PhpSoftBox\Profiler\ProfilerInterface;

require_once __DIR__ . '/bootstrap.php';

$builder = new ContainerBuilder();

$builder->useAutowiring(true);
$builder->useAttributes(false);

$builder->addDefinitions(require __DIR__ . '/dependencies.php');

if (Environment::detect() === Environment::PROD) {
    $path = new Path(dirname(__DIR__));

    $builder->enableCompilation($path->cachePath('di'));
}

$container = $builder->build();

if ($container->has(ProfilerInterface::class)) {
    $container->setProfiler(
        $container->get(ProfilerInterface::class),
        $container->has(ContainerProfilerCollector::class) ? $container->get(ContainerProfilerCollector::class) : null,
    );
}

return $container;
