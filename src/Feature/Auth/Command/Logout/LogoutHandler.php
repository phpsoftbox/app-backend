<?php

declare(strict_types=1);

namespace App\Feature\Auth\Command\Logout;

use PhpSoftBox\Auth\Guard\SessionGuard;

final readonly class LogoutHandler
{
    public function __construct(
        private SessionGuard $guard,
    ) {
    }

    public function handle(LogoutCommand $command): void
    {
        $this->guard->logout();
    }
}
