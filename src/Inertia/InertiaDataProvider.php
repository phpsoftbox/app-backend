<?php

declare(strict_types=1);

namespace App\Inertia;

use PhpSoftBox\Config\Config;
use PhpSoftBox\Inertia\Page\Breadcrumbs;
use PhpSoftBox\Inertia\Page\PageMeta;
use PhpSoftBox\Inertia\Page\Tabs;
use PhpSoftBox\Inertia\Share\InertiaBaseDataProvider;
use PhpSoftBox\Profiler\ProfilerInterface;
use PhpSoftBox\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class InertiaDataProvider extends InertiaBaseDataProvider
{
    public function __construct(
        ?SessionInterface $session = null,
        ?Breadcrumbs $breadcrumbs = null,
        ?PageMeta $meta = null,
        ?Tabs $tabs = null,
        private readonly ?ProfilerInterface $profiler = null,
        private readonly ?Config $config = null,
    ) {
        parent::__construct($session, $breadcrumbs, $meta, $tabs);
    }

    public function share(ServerRequestInterface $request): array
    {
        $shared = parent::share($request);

        if ($this->profiler === null || !$this->profiler->enabled()) {
            return $shared;
        }

        $config = (array) ($this->config?->get('profiler', []) ?? []);

        $shared['profiler'] = [
            'enabled'  => true,
            'trace_id' => $this->profiler->traceId(),
            'endpoint' => (string) ($config['endpoint'] ?? '/__profiler'),
        ];

        return $shared;
    }
}
