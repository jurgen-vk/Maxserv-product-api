<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'make:template', description: 'Scaffold a new template (index.html.twig, index.css, index.js)')]
final class MakeTemplateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'path',
                mode: InputArgument::REQUIRED,
                description: 'Dot or slash notation, e.g. pages.products.show._nav-menu or pages/products/show/_nav-menu',
            )
            ->addOption(
                name: 'twig',
                shortcut: 't',
                mode: InputOption::VALUE_NONE,
                description: 'Generate index.html.twig',
            )
            ->addOption(
                name: 'css',
                shortcut: 'c',
                mode: InputOption::VALUE_NONE,
                description: 'Generate index.css',
            )
            ->addOption(
                name: 'js',
                shortcut: 'j',
                mode: InputOption::VALUE_NONE,
                description: 'Generate index.js',
            )
            ->addOption(
                name: 'class',
                mode: InputOption::VALUE_REQUIRED,
                description: "Override the wrapping element's class name",
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Override the base filename',
                default: 'index',
            )
            ->addOption(
                name: 'package',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Which package to generate into (e.g. core, app)',
                default: 'app',
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

        if (!in_array(needle: $package, haystack: $packages, strict: true)) {
            $io->error(
                message: "Unknown package \"$package\". Available packages:\n$availablePackagesList",
            );

            return Command::FAILURE;
        }

        $rawPath = trim(string: (string)$input->getArgument(name: 'path'), characters: './ ');
        $segments = preg_split(pattern: '/[.\/]+/', subject: $rawPath);

        if ($segments === false || $segments === [''] || count(value: $segments) < 2) {
            $io->error(
                message: 'Path must include a category and at least one more segment, e.g. pages.products.show.',
            );

            return Command::FAILURE;
        }

        $category = $segments[0];
        $prefix = $category[0];

        $className = $input->getOption(name: 'class');
        if ($className === null) {
            $rest = implode(separator: '_', array: array_slice(array: $segments, offset: 1));
            $className = "{$prefix}_$rest";
        }

        $relativePath = implode(separator: '/', array: $segments);
        $directory = APPLICATION_ROOT . "/packages/$package/templates/$relativePath";
        $fileName = $input->getOption(name: 'name');

        $generateTwig = (bool)$input->getOption(name: 'twig');
        $generateCss = (bool)$input->getOption(name: 'css');
        $generateJs = (bool)$input->getOption(name: 'js');

        if (!$generateTwig && !$generateCss && !$generateJs) {
            $generateTwig = $generateCss = $generateJs = true;
        }

        if (!is_dir(filename: $directory) && !@mkdir(directory: $directory, recursive: true) && !is_dir(
                filename: $directory,
            )) {
            $io->error(message: "Could not create directory: $directory");

            return Command::FAILURE;
        }

        $created = [];
        $skipped = [];
        $failed = [];

        if ($generateTwig) {
            $this->writeIfMissing(
                path: "$directory/$fileName.html.twig",
                contents: <<<TWIG
                    <div {{ attrs(defaults: { class: '$className' }, attributes: attributes) }}>
                    
                    </div>
                    TWIG,
                created: $created,
                skipped: $skipped,
                failed: $failed,
            );
        }

        if ($generateCss) {
            $this->writeIfMissing(
                path: "$directory/$fileName.css",
                contents: <<<CSS
                    @layer main {
                      .$className {
                    
                      }
                    }
                    CSS,
                created: $created,
                skipped: $skipped,
                failed: $failed,
            );
        }

        if ($generateJs) {
            $this->writeIfMissing(
                path: "$directory/$fileName.js",
                contents: <<<JS
                    import \$ from 'jquery';
                    
                    \$('.$className').found(function (\$root) {
                    
                    });
                    JS,
                created: $created,
                skipped: $skipped,
                failed: $failed,
            );
        }

        foreach ($created as $file) {
            $io->writeln(messages: "  Created $file");
        }

        foreach ($skipped as $file) {
            $io->writeln(messages: "  Skipped $file (already exists)");
        }

        foreach ($failed as $file) {
            $io->writeln(messages: "  <error>Failed to write $file</error>");
        }

        if ($failed !== []) {
            $io->error(message: 'One or more files could not be written - check directory permissions.');

            return Command::FAILURE;
        }

        $io->success(
            message: "Template scaffolded in packages/$package/templates/$relativePath ($className)",
        );

        return Command::SUCCESS;
    }

    private function writeIfMissing(
        string $path,
        string $contents,
        array &$created,
        array &$skipped,
        array &$failed,
    ): void {
        if (file_exists(filename: $path)) {
            $skipped[] = basename(path: $path);

            return;
        }

        if (@file_put_contents(filename: $path, data: $contents) === false) {
            $failed[] = basename(path: $path);

            return;
        }

        $created[] = basename(path: $path);
    }
}