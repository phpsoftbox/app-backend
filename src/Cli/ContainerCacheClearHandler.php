<?php

declare(strict_types=1);

namespace App\Cli;

use App\Path;
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PhpSoftBox\Container\ContainerBuilder;

use function dirname;

/**
 * Сбрасывает скомпилированный DI-контейнер (`local/cache/di`). Запускать при деплое после `composer install` — до того,
 * как воркеры соберут контейнер заново.
 */
final class ContainerCacheClearHandler implements HandlerInterface
{
    public function run(RunnerInterface $runner): int|Response
    {
        $directory = new Path(dirname(__DIR__, 2))->cachePath('di');

        $removed = ContainerBuilder::clearCompiledCache($directory);

        $runner->io()->writeln('Кеш контейнера очищен (' . $directory . '), файлов: ' . $removed . '.', 'success');

        return Response::SUCCESS;
    }
}
