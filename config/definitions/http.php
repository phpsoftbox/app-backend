<?php

declare(strict_types=1);

use App\Path;
use App\Runtime\FileLogger;
use PhpSoftBox\Application\ApplicationEnvironment;
use PhpSoftBox\Application\ErrorHandler\ContentNegotiationExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\DefaultExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\ExceptionHandlerInterface;
use PhpSoftBox\Application\ErrorHandler\HtmlExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\JsonExceptionHandler;
use PhpSoftBox\Application\ErrorHandler\LoggerExceptionReporter;
use PhpSoftBox\Application\Middleware\ErrorHandlerMiddleware;
use PhpSoftBox\Application\Middleware\TrustedProxyMiddleware;
use PhpSoftBox\Config\Config;
use PhpSoftBox\Cookie\CookieMiddleware;
use PhpSoftBox\Cookie\CookieQueue;
use PhpSoftBox\Http\Emitter\EmitterInterface;
use PhpSoftBox\Http\Emitter\SapiEmitter;
use PhpSoftBox\Http\Message\ResponseFactory;
use PhpSoftBox\Http\Message\ServerRequestCreator;
use PhpSoftBox\Http\Message\StreamFactory;
use PhpSoftBox\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

use function PhpSoftBox\Container\factory;

return [
    ResponseFactory::class          => factory(static fn (): ResponseFactory => new ResponseFactory()),
    StreamFactory::class            => factory(static fn (): StreamFactory => new StreamFactory()),
    CookieQueue::class              => factory(static fn (): CookieQueue => new CookieQueue()),
    CookieMiddleware::class         => factory(static fn (ContainerInterface $container): CookieMiddleware => new CookieMiddleware($container->get(CookieQueue::class))),
    ResponseFactoryInterface::class => factory(static fn (ContainerInterface $container): ResponseFactoryInterface => $container->get(ResponseFactory::class)),
    StreamFactoryInterface::class   => factory(static fn (ContainerInterface $container): StreamFactoryInterface => $container->get(StreamFactory::class)),
    ServerRequestCreator::class     => factory(static fn (): ServerRequestCreator => new ServerRequestCreator()),
    EmitterInterface::class         => factory(static fn (): EmitterInterface => new SapiEmitter()),
    TrustedProxyMiddleware::class   => factory(static function (ContainerInterface $container): TrustedProxyMiddleware {
        $proxies = $container->get(Config::class)->get('app.trusted_proxies', []);

        return new TrustedProxyMiddleware(is_array($proxies) ? array_values(array_filter($proxies, is_string(...))) : []);
    }),
    ExceptionHandlerInterface::class => factory(static function (ContainerInterface $container): ExceptionHandlerInterface {
        $responseFactory = $container->get(ResponseFactory::class);
        $streamFactory   = $container->get(StreamFactory::class);
        $includeDetails  = $container->get(ApplicationEnvironment::class)->isDebug();

        // Необработанные исключения пишутся в лог (LoggerExceptionReporter), ответ формирует обработчик по формату.
        return new DefaultExceptionHandler(
            fallbackHandler: new ContentNegotiationExceptionHandler(
                new JsonExceptionHandler($responseFactory, $streamFactory, includeDetails: $includeDetails),
                new HtmlExceptionHandler($responseFactory, $streamFactory, includeDetails: $includeDetails),
            ),
            responseFactory: $responseFactory,
            session: $container->has(SessionInterface::class) ? $container->get(SessionInterface::class) : null,
            reporters: [new LoggerExceptionReporter($container->get(LoggerInterface::class))],
        );
    }),
    LoggerInterface::class => factory(static fn (ContainerInterface $container): LoggerInterface => new FileLogger(
        $container->get(Path::class)->logsPath('app.log'),
    )),
    ErrorHandlerMiddleware::class => factory(static function (ContainerInterface $container): ErrorHandlerMiddleware {
        return new ErrorHandlerMiddleware($container->get(ExceptionHandlerInterface::class));
    }),
];
