<?php

declare(strict_types=1);

namespace App\Http\Action;

use PhpSoftBox\Inertia\Inertia;
use Psr\Http\Message\ResponseInterface;

final readonly class HomeAction
{
    public function __construct(
        private Inertia $inertia,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        return $this->inertia->render('Web/Home', [
            'title' => 'PhpSoftBox App',
            'meta'  => [
                'title'       => 'PhpSoftBox App',
                'description' => 'Backend application skeleton for PhpSoftBox.',
            ],
        ]);
    }
}
