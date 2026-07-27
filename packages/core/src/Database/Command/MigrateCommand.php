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

#[AsCommand(name: 'migrate', description: 'Run pending database migrations')]
final class MigrateCommand extends Command
{
    public function __construct(
        private readonly Migrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(input: $input, output: $output);

        foreach ($this->migrator->run() as $filename => $status) {
            $io->writeln(messages: "  [$status] $filename");
        }

        $this
            ->getApplication()
            ->find(name: 'messenger:setup-transports')
            ->run(input: new ArrayInput([]), output: $output);

        $io->success(message: 'Migrations complete.');

        return Command::SUCCESS;
    }
}
