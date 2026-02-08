<?php

declare(strict_types=1);

namespace App\Http\Action\Admin;

use PhpSoftBox\Inertia\Inertia;
use Psr\Http\Message\ResponseInterface;

final readonly class DashboardAction
{
    public function __construct(
        private Inertia $inertia,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        return $this->inertia->render('Admin/Dashboard', [
            'title' => 'Admin Dashboard',
            'meta'  => [
                'title'       => 'Admin Dashboard',
                'description' => 'Administration area for PhpSoftBox App.',
            ],
        ]);
    }
}
