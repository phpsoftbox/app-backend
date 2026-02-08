<?php

declare(strict_types=1);

use PhpSoftBox\Config\Config;
use PhpSoftBox\Config\Path\PathInterface;
use PhpSoftBox\Http\Message\ResponseFactory;
use PhpSoftBox\Http\Message\StreamFactory;
use PhpSoftBox\Inertia\Inertia;
use PhpSoftBox\Inertia\InertiaConfig;
use PhpSoftBox\Inertia\Middleware\InertiaMiddleware;
use PhpSoftBox\Inertia\View\PhpViewRenderer;
use PhpSoftBox\Inertia\View\ViewRendererInterface;
use PhpSoftBox\Vite\Vite;
use Psr\Container\ContainerInterface;

use function DI\factory;

return [
    InertiaConfig::class => factory(static function (ContainerInterface $container): InertiaConfig {
        $config = (array) $container->get(Config::class)->get('inertia', []);
        $path = $container->get(PathInterface::class);

        $rootView = is_string($config['root_view'] ?? null) ? (string) $config['root_view'] : 'resources/views/app.php';
        $rootId = is_string($config['root_id'] ?? null) ? (string) $config['root_id'] : 'app';
        $shared = is_array($config['shared'] ?? null) ? $config['shared'] : [];

        $version = $config['version'] ?? null;
        if (!is_string($version) || $version === '') {
            $version = null;
        }

        if ($version === null && $container->has(Vite::class)) {
            $version = $container->get(Vite::class)->version();
        }

        return new InertiaConfig(
            rootView: $path->createPath($rootView),
            rootId: $rootId,
            version: $version,
            shared: $shared,
        );
    }),

    ViewRendererInterface::class => factory(static function (ContainerInterface $container): ViewRendererInterface {
        $config = $container->get(InertiaConfig::class);
        $shared = [];

        if ($container->has(Vite::class)) {
            $shared['vite'] = $container->get(Vite::class);
        }

        return new PhpViewRenderer($config->rootView(), $config->rootId(), $shared);
    }),

    Inertia::class => factory(static function (ContainerInterface $container): Inertia {
        return new Inertia(
            $container->get(InertiaConfig::class),
            $container->get(ViewRendererInterface::class),
            $container->get(ResponseFactory::class),
            $container->get(StreamFactory::class),
        );
    }),

    InertiaMiddleware::class => factory(static function (ContainerInterface $container): InertiaMiddleware {
        return new InertiaMiddleware(
            $container->get(InertiaConfig::class),
            $container->get(ResponseFactory::class),
        );
    }),
];
