<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Feature\Auth\Command\Login\LoginCommand;
use App\Feature\Auth\Command\Login\LoginHandler;
use App\Http\Request\LoginRequest;
use App\Http\Resource\UserResource;
use App\Support\JsonResponder;
use PhpSoftBox\Application\Response\JsonResponse;

final readonly class LoginAction
{
    public function __construct(
        private LoginHandler $handler,
        private JsonResponder $responses,
    ) {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->handler->handle(new LoginCommand(
            email: $request->getString('email'),
            password: $request->getString('password'),
        ));

        if ($user === null) {
            return $this->responses->error(
                message: 'Invalid credentials.',
                status: 401,
                code: 'invalid_credentials',
            );
        }

        return $this->responses->success([
            'user' => new UserResource($user),
        ]);
    }
}
