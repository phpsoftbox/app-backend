<?php

declare(strict_types=1);

namespace App\Feature\Auth\Command\Login;

use PhpSoftBox\Auth\Contracts\UserInterface;
use PhpSoftBox\Auth\Guard\SessionGuard;
use PhpSoftBox\Auth\Provider\UserProviderInterface;

final readonly class LoginHandler
{
    public function __construct(
        private UserProviderInterface $users,
        private SessionGuard $guard,
    ) {
    }

    public function handle(LoginCommand $command): ?UserInterface
    {
        $credentials = [
            'email'    => $command->email,
            'password' => $command->password,
        ];

        $user = $this->users->retrieveByCredentials($credentials);
        if (!$user instanceof UserInterface) {
            return null;
        }

        if (!$this->users->validateCredentials($user, $credentials)) {
            return null;
        }

        $this->guard->login($user);

        return $user;
    }
}
