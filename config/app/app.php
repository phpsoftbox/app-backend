<?php

declare(strict_types=1);

use App\Runtime\Environment;
use PhpSoftBox\Env\EnvStorage;

return [
    'env'   => Environment::detect()->value,
    'debug' => EnvStorage::value('APP_DEBUG')->bool(default: false) ?? false,
    'url'   => env('APP_URL', 'https://domain.local'),

    // Прокси (IP или CIDR через запятую), от которых принимаются X-Forwarded-For/Proto/Host/Port. Пусто — не доверять
    // никому: за балансировщиком укажите его адрес, иначе IP клиента и схема будут адресом и схемой балансировщика.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('APP_TRUSTED_PROXIES', ''))))),
];
