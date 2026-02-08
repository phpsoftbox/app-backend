<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\Request;

use App\Http\Request\LoginRequest;
use PhpSoftBox\Http\Message\ServerRequest;
use PhpSoftBox\Request\Request;
use PhpSoftBox\Validator\Exception\ValidationException;
use PhpSoftBox\Validator\Validator;
use PHPUnit\Framework\TestCase;

final class LoginRequestTest extends TestCase
{
    public function testValidateNormalizesCredentials(): void
    {
        $schema = $this->schema([
            'email'    => '  DEMO@EXAMPLE.TEST  ',
            'password' => ' password ',
        ]);

        $data = $schema->validate();

        $this->assertSame('demo@example.test', $data['email']);
        $this->assertSame('password', $data['password']);
    }

    public function testValidateRejectsInvalidEmail(): void
    {
        $schema = $this->schema([
            'email'    => 'not-email',
            'password' => 'password',
        ]);

        $this->expectException(ValidationException::class);

        $schema->validate();
    }

    /**
     * @param array<string, mixed> $body
     */
    private function schema(array $body): LoginRequest
    {
        $psr = new ServerRequest('POST', 'https://example.test/auth/login', parsedBody: $body);

        return new LoginRequest(new Request($psr, new Validator()));
    }
}
