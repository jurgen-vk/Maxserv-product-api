<?php

declare(strict_types=1);

namespace MaxServ\Core\Database\Command;

use MaxServ\Core\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'migrate:init', description: 'Run migrations, but only against a genuinely uninitialized database — a no-op otherwise')]
class MigrateInitCommand extends Command
{
    public function __construct(
        private readonly Migrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->migrator->isInitialized()) {
            $io->note('Database already initialized — skipping.');

            return Command::SUCCESS;
        }

        foreach ($this->migrator->run() as $filename => $status) {
            $io->writeln("  [$status] $filename");
        }

        $this->getApplication()->find('messenger:setup-transports')->run(new ArrayInput([]), $output);

        $io->success('Database initialized.');

        return Command::SUCCESS;
    }
}
