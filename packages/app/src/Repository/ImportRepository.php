<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Dto\Pagination\PaginatedResult;
use MaxServ\App\Dto\Pagination\Paginator;
use MaxServ\App\Entity\Import;
use MaxServ\App\Hydrator\ImportHydrator;
use MaxServ\App\Utility\QueryUtility;
use MaxServ\Core\Database\Connection;
use PDO;

final readonly class ImportRepository
{
    public function __construct(
        private Connection $connection,
        private ImportHydrator $hydrator,
    ) {}

    public function find(int $id): ?Import
    {
        $stmt = $this->connection->pdo->prepare(
            query: 'SELECT * FROM imports WHERE id = :id',
        );
        $stmt->execute(params: [':id' => $id]);
        $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrator->hydrateFromDatabase(row: $row)
            : null;
    }

    public function save(Import $import): void
    {
        $sql = '
      INSERT INTO imports
        (id, type, status, started_at, completed_at, processed, total)
      VALUES
        (:id, :type, :status, :startedAt, :completedAt, :processed, :total)
      AS new_row
      ON DUPLICATE KEY UPDATE
        type         = new_row.type,
        status       = new_row.status,
        started_at   = new_row.started_at,
        completed_at = new_row.completed_at,
        processed    = new_row.processed,
        total        = new_row.total
    ';

        $this->connection->pdo->prepare(query: $sql)->execute(params: [
            ':id' => $import->id,
            ':type' => $import->type,
            ':status' => $import->status->value,
            ':startedAt' => $import->startedAt->format(format: 'Y-m-d H:i:s'),
            ':completedAt' => $import->completedAt?->format(format: 'Y-m-d H:i:s'),
            ':processed' => $import->processed,
            ':total' => $import->total,
        ]);

        $import->id ??= (int)$this->connection->pdo->lastInsertId();
    }

    public function delete(Import $import): void
    {
        $this->connection->pdo->prepare(
            query: 'DELETE FROM imports WHERE id = :id',
        )->execute(params: [':id' => $import->id]);
    }

    public function findMany(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(
            separator: ', ',
            array: array_fill(start_index: 0, count: count($ids), value: '?'),
        );
        $stmt = $this->connection->pdo->prepare(
            query: "SELECT * FROM imports WHERE id IN ($placeholders)",
        );
        $stmt->execute(params: $ids);

        $result = [];
        foreach ($stmt->fetchAll(mode: PDO::FETCH_ASSOC) as $row) {
            $result[] = $this->hydrator->hydrateFromDatabase(row: $row);
        }
        return $result;
    }

    public function saveMany(array $imports): void
    {
        if (empty($imports)) {
            return;
        }

        $placeholders = QueryUtility::buildPositionalPlaceholders(
            rowCount: count($imports),
            columnCount: 7,
        );

        $sql = "
          INSERT INTO imports
            (id, type, status, started_at, completed_at, processed, total)
          VALUES
            $placeholders
          AS new_row
          ON DUPLICATE KEY UPDATE
            type         = new_row.type,
            status       = new_row.status,
            started_at   = new_row.started_at,
            completed_at = new_row.completed_at,
            processed    = new_row.processed,
            total        = new_row.total
        ";

        $values = [];
        foreach ($imports as $import) {
            array_push(
                $values,
                $import->id,
                $import->type,
                $import->status->value,
                $import->startedAt->format('Y-m-d H:i:s'),
                $import->completedAt?->format('Y-m-d H:i:s'),
                $import->processed,
                $import->total,
            );
        }

        $this->connection->pdo->prepare(query: $sql)->execute(params: $values);
    }

    public function deleteMany(array $imports): void
    {
        $this->deleteByIds(
            ids: array_map(
                callback: fn(Import $import) => $import->id,
                array: $imports,
            ),
        );
    }

    public function deleteByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $placeholders = implode(
            separator: ', ',
            array: array_fill(start_index: 0, count: count($ids), value: '?'),
        );
        $this->connection->pdo->prepare(
            query: "DELETE FROM imports WHERE id IN ($placeholders)",
        )->execute(
            params: $ids,
        );
    }

    public function findAllPaginated(int|string|null $page, int|string|null $perPage): PaginatedResult
    {
        $countStmt = $this->connection->pdo->query(query: 'SELECT COUNT(*) FROM imports');

        $paginator = new Paginator(
            page: $page,
            perPage: $perPage,
            total: (int)$countStmt->fetchColumn(),
        );

        $stmt = $this->connection->pdo->query(
            query: "
              SELECT * FROM imports
              ORDER BY started_at DESC
              LIMIT {$paginator->perPage} OFFSET {$paginator->offset}
            ",
        );

        $items = array_map(
            callback: fn(array $row) => $this->hydrator->hydrateFromDatabase(row: $row),
            array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC),
        );

        return new PaginatedResult(items: $items, paginator: $paginator);
    }
}
