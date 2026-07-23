<?php

declare(strict_types=1);

namespace MaxServ\Core\Cache\Command;

use MaxServ\Core\Cache\CachableInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'cache:clear', description: 'Clear every cache (container, templates, routes)')]
class CacheClearCommand extends Command
{
    /**
     * @param iterable<CachableInterface> $cachables
     */
    public function __construct(
        private readonly iterable $cachables,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->cachables as $cachable) {
            $cachable->clear();
        }

        (new SymfonyStyle($input, $output))->success('All caches cleared.');

        return Command::SUCCESS;
    }
}
