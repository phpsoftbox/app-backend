<?php

declare(strict_types=1);

use PhpSoftBox\Inflector\Contracts\InflectorInterface;
use PhpSoftBox\Inflector\InflectorFactory;
use PhpSoftBox\Inflector\LanguageEnum;
use PhpSoftBox\Orm\Inflector\PhpSoftBoxInflectorAdapter;
use PhpSoftBox\Orm\Contracts\InflectorInterface as OrmInflectorInterface;
use Psr\Container\ContainerInterface;

use function DI\factory;

return [
    InflectorInterface::class => factory(static function (): InflectorInterface {
        $lang = strtolower((string) env('APP_INFLECTOR_LANG', 'en'));
        $language = $lang === 'en' ? LanguageEnum::EN : LanguageEnum::EN;

        return InflectorFactory::create($language);
    }),

    OrmInflectorInterface::class => factory(static function (ContainerInterface $container): OrmInflectorInterface {
        return new PhpSoftBoxInflectorAdapter($container->get(InflectorInterface::class));
    }),
];
