<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feature\Auth;

use App\Feature\Auth\Command\Login\LoginCommand;
use App\Feature\Auth\Command\Login\LoginHandler;
use App\Feature\Auth\Command\Logout\LogoutCommand;
use App\Feature\Auth\Command\Logout\LogoutHandler;
use PhpSoftBox\Auth\Contracts\UserInterface;
use PhpSoftBox\Auth\Guard\SessionGuard;
use PhpSoftBox\Auth\Provider\ArrayUserProvider;
use PhpSoftBox\Session\ArraySessionStore;
use PhpSoftBox\Session\Session;
use PHPUnit\Framework\TestCase;

use function password_hash;

use const PASSWORD_DEFAULT;

final class LoginHandlerTest extends TestCase
{
    public function testLoginStoresUserInSession(): void
    {
        [$handler, $session] = $this->makeLoginHandler();

        $user = $handler->handle(new LoginCommand('demo@example.test', 'password'));

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame(1, $session->get('auth.user_id'));
    }

    public function testLoginRejectsInvalidCredentials(): void
    {
        [$handler, $session] = $this->makeLoginHandler();

        $user = $handler->handle(new LoginCommand('demo@example.test', 'wrong-password'));

        $this->assertNull($user);
        $this->assertNull($session->get('auth.user_id'));
    }

    public function testLogoutClearsSession(): void
    {
        [$handler, $session, $guard] = $this->makeLoginHandler();
        $this->assertInstanceOf(UserInterface::class, $handler->handle(new LoginCommand('demo@example.test', 'password')));

        new LogoutHandler($guard)->handle(new LogoutCommand());

        $this->assertNull($session->get('auth.user_id'));
    }

    /**
     * @return array{0:LoginHandler,1:Session,2:SessionGuard}
     */
    private function makeLoginHandler(): array
    {
        $users = new ArrayUserProvider([
            [
                'id'            => 1,
                'email'         => 'demo@example.test',
                'name'          => 'Demo User',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            ],
        ]);

        $session = new Session(new ArraySessionStore());

        $session->start();

        $guard = new SessionGuard($session, $users);

        return [
            new LoginHandler($users, $guard),
            $session,
            $guard,
        ];
    }
}
