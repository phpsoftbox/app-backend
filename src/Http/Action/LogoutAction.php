<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Feature\Auth\Command\Logout\LogoutCommand;
use App\Feature\Auth\Command\Logout\LogoutHandler;
use App\Support\JsonResponder;
use PhpSoftBox\Application\Response\JsonResponse;

final readonly class LogoutAction
{
    public function __construct(
        private LogoutHandler $handler,
        private JsonResponder $responses,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->handler->handle(new LogoutCommand());

        return $this->responses->success([
            'authenticated' => false,
        ]);
    }
}
