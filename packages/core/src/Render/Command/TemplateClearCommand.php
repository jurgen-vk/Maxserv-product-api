<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Command;

use MaxServ\Core\Render\TemplateCacher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'template:clear', description: 'Clear the Twig template cache')]
class TemplateClearCommand extends Command
{
    public function __construct(
        private readonly TemplateCacher $cacher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cacher->clear();

        (new SymfonyStyle($input, $output))->success('Template cache cleared.');

        return Command::SUCCESS;
    }
}
