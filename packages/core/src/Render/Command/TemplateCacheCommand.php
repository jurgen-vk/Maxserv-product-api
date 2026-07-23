<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Command;

use MaxServ\Core\Render\TemplateCacher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'template:cache', description: 'Warm up the Twig template cache')]
class TemplateCacheCommand extends Command
{
    public function __construct(
        private readonly TemplateCacher $cacher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cacher->cache();

        (new SymfonyStyle($input, $output))->success('Template cache warmed.');

        return Command::SUCCESS;
    }
}
