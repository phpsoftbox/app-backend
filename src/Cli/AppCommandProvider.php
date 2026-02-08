<?php

declare(strict_types=1);

namespace App\Cli;

use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Loader\CommandProviderInterface;

final class AppCommandProvider implements CommandProviderInterface
{
    public function register(CommandRegistryInterface $registry): void
    {
        $registry->register(Command::define(
            name: 'app:health',
            description: 'Check application bootstrap',
            signature: [],
            handler: HealthHandler::class,
        ));
    }
}
