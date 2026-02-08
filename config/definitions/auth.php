<?php

declare(strict_types=1);

use App\Entity\User;
use PhpSoftBox\Auth\Authorization\ArrayRoleDefinitionProvider;
use PhpSoftBox\Auth\Authorization\DatabasePermissionChecker;
use PhpSoftBox\Auth\Authorization\PermissionCheckerInterface;
use PhpSoftBox\Auth\Authorization\RoleDefinitionProviderInterface;
use PhpSoftBox\Auth\Contracts\UserInterface;
use PhpSoftBox\Auth\Credentials\PasswordCredentialsValidator;
use PhpSoftBox\Auth\Guard\GuardInterface;
use PhpSoftBox\Auth\Guard\SessionGuard;
use PhpSoftBox\Auth\Manager\AuthManager;
use PhpSoftBox\Auth\Middleware\AreaAccessDeniedMode;
use PhpSoftBox\Auth\Middleware\AreaAccessMiddleware;
use PhpSoftBox\Auth\Middleware\AreaAccessRule;
use PhpSoftBox\Auth\Middleware\AuthMiddleware;
use PhpSoftBox\Auth\Middleware\GuardMiddleware;
use PhpSoftBox\Auth\Provider\DatabaseUserProvider;
use PhpSoftBox\Auth\Provider\InMemoryUserProvider;
use PhpSoftBox\Auth\Provider\UserProviderInterface;
use PhpSoftBox\Auth\Remember\DatabaseRememberTokenStore;
use PhpSoftBox\Auth\Remember\RememberCookieConfig;
use PhpSoftBox\Auth\Remember\RememberCookieManager;
use PhpSoftBox\Auth\Remember\RememberMismatchPolicy;
use PhpSoftBox\Auth\Remember\RememberRestoreMiddleware;
use PhpSoftBox\Auth\Remember\RememberTokenExtractor;
use PhpSoftBox\Config\Config;
use PhpSoftBox\Cookie\CookieQueue;
use PhpSoftBox\Cookie\SameSite;
use PhpSoftBox\Database\Connection\ConnectionManagerInterface;
use PhpSoftBox\DataCasting\Contracts\TypeCasterInterface;
use PhpSoftBox\DataCasting\DefaultTypeCasterFactory;
use PhpSoftBox\DataCasting\Options\TypeCastOptionsManager;
use PhpSoftBox\Orm\Metadata\AttributeMetadataProvider;
use PhpSoftBox\Orm\Metadata\MetadataProviderInterface;
use PhpSoftBox\Orm\Repository\AutoEntityMapper;
use PhpSoftBox\Session\Config\CookieSecurePolicy;
use PhpSoftBox\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function PhpSoftBox\Container\factory;
use function PhpSoftBox\Container\get;

