<?php

declare(strict_types=1);

namespace MaxServ\Core\Database\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'make:migration', description: 'Scaffold a new, timestamped migration file for a package')]
class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'Short description, e.g. add_sku_to_products',
            )
            ->addOption(
                name: 'package',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Which package this migration belongs to (e.g. core, app)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $packages = [];
        foreach ((new Finder())->directories()->in(APPLICATION_ROOT . '/packages')->depth('== 0') as $dir) {
            $packages[] = $dir->getFilename();
        }

        $package = $input->getOption('package');

        if ($package === null) {
            $io->error('The --package option is required. Available packages: ' . implode(', ', $packages));

            return Command::FAILURE;
        }

        if (!in_array($package, $packages, strict: true)) {
            $io->error("Unknown package \"$package\". Available packages: " . implode(', ', $packages));

            return Command::FAILURE;
        }

        $directory = APPLICATION_ROOT . "/packages/$package/migrations";
        if (!is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        $filename = date('YmdHis') . '_' . $input->getArgument('name') . '.sql';
        $path = "$directory/$filename";

        file_put_contents($path, "-- $filename\n");

        $io->success("Created packages/$package/migrations/$filename");

        return Command::SUCCESS;
    }
}
