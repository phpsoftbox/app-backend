<?php

declare(strict_types=1);

namespace App\Inertia;

use PhpSoftBox\Inertia\Area\AreaSharedDataProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AdminSharedDataProvider implements AreaSharedDataProviderInterface
{
    public function area(): string
    {
        return 'admin';
    }

    public function share(ServerRequestInterface $request): array
    {
        return [
            'admin' => [
                'navigation' => [
                    [
                        'label' => 'Dashboard',
                        'href'  => '/admin',
                    ],
                ],
            ],
        ];
    }
}
