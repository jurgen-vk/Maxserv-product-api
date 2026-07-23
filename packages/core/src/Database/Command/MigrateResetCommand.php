<?php

declare(strict_types=1);

namespace MaxServ\Core\Database\Command;

use MaxServ\Core\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'migrate:reset', description: 'Drop every table without re-running migrations')]
class MigrateResetCommand extends Command
{
    public function __construct(
        private readonly Migrator $migrator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'force',
            mode: InputOption::VALUE_NONE,
            description: 'Skip the confirmation prompt',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force') && !$io->confirm('This will drop every table in the database. Continue?', false)) {
            $io->warning('Aborted.');

            return Command::SUCCESS;
        }

        $this->migrator->reset();

        $io->success('Database reset. No migrations were re-run.');

        return Command::SUCCESS;
    }
}
