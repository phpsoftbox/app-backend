<?php

declare(strict_types=1);

use PhpSoftBox\Application\ApplicationEnvironment;
use PhpSoftBox\Application\ErrorHandler\ContentNegotiationExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\ExceptionHandlerInterface;
use PhpSoftBox\Application\ErrorHandler\HtmlExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\JsonExceptionHandler;
use PhpSoftBox\Application\Middleware\ErrorHandlerMiddleware;
use PhpSoftBox\Cookie\CookieMiddleware;
use PhpSoftBox\Cookie\CookieQueue;
use PhpSoftBox\Http\Emitter\EmitterInterface;
use PhpSoftBox\Http\Emitter\SapiEmitter;
use PhpSoftBox\Http\Message\ResponseFactory;
use PhpSoftBox\Http\Message\ServerRequestCreator;
use PhpSoftBox\Http\Message\StreamFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function PhpSoftBox\Container\factory;

return [
    ResponseFactory::class           => factory(static fn (): ResponseFactory => new ResponseFactory()),
    StreamFactory::class             => factory(static fn (): StreamFactory => new StreamFactory()),
    CookieQueue::class               => factory(static fn (): CookieQueue => new CookieQueue()),
    CookieMiddleware::class          => factory(static fn (ContainerInterface $container): CookieMiddleware => new CookieMiddleware($container->get(CookieQueue::class))),
    ResponseFactoryInterface::class  => factory(static fn (ContainerInterface $container): ResponseFactoryInterface => $container->get(ResponseFactory::class)),
    StreamFactoryInterface::class    => factory(static fn (ContainerInterface $container): StreamFactoryInterface => $container->get(StreamFactory::class)),
    ServerRequestCreator::class      => factory(static fn (): ServerRequestCreator => new ServerRequestCreator()),
    EmitterInterface::class          => factory(static fn (): EmitterInterface => new SapiEmitter()),
    ExceptionHandlerInterface::class => factory(static function (ContainerInterface $container): ExceptionHandlerInterface {
        $responseFactory = $container->get(ResponseFactory::class);
        $streamFactory   = $container->get(StreamFactory::class);
        $includeDetails  = $container->get(ApplicationEnvironment::class)->isDebug();

        return new ContentNegotiationExceptionHandler(
            new JsonExceptionHandler($responseFactory, $streamFactory, includeDetails: $includeDetails),
            new HtmlExceptionHandler($responseFactory, $streamFactory, includeDetails: $includeDetails),
        );
    }),
    ErrorHandlerMiddleware::class => factory(static function (ContainerInterface $container): ErrorHandlerMiddleware {
        return new ErrorHandlerMiddleware($container->get(ExceptionHandlerInterface::class));
    }),
];
