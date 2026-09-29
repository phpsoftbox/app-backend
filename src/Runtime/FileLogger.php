<?php

declare(strict_types=1);

namespace App\Runtime;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\AbstractLogger;
use Stringable;
use Throwable;

use function dirname;
use function file_put_contents;
use function get_debug_type;
use function is_dir;
use function is_scalar;
use function json_encode;
use function mkdir;

use const FILE_APPEND;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const LOCK_EX;

/**
 * Логгер скелета по умолчанию: одна JSON-строка на запись в файл (`local/logs/app.log`).
 *
 * Замените на полноценный логгер (например, `phpsoftbox/logger`), когда понадобятся ротация и каналы.
 */
final class FileLogger extends AbstractLogger
{
    public function __construct(
        private readonly string $file,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $record = [
            'time'    => new DateTimeImmutable('now', new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z'),
            'level'   => (string) $level,
            'message' => (string) $message,
            'context' => $this->normalizeContext($context),
        ];

        $directory = dirname($this->file);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        @file_put_contents($this->file, ($line === false ? '{"message":"log encoding failed"}' : $line) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function normalizeContext(array $context): array
    {
        $normalized = [];
        foreach ($context as $key => $value) {
            $normalized[$key] = match (true) {
                $value instanceof Throwable => [
                    'class'   => $value::class,
                    'message' => $value->getMessage(),
                    'file'    => $value->getFile() . ':' . $value->getLine(),
                    'trace'   => $value->getTraceAsString(),
                ],
                $value === null || is_scalar($value) => $value,
                $value instanceof Stringable         => (string) $value,
                default                              => get_debug_type($value),
            };
        }

        return $normalized;
    }
}
