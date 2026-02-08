<?php

declare(strict_types=1);

namespace App\Inertia;

use PhpSoftBox\Inertia\Share\InertiaBaseDataProvider;
use Psr\Http\Message\ServerRequestInterface;

class InertiaDataProvider extends InertiaBaseDataProvider
{
    public function share(ServerRequestInterface $request): array
    {
        return parent::share($request);
    }
}
