<?php

declare(strict_types=1);

namespace MaxServ\Core\Database;

use PDO;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Throwable;

readonly class Migrator
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function install(): void
    {
        $this->ensureTrackingTable();
    }

    public function isInitialized(): bool
    {
        return (bool) $this->connection->pdo->query('SHOW TABLES')->fetchColumn();
    }

    public function reset(): void
    {
        $pdo = $this->connection->pdo;
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $table . '`');
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->ensureTrackingTable();
    }

    public function run(): iterable
    {
        $this->ensureTrackingTable();

        $pdo = $this->connection->pdo;

        $applied = $pdo
            ->query('SELECT package, filename FROM migrations')
            ->fetchAll(PDO::FETCH_ASSOC);
        $appliedKeys = array_map(
            static fn (array $row): string => $row['package'] . '/' . $row['filename'],
            $applied,
        );

        $files = iterator_to_array(
            (new Finder())
                ->files()
                ->in(APPLICATION_ROOT . '/packages')
                ->path('migrations')
                ->name('*.sql')
        );
        usort($files, static fn ($a, $b) => $a->getFilename() <=> $b->getFilename());

        foreach ($files as $file) {
            $package = $this->packageNameFromPath($file->getPathname());
            $filename = $file->getFilename();

            if (in_array($package . '/' . $filename, $appliedKeys, strict: true)) {
                yield $filename => 'skip';
                continue;
            }

            // MySQL implicitly commits before DDL statements (CREATE TABLE, etc.), so wrapping
            // these in an explicit PDO transaction can't provide real atomicity the way it
            // would on a database with transactional DDL — the transaction ends the moment the
            // statement runs, before commit()/rollBack() would ever be reached.
            //
            // INSERT IGNORE (not INSERT): the app and worker containers both run `migrate` on
            // startup concurrently, so two processes can both see a migration as pending and
            // both run it — harmless since the migrations themselves are idempotent
            // (CREATE TABLE IF NOT EXISTS), but the second tracking-row insert would otherwise
            // crash on the unique constraint instead of silently losing the race.
            try {
                $pdo->exec($file->getContents());
                $pdo
                    ->prepare('INSERT IGNORE INTO migrations (package, filename) VALUES (:package, :filename)')
                    ->execute([':package' => $package, ':filename' => $filename]);
            } catch (Throwable $exception) {
                throw new RuntimeException("Migration \"$filename\" failed: {$exception->getMessage()}", previous: $exception);
            }

            yield $filename => 'done';
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
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                package VARCHAR(190) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_package_filename (package, filename)
            )'
        );
    }

    private function packageNameFromPath(string $path): string
    {
        $relative = str_replace(APPLICATION_ROOT . '/packages/', '', str_replace('\\', '/', $path));

        return explode('/', $relative)[0];
    }
}
