<?php

declare(strict_types=1);

namespace App\Tests\Unit\Runtime;

use App\Runtime\Environment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Environment::class)]
#[CoversMethod(Environment::class, 'detect')]
final class EnvironmentTest extends TestCase
{
    private mixed $previous;

    protected function setUp(): void
    {
        $this->previous = $_ENV['APP_ENV'] ?? null;
    }

    protected function tearDown(): void
    {
        $_ENV['APP_ENV'] = $this->previous;
    }

    /**
     * Проверим, что полное имя окружения приводится к короткому: `production` → `prod`.
     *
     * @see Environment::detect()
     */
    #[Test]
    public function mapsProductionAlias(): void
    {
        $_ENV['APP_ENV'] = 'production';

        self::assertSame(Environment::PROD, Environment::detect());
    }

    /**
     * Проверим, что неизвестное окружение даёт понятную ошибку со списком допустимых значений.
     *
     * @see Environment::detect()
     */
    #[Test]
    public function rejectsUnknownEnvironment(): void
    {
        $_ENV['APP_ENV'] = 'staging';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown APP_ENV "staging". Allowed: dev, demo, test, prod');

        Environment::detect();
    }
}
