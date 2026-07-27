<?php

declare(strict_types=1);

namespace MaxServ\Core\Database\Command;

use MaxServ\Core\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'migrate:install', description: 'Create the migrations tracking table, without running any migrations')]
final class MigrateInstallCommand extends Command
{
    public function __construct(
        private readonly Migrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrator->install();

        (new SymfonyStyle(input: $input, output: $output))
            ->success(message: 'Migrations table is ready.');

        return Command::SUCCESS;
    }
}
