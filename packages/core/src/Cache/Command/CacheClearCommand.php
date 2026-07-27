<?php

declare(strict_types=1);

namespace MaxServ\Core\Cache\Command;

use MaxServ\Core\Cache\CacheableInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'cache:clear', description: 'Clear every cache (container, templates, routes, etc)')]
final class CacheClearCommand extends Command
{
    /**
     * @param iterable<CacheableInterface> $cacheables
     */
    public function __construct(
        private readonly iterable $cacheables,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->cacheables as $cacheable) {
            $cacheable->clear();
        }

        (new SymfonyStyle(input: $input, output: $output))->success('All caches cleared.');

        return Command::SUCCESS;
    }
}
