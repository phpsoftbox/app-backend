<?php

declare(strict_types=1);

return [
    'default_guard' => 'web',
    'users'         => [
        [
            'id'            => 1,
            'email'         => 'demo@example.test',
            'name'          => 'Demo User',
            'password_hash' => '$2y$12$s/9YpCI9uZB5WWptEcg5Z.nWAiicQ1FIjsQxc.hh94z2GvMnJDSI6',
        ],
    ],
    'login_fields' => ['email'],
    'id_field'     => 'id',
    'session'      => [
        'key'           => 'auth.user_id',
        'hash_key'      => null,
        'user_hash_key' => 'password_hash',
    ],
];
