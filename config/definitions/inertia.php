<?php

declare(strict_types=1);

use PhpSoftBox\Config\Config;
use PhpSoftBox\Config\Path\PathInterface;
use PhpSoftBox\Http\Message\ResponseFactory;
use PhpSoftBox\Http\Message\StreamFactory;
use PhpSoftBox\Inertia\Inertia;
use PhpSoftBox\Inertia\InertiaConfig;
use PhpSoftBox\Inertia\Middleware\InertiaMiddleware;
use PhpSoftBox\Inertia\Middleware\InertiaShareMiddleware;
use PhpSoftBox\Inertia\Page\Breadcrumbs;
use PhpSoftBox\Inertia\Page\PageMeta;
use PhpSoftBox\Inertia\Ssr\SsrRendererInterface;
use PhpSoftBox\Inertia\Share\InertiaBaseDataProvider;
use PhpSoftBox\Inertia\Share\SharedDataProviderInterface;
use PhpSoftBox\Inertia\View\PhpViewRenderer;
use PhpSoftBox\Inertia\View\ViewRendererInterface;
use PhpSoftBox\Session\SessionInterface;
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

        $ssrEnabled = (bool) (($config['ssr']['enabled'] ?? false));

        return new InertiaConfig(
            rootView: $path->createPath($rootView),
            rootId: $rootId,
            version: $version,
            shared: $shared,
            ssrEnabled: $ssrEnabled,
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
            $container->has(SsrRendererInterface::class) ? $container->get(SsrRendererInterface::class) : null,
        );
    }),

    InertiaMiddleware::class => factory(static function (ContainerInterface $container): InertiaMiddleware {
        return new InertiaMiddleware(
            $container->get(InertiaConfig::class),
            $container->get(ResponseFactory::class),
        );
    }),

    Breadcrumbs::class => factory(static fn (): Breadcrumbs => new Breadcrumbs()),
    PageMeta::class => factory(static fn (): PageMeta => new PageMeta()),

    SharedDataProviderInterface::class => factory(static function (ContainerInterface $container): SharedDataProviderInterface {
        $session = $container->has(SessionInterface::class) ? $container->get(SessionInterface::class) : null;

        return new InertiaBaseDataProvider(
            $session,
            $container->get(Breadcrumbs::class),
            $container->get(PageMeta::class),
        );
    }),

    InertiaShareMiddleware::class => factory(static function (ContainerInterface $container): InertiaShareMiddleware {
        return new InertiaShareMiddleware(
            $container->get(Inertia::class),
            $container->get(SharedDataProviderInterface::class),
        );
    }),
];
