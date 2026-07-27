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
final class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'Short description, e.g. create_products_table',
            )
            ->addOption(
                name: 'package',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Which package this migration belongs to (e.g. core, app)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(input: $input, output: $output);

        $packages = [];
        $dirs = (new Finder())
            ->directories()
            ->in(dirs: APPLICATION_ROOT . '/packages')
            ->depth(levels: '== 0');

        foreach ($dirs as $dir) {
            $packages[] = $dir->getFilename();
        }

        $package = $input->getOption(name: 'package');
        $availablePackagesList = '- ' . implode(separator: "\n - ", array: $packages);

        if ($package === null) {
            $io->error(
                message: "The --package option is required. Available packages:\n$availablePackagesList",
            );

            return Command::FAILURE;
        }

        if (!in_array(needle: $package, haystack: $packages, strict: true)) {
            $io->error(
                message: "Unknown package \"$package\". Available packages:\n$availablePackagesList",
            );

            return Command::FAILURE;
        }

        $directory = APPLICATION_ROOT . "/packages/$package/migrations";
        if (!is_dir(filename: $directory)) {
            mkdir(directory: $directory, recursive: true);
        }

        $name = $input->getArgument(name: 'name');
        $now = new \DateTimeImmutable();

        do {
            $filename = $now->format(format: 'Y_m_d_Hisv') . "_$name.sql";
            $path = "$directory/$filename";
            $now = $now->modify(modifier: '+1 millisecond');
        } while (file_exists(filename: $path));

        file_put_contents(filename: $path, data: '');

        $io->success(message: "Created packages/$package/migrations/$filename");

        return Command::SUCCESS;
    }
}
