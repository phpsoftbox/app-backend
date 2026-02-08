<?php

declare(strict_types=1);

namespace App\Inertia;

use PhpSoftBox\Inertia\Area\AreaSharedDataProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class WebSharedDataProvider implements AreaSharedDataProviderInterface
{
    public function area(): string
    {
        return 'web';
    }

    public function share(ServerRequestInterface $request): array
    {
        return [
            'web' => [
                'navigation' => [
                    [
                        'label' => 'Home',
                        'href'  => '/',
                    ],
                    [
                        'label' => 'Health',
                        'href'  => '/health',
                    ],
                ],
            ],
        ];
    }
}
