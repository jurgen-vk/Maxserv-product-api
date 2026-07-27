<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing\Command;

use MaxServ\Core\Routing\RouteCacher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'route:clear', description: 'Clear the route cache')]
final class RouteClearCommand extends Command
{
    public function __construct(
        private readonly RouteCacher $cacher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cacher->clear();

        (new SymfonyStyle(input: $input, output: $output))
            ->success(message: 'Route cache cleared.');

        return Command::SUCCESS;
    }
}
