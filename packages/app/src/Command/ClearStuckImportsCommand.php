<?php

declare(strict_types=1);

namespace MaxServ\App\Command;

use MaxServ\App\Enum\ImportStatus;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Repository\ImportRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'import:clear-stuck',
    description: 'Mark imports stuck in "pending", "started" or "running" as failed (e.g. after a worker crash/restart)'
)]
final class ClearStuckImportsCommand extends Command
{
    public function __construct(
        private readonly ImportRepository $importRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(input: $input, output: $output);

        $stuck = $this->importRepository->findByStatuses(
            statuses: [ImportStatus::Pending, ImportStatus::Started, ImportStatus::Running],
        );

        foreach ($stuck as $import) {
            $import->markFailed();
            $this->importRepository->save(import: $import);
            $this->eventDispatcher->dispatch(event: new ImportFailedEvent(import: $import));

            $io->writeln(messages: "  Marked import #{$import->id} as failed");
        }

        $io->success(message: count($stuck) . ' stuck import(s) cleared.');

        return Command::SUCCESS;
    }
}