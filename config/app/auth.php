<?php

declare(strict_types=1);

use App\Entity\User;

return [
    'default_guard' => 'web',
    'provider'      => [
        'driver'       => env('APP_AUTH_PROVIDER', 'database'),
        'connection'   => env('APP_AUTH_CONNECTION', 'default'),
        'table'        => env('APP_AUTH_USERS_TABLE', 'users'),
        'identity'     => User::class,
        'login_fields' => ['email'],
        'id_field'     => 'id',
    ],
    'session' => [
        'key'      => env('APP_AUTH_SESSION_KEY', 'auth.user_id'),
        'hash_key' => env('APP_AUTH_SESSION_HASH_KEY') ?: null,
    ],
    'remember' => [
        'cookie'          => env('APP_AUTH_REMEMBER_COOKIE', 'remember_token'),
        'connection'      => env('APP_AUTH_REMEMBER_CONNECTION', 'default'),
        'table'           => env('APP_AUTH_REMEMBER_TABLE', 'user_tokens'),
        'path'            => env('APP_AUTH_REMEMBER_PATH', '/'),
        'domain'          => env('APP_AUTH_REMEMBER_DOMAIN'),
        'secure'          => env('APP_AUTH_REMEMBER_SECURE', 'always'),
        'http_only'       => env('APP_AUTH_REMEMBER_HTTP_ONLY', '1') === '1',
        'same_site'       => env('APP_AUTH_REMEMBER_SAME_SITE', 'Lax'),
        'max_age'         => env('APP_AUTH_REMEMBER_MAX_AGE', (string) (60 * 60 * 24 * 30)),
        'mismatch_policy' => env('APP_AUTH_REMEMBER_MISMATCH_POLICY', 'revoke_token'),
    ],
    'authorization' => [
        'connection'             => env('APP_AUTHORIZATION_CONNECTION', 'default'),
        'roles_table'            => 'roles',
        'permissions_table'      => 'permissions',
        'role_permissions_table' => 'role_permissions',
        'user_roles_table'       => 'user_roles',
        'user_permissions_table' => 'user_permissions',
        'permissions'            => [
            'admin.access' => 'Доступ в административную область',
        ],
        'roles' => [
            [
                'name'        => 'admin',
                'label'       => 'Администратор',
                'permissions' => ['admin.access'],
            ],
        ],
    ],
    'areas' => [
        'admin' => [
            'area'        => 'admin',
            'guard'       => 'web',
            'permission'  => env('APP_ADMIN_PERMISSION', 'admin.access'),
            'denied_mode' => env('APP_ADMIN_DENIED_MODE', 'not_found'),
            'redirect_to' => env('APP_ADMIN_REDIRECT_TO', '/'),
        ],
    ],
];
