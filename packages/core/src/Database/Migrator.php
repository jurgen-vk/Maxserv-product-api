<?php

declare(strict_types=1);

namespace MaxServ\Core\Database;

use PDO;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Throwable;

final readonly class Migrator
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function install(): void
    {
        $this->ensureTrackingTable();
    }

    public function isInitialized(): bool
    {
        return (bool)$this->connection->pdo->query(query: 'SHOW TABLES')->fetchColumn();
    }

    public function reset(): void
    {
        $pdo = $this->connection->pdo;
        $pdo->exec(statement: 'SET FOREIGN_KEY_CHECKS = 0');

        $tables = $pdo->query(query: 'SHOW TABLES')->fetchAll(mode: PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec(statement: 'DROP TABLE IF EXISTS `' . $table . '`');
        }

        $pdo->exec(statement: 'SET FOREIGN_KEY_CHECKS = 1');

        $this->ensureTrackingTable();
    }

    public function run(): iterable
    {
        $this->ensureTrackingTable();

        $pdo = $this->connection->pdo;

        $applied = $pdo
            ->query(query: 'SELECT package, filename FROM migrations')
            ->fetchAll(mode: PDO::FETCH_ASSOC);
        $appliedKeys = array_map(
            callback: static fn(array $row): string => $row['package'] . '/' . $row['filename'],
            array: $applied,
        );

        $files = iterator_to_array(
            (new Finder())
                ->files()
                ->in(dirs: APPLICATION_ROOT . '/packages')
                ->path(patterns: 'migrations')
                ->name(patterns: '*.sql'),
        );

        usort(
            array: $files,
            callback: static fn($a, $b) => $a->getFilename() <=> $b->getFilename(),
        );

        foreach ($files as $file) {
            $package = $this->packageNameFromPath($file->getPathname());
            $filename = $file->getFilename();

            if (in_array(needle: $package . '/' . $filename, haystack: $appliedKeys, strict: true)) {
                yield $filename => 'Skip';
                continue;
            }

            try {
                $pdo->exec(statement: $file->getContents());
                $statement = $pdo->prepare(
                    query: '
                      INSERT IGNORE INTO migrations (package, filename) 
                      VALUES (:package, :filename)
                    ',
                );

                $statement->execute(params: [':package' => $package, ':filename' => $filename]);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    message: "Migration \"$filename\" failed: {$exception->getMessage()}",
                    previous: $exception,
                );
            }

            yield $filename => 'Done';
        }
    }

    public function fresh(): iterable
    {
        $this->reset();

        yield from $this->run();
    }

    private function ensureTrackingTable(): void
    {
        $this->connection->pdo->exec(
            statement: '
              CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                package VARCHAR(190) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_package_filename (package, filename)
              )
            ',
        );
    }

    private function packageNameFromPath(string $path): string
    {
        $relative = str_replace(
            search: APPLICATION_ROOT . '/packages/',
            replace: '',
            subject: str_replace(search: '\\', replace: '/', subject: $path),
        );

        return explode(separator: '/', string: $relative)[0];
    }
}
