<?php

declare(strict_types=1);

use PhpSoftBox\Application\ErrorHandler\ContentNegotiationExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\ExceptionHandlerInterface;
use PhpSoftBox\Application\ErrorHandler\HtmlExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\JsonExceptionHandler;
use PhpSoftBox\Application\Middleware\ErrorHandlerMiddleware;
use PhpSoftBox\Http\Emitter\EmitterInterface;
use PhpSoftBox\Http\Emitter\SapiEmitter;
use PhpSoftBox\Http\Message\ResponseFactory;
use PhpSoftBox\Http\Message\ServerRequestCreator;
use PhpSoftBox\Http\Message\StreamFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function DI\factory;

return [
    ResponseFactory::class => factory(static fn (): ResponseFactory => new ResponseFactory()),
    StreamFactory::class => factory(static fn (): StreamFactory => new StreamFactory()),
    ResponseFactoryInterface::class => factory(static fn (ContainerInterface $container): ResponseFactoryInterface => $container->get(ResponseFactory::class)),
    StreamFactoryInterface::class => factory(static fn (ContainerInterface $container): StreamFactoryInterface => $container->get(StreamFactory::class)),
    ServerRequestCreator::class => factory(static fn (): ServerRequestCreator => new ServerRequestCreator()),
    EmitterInterface::class => factory(static fn (): EmitterInterface => new SapiEmitter()),
    ExceptionHandlerInterface::class => factory(static function (ContainerInterface $container): ExceptionHandlerInterface {
        $responseFactory = $container->get(ResponseFactory::class);
        $streamFactory = $container->get(StreamFactory::class);

        return new ContentNegotiationExceptionHandler(
            new JsonExceptionHandler($responseFactory, $streamFactory, includeDetails: true),
            new HtmlExceptionHandler($responseFactory, $streamFactory, includeDetails: true),
        );
    }),
    ErrorHandlerMiddleware::class => factory(static function (ContainerInterface $container): ErrorHandlerMiddleware {
        return new ErrorHandlerMiddleware($container->get(ExceptionHandlerInterface::class));
    }),
];