return [
    MetadataProviderInterface::class => factory(static fn (): MetadataProviderInterface => new AttributeMetadataProvider()),

    TypeCastOptionsManager::class => factory(static fn (): TypeCastOptionsManager => new TypeCastOptionsManager()),

    TypeCasterInterface::class => factory(static fn (): TypeCasterInterface => new DefaultTypeCasterFactory()->create()),

    AutoEntityMapper::class => factory(static function (ContainerInterface $container): AutoEntityMapper {
        return new AutoEntityMapper(
            $container->get(MetadataProviderInterface::class),
            $container->get(TypeCasterInterface::class),
            $container->get(TypeCastOptionsManager::class),
        );
    }),

    UserProviderInterface::class => factory(static function (ContainerInterface $container): UserProviderInterface {
        $config = (array) $container->get(Config::class)->get('auth', []);

        $providerConfig = is_array($config['provider'] ?? null) ? $config['provider'] : $config;
        $driver         = strtolower((string) ($providerConfig['driver'] ?? $config['provider_driver'] ?? 'database'));
        $loginFields    = is_array($providerConfig['login_fields'] ?? null) ? $providerConfig['login_fields'] : ['email'];
        $idField        = is_string($providerConfig['id_field'] ?? null) ? (string) $providerConfig['id_field'] : 'id';

        if ($driver === 'array') {
            $rows  = is_array($providerConfig['users'] ?? null) ? $providerConfig['users'] : [];
            $users = [];

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $user = new User();

                $user->id           = (int) ($row['id'] ?? 0);
                $user->email        = (string) ($row['email'] ?? '');
                $user->name         = is_string($row['name'] ?? null) ? (string) $row['name'] : null;
                $user->passwordHash = is_string($row['password_hash'] ?? null) ? (string) $row['password_hash'] : null;
                $users[]            = $user;
            }

            return new InMemoryUserProvider(
                users: $users,
                credentialsMatcher: static fn (UserInterface $user, array $credentials): bool => $user instanceof User
                    && $user->email === ($credentials['email'] ?? null),
                validators: [new PasswordCredentialsValidator(
                    passwordHashResolver: static fn (UserInterface $user): ?string => $user instanceof User
                        ? $user->passwordHash
                        : null,
                )],
            );
        }

        $identity = is_string($providerConfig['identity'] ?? null)
            ? (string) $providerConfig['identity']
            : User::class;

        return new DatabaseUserProvider(
            connections: $container->get(ConnectionManagerInterface::class),
            identityClass: $identity,
            identityMapper: $container->get(AutoEntityMapper::class),
            connectionName: (string) ($providerConfig['connection'] ?? 'default'),
            table: (string) ($providerConfig['table'] ?? 'users'),
            loginFields: $loginFields,
            idField: $idField,
            validators: [new PasswordCredentialsValidator(
                passwordHashResolver: static fn (UserInterface $user): ?string => $user instanceof User
                    ? $user->passwordHash
                    : null,
            )],
        );
    }),

    SessionGuard::class => factory(static function (ContainerInterface $container): SessionGuard {
        $config = (array) $container->get(Config::class)->get('auth.session', []);

        $sessionKey     = is_string($config['key'] ?? null) ? (string) $config['key'] : 'auth.user_id';
        $sessionHashKey = is_string($config['hash_key'] ?? null) && $config['hash_key'] !== ''
            ? (string) $config['hash_key']
            : null;

        return new SessionGuard(
            session: $container->get(SessionInterface::class),
            users: $container->get(UserProviderInterface::class),
            sessionKey: $sessionKey,
            sessionHashKey: $sessionHashKey,
            userStampResolver: static fn (UserInterface $user): ?string => $user instanceof User
                ? $user->passwordHash
                : null,
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
            permissions: $container->get(PermissionCheckerInterface::class),
        );
    }),

    RoleDefinitionProviderInterface::class => factory(static function (ContainerInterface $container): RoleDefinitionProviderInterface {
        $config = (array) $container->get(Config::class)->get('auth.authorization', []);

        return new ArrayRoleDefinitionProvider(
            roles: is_array($config['roles'] ?? null) ? $config['roles'] : [],
            permissionModels: is_array($config['permission_models'] ?? null) ? $config['permission_models'] : [],
            permissions: is_array($config['permissions'] ?? null) ? $config['permissions'] : [],
        );
    }),

    PermissionCheckerInterface::class => factory(static function (ContainerInterface $container): PermissionCheckerInterface {
        $config = (array) $container->get(Config::class)->get('auth.authorization', []);

        return new DatabasePermissionChecker(
            connections: $container->get(ConnectionManagerInterface::class),
            connectionName: (string) ($config['connection'] ?? 'default'),
            permissionsTable: (string) ($config['permissions_table'] ?? 'permissions'),
            rolesTable: (string) ($config['roles_table'] ?? 'roles'),
            rolePermissionsTable: (string) ($config['role_permissions_table'] ?? 'role_permissions'),
            userPermissionsTable: (string) ($config['user_permissions_table'] ?? 'user_permissions'),
            userRolesTable: (string) ($config['user_roles_table'] ?? 'user_roles'),
            roleDefinitions: $container->get(RoleDefinitionProviderInterface::class),
        );
    }),

    RememberCookieConfig::class => factory(static function (ContainerInterface $container): RememberCookieConfig {
        $config = (array) $container->get(Config::class)->get('auth.remember', []);

        $sameSite      = SameSite::Lax;
        $sameSiteValue = $config['same_site'] ?? null;
        if (is_string($sameSiteValue)) {
            $sameSite = match (strtolower(trim($sameSiteValue))) {
                'strict' => SameSite::Strict,
                'none'   => SameSite::None,
                default  => SameSite::Lax,
            };
        }

        $secure = $config['secure'] ?? null;
        if (is_numeric($secure)) {
            $securePolicy = CookieSecurePolicy::fromBoolean((bool) $secure);
        } else {
            $securePolicy = CookieSecurePolicy::from($config['secure'] ?? 'always');
        }

        return new RememberCookieConfig(
            name: (string) ($config['cookie'] ?? 'remember_token'),
            path: (string) ($config['path'] ?? '/'),
            domain: is_string($config['domain'] ?? null) && $config['domain'] !== '' ? (string) $config['domain'] : null,
            secure: $securePolicy,
            httpOnly: (bool) ($config['http_only'] ?? true),
            sameSite: $sameSite,
            maxAge: is_numeric($config['max_age'] ?? null) ? (int) $config['max_age'] : null,
        );
    }),

    RememberTokenExtractor::class => factory(static function (ContainerInterface $container): RememberTokenExtractor {
        $config = (array) $container->get(Config::class)->get('auth.remember', []);

        return new RememberTokenExtractor((string) ($config['cookie'] ?? 'remember_token'));
    }),

    DatabaseRememberTokenStore::class => factory(static function (ContainerInterface $container): DatabaseRememberTokenStore {
        $config = (array) $container->get(Config::class)->get('auth.remember', []);

        return new DatabaseRememberTokenStore(
            connections: $container->get(ConnectionManagerInterface::class),
            connectionName: (string) ($config['connection'] ?? 'default'),
            table: (string) ($config['table'] ?? 'user_tokens'),
        );
    }),

    RememberCookieManager::class => factory(static function (ContainerInterface $container): RememberCookieManager {
        return new RememberCookieManager(
            $container->get(CookieQueue::class),
            $container->get(RememberCookieConfig::class),
        );
    }),

    RememberRestoreMiddleware::class => factory(static function (ContainerInterface $container): RememberRestoreMiddleware {
        $config = (array) $container->get(Config::class)->get('auth.remember', []);
        $policy = RememberMismatchPolicy::tryFrom((string) ($config['mismatch_policy'] ?? RememberMismatchPolicy::RevokeToken->value))
            ?? RememberMismatchPolicy::RevokeToken;

        return new RememberRestoreMiddleware(
            guard: $container->get(SessionGuard::class),
            users: $container->get(UserProviderInterface::class),
            tokens: $container->get(DatabaseRememberTokenStore::class),
            extractor: $container->get(RememberTokenExtractor::class),
            cookies: $container->get(RememberCookieManager::class),
            mismatchPolicy: $policy,
        );
    }),

    'area.access.admin' => factory(static function (ContainerInterface $container): AreaAccessMiddleware {
        $config = (array) $container->get(Config::class)->get('auth.areas.admin', []);

        $permission = is_string($config['permission'] ?? null) && trim($config['permission']) !== ''
            ? (string) $config['permission']
            : null;
        $redirectTo = is_string($config['redirect_to'] ?? null) && trim($config['redirect_to']) !== ''
            ? (string) $config['redirect_to']
            : null;
        $deniedMode = AreaAccessDeniedMode::tryFrom((string) ($config['denied_mode'] ?? AreaAccessDeniedMode::NotFound->value))
            ?? AreaAccessDeniedMode::NotFound;

        return new AreaAccessMiddleware(
            auth: $container->get(AuthManager::class),
            responses: $container->get(ResponseFactoryInterface::class),
            rule: new AreaAccessRule(
                area: (string) ($config['area'] ?? 'admin'),
                guard: is_string($config['guard'] ?? null) ? (string) $config['guard'] : null,
                permission: $permission,
                deniedMode: $deniedMode,
                redirectTo: $redirectTo,
            ),
        );
    }),

    AuthMiddleware::class => factory(
        static fn (ContainerInterface $container): AuthMiddleware => new AuthMiddleware($container->get(AuthManager::class)),
    ),

    GuardMiddleware::class => factory(
        static fn (ContainerInterface $container): GuardMiddleware => new GuardMiddleware($container->get(AuthManager::class)),
    ),
];
