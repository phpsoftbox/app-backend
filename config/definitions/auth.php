<?php

declare(strict_types=1);

use App\Entity\User;
use PhpSoftBox\Auth\Guard\GuardInterface;
use PhpSoftBox\Auth\Guard\SessionGuard;
use PhpSoftBox\Auth\Manager\AuthManager;
use PhpSoftBox\Auth\Middleware\AuthMiddleware;
use PhpSoftBox\Auth\Middleware\GuardMiddleware;
use PhpSoftBox\Auth\Provider\ArrayUserProvider;
use PhpSoftBox\Auth\Provider\UserProviderInterface;
use PhpSoftBox\Config\Config;
use PhpSoftBox\Session\SessionInterface;
use Psr\Container\ContainerInterface;

use function PhpSoftBox\Container\factory;
use function PhpSoftBox\Container\get;

return [
    UserProviderInterface::class => factory(static function (ContainerInterface $container): UserProviderInterface {
        $config      = (array) $container->get(Config::class)->get('auth', []);
        $users       = is_array($config['users'] ?? null) ? $config['users'] : [];
        $loginFields = is_array($config['login_fields'] ?? null) ? $config['login_fields'] : ['email'];
        $idField     = is_string($config['id_field'] ?? null) ? (string) $config['id_field'] : 'id';

        return new ArrayUserProvider(
            users: $users,
            loginFields: $loginFields,
            idField: $idField,
            identityResolver: static fn (array $row): User => new User(
                id: (int) ($row['id'] ?? 0),
                email: (string) ($row['email'] ?? ''),
                name: (string) ($row['name'] ?? ''),
            ),
        );
    }),

    SessionGuard::class => factory(static function (ContainerInterface $container): SessionGuard {
        $config = (array) $container->get(Config::class)->get('auth.session', []);

        $sessionKey     = is_string($config['key'] ?? null) ? (string) $config['key'] : 'auth.user_id';
        $sessionHashKey = is_string($config['hash_key'] ?? null) && $config['hash_key'] !== ''
            ? (string) $config['hash_key']
            : null;
        $userHashKey = is_string($config['user_hash_key'] ?? null)
            ? (string) $config['user_hash_key']
            : 'password_hash';

        return new SessionGuard(
            session: $container->get(SessionInterface::class),
            users: $container->get(UserProviderInterface::class),
            sessionKey: $sessionKey,
            sessionHashKey: $sessionHashKey,
            userHashKey: $userHashKey,
        );
    }),

    GuardInterface::class => get(SessionGuard::class),

    AuthManager::class => factory(static function (ContainerInterface $container): AuthManager {
        $config       = (array) $container->get(Config::class)->get('auth', []);
        $defaultGuard = is_string($config['default_guard'] ?? null) ? (string) $config['default_guard'] : 'web';

        return new AuthManager(
            guards: [
                'web' => static fn (): GuardInterface => $container->get(SessionGuard::class),
            ],
            defaultGuard: $defaultGuard,
            container: $container,
        );
    }),

    AuthMiddleware::class => factory(
        static fn (ContainerInterface $container): AuthMiddleware => new AuthMiddleware($container->get(AuthManager::class)),
    ),

    GuardMiddleware::class => factory(
        static fn (ContainerInterface $container): GuardMiddleware => new GuardMiddleware($container->get(AuthManager::class)),
    ),
];
