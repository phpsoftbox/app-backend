<?php

declare(strict_types=1);

use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAttributes(false);

$builder->addDefinitions(require __DIR__ . '/dependencies.php');

if (env('APP_ENV', 'dev') === 'prod') {
    $builder->enableCompilation($baseDir = dirname(__DIR__, 2) . '/local/cache/di');
}

return $builder->build();
