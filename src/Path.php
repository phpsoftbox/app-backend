<?php

declare(strict_types=1);

namespace App;

use PhpSoftBox\Config\Path\AbstractPath;

final class Path extends AbstractPath
{
    public function configPath(string ...$path): string
    {
        return $this->path('config', ...$path);
    }

    public function routesPath(string $path = ''): string
    {
        return $this->path($this->configPath('routes', $path));
    }

    public function localPath(string $path = ''): string
    {
        return $this->path($path);
    }

    public function cachePath(string $path = ''): string
    {
        return $this->path('cache', $path);
    }

    public function cacheRoutes(string $path = ''): string
    {
        return $this->path('cache', 'routes', $path);
    }

    public function migrationBasePath(string $path = ''): string
    {
        return $this->path($path);
    }
}
