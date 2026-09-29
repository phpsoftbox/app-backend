<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\Application\Application;
use PhpSoftBox\Http\Message\ServerRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;

/**
 * Приложение собирается целиком — `config/app.php` с `config/middleware.php`, как в `public/index.php`.
 */
#[CoversNothing]
final class HttpStackIntegrationTest extends IntegrationTestCase
{
    /**
     * Проверим, что глобальный стек middleware из config/middleware.php собирается и пропускает запрос: все классы
     * существуют и создаются из контейнера.
     *
     * @see Application::handle()
     */
    #[Test]
    public function handlesRequestThroughMiddlewareStack(): void
    {
        /** @var Application $app */
        $app = (require __DIR__ . '/../../config/app.php')(self::container());

        $response = $app->handle(new ServerRequest(
            'GET',
            'https://localhost.test/health',
            ['Accept' => 'application/json'],
            serverParams: ['REMOTE_ADDR' => '127.0.0.1'],
        ));

        self::assertSame(200, $response->getStatusCode(), (string) $response->getBody());
    }
}
