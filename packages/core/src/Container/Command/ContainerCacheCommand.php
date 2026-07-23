<?php

declare(strict_types=1);

namespace MaxServ\Core\Container\Command;

use MaxServ\Core\Container\ContainerProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'container:cache', description: 'Build and cache the DI container')]
class ContainerCacheCommand extends Command
{
    public function __construct(
        private readonly ContainerProvider $provider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->provider->cache();

        (new SymfonyStyle($input, $output))->success('Container cached.');

        return Command::SUCCESS;
    }
}
