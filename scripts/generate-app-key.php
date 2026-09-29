<?php

declare(strict_types=1);

/**
 * Записывает случайный APP_KEY (32 байта, base64) в config/env/.env, если он пуст. Запускается при create-project.
 */

$file = __DIR__ . '/../config/env/.env';
if (!is_file($file)) {
    exit(0);
}

$content = (string) file_get_contents($file);
if (preg_match('/^APP_KEY=\s*$/m', $content) !== 1) {
    exit(0);
}

$content = (string) preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY=' . base64_encode(random_bytes(32)), $content, 1);
file_put_contents($file, $content);

fwrite(STDOUT, "APP_KEY generated in config/env/.env\n");
