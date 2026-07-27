<?php

declare(strict_types=1);

namespace MaxServ\Core\Database\Command;

use MaxServ\Core\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'migrate:fresh', description: 'Drop every table and re-run all migrations from scratch')]
final class MigrateFreshCommand extends Command
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
        $io = new SymfonyStyle(input: $input, output: $output);

        if (!$input->getOption(name: 'force')) {
            $confirmed = $io->confirm(
                question: 'This will drop every table in the database. Continue?',
                default: false,
            );

            if (!$confirmed) {
                $io->warning(message: 'Aborted.');

                return Command::SUCCESS;
            }
        }

        $io->warning(message: 'Dropping all tables...');

        foreach ($this->migrator->fresh() as $filename => $status) {
            $io->writeln(messages: "  [$status] $filename");
        }

        $this
            ->getApplication()
            ->find(name: 'messenger:setup-transports')
            ->run(input: new ArrayInput([]), output: $output);

        $io->success(message: 'Database is fresh.');

        return Command::SUCCESS;
    }
}
