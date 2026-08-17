<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use InvalidArgumentException;
use MaxServ\App\Dto\Pagination\PaginatedResult;
use MaxServ\App\Dto\Pagination\Paginator;
use MaxServ\App\Entity\Import;
use MaxServ\App\Enum\ImportStatus;
use MaxServ\App\Filter\ImportFilter;
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

    private const array PLUCKABLE_COLUMNS = ['type'];

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
            (id, type, status, started_at, ended_at, processed, total)
          VALUES
            (:id, :type, :status, :startedAt, :endedAt, :processed, :total)
          AS new_row
          ON DUPLICATE KEY UPDATE
            type         = new_row.type,
            status       = new_row.status,
            started_at   = new_row.started_at,
            ended_at     = new_row.ended_at,
            processed    = new_row.processed,
            total        = new_row.total
        ';

        $this->connection->pdo->prepare(query: $sql)->execute(params: [
            ':id' => $import->id,
            ':type' => $import->type,
            ':status' => $import->status->value,
            ':startedAt' => $import->startedAt->format(format: 'Y-m-d H:i:s'),
            ':endedAt' => $import->endedAt?->format(format: 'Y-m-d H:i:s'),
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
            (id, type, status, started_at, ended_at, processed, total)
          VALUES
            $placeholders
          AS new_row
          ON DUPLICATE KEY UPDATE
            type         = new_row.type,
            status       = new_row.status,
            started_at   = new_row.started_at,
            ended_at     = new_row.ended_at,
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
                $import->endedAt?->format('Y-m-d H:i:s'),
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

    public function findActiveByType(string $type): ?Import
    {
        $stmt = $this->connection->pdo->prepare(
            query: "
              SELECT * FROM imports
              WHERE type = :type AND status IN ('pending', 'running', 'started')
              ORDER BY started_at DESC
              LIMIT 1
            ",
        );
        $stmt->execute(params: [':type' => $type]);
        $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrator->hydrateFromDatabase(row: $row)
            : null;
    }

    public function findByStatuses(array $statuses): array
    {
        [$placeholders, $bindings] = QueryUtility::buildNamedPlaceholders(
            prefix: 'status',
            values: array_map(callback: fn(ImportStatus $status) => $status->value, array: $statuses),
        );

        $stmt = $this->connection->pdo->prepare(
            query: "SELECT * FROM imports WHERE status IN ($placeholders)",
        );
        $stmt->execute(params: $bindings);

        return array_map(
            callback: fn(array $row) => $this->hydrator->hydrateFromDatabase(row: $row),
            array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC),
        );
    }

    public function count(?ImportFilter $filter = null): int
    {
        $stmt = $this->connection->pdo->prepare(
            query: "SELECT COUNT(*) FROM imports {$filter?->joins} {$filter?->filters}",
        );
        $stmt->execute(params: $filter?->bindings);

        return (int)$stmt->fetchColumn();
    }

    public function searchPaginated(ImportFilter $filter, int|string|null $page, int|string|null $perPage): PaginatedResult
    {
        $paginator = new Paginator(page: $page, perPage: $perPage, total: $this->count(filter: $filter));

        $stmt = $this->connection->pdo->prepare(
            query: "
              SELECT imports.* FROM imports
              {$filter->joins}
              {$filter->filters}
              {$filter->sorts}
              LIMIT {$paginator->perPage} OFFSET {$paginator->offset}
            ",
        );
        $stmt->execute(params: $filter->bindings);

        $items = array_map(
            callback: fn(array $row): Import => $this->hydrator->hydrateFromDatabase(row: $row),
            array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC),
        );

        return new PaginatedResult(items: $items, paginator: $paginator);
    }

    public function pluckDistinct(string $column): array
    {
        if (!in_array(needle: $column, haystack: self::PLUCKABLE_COLUMNS, strict: true)) {
            throw new InvalidArgumentException(message: "Column \"$column\" is not allowed for pluckDistinct().");
        }

        return $this->connection->pdo
            ->query(query: "SELECT DISTINCT $column FROM imports ORDER BY $column")
            ->fetchAll(mode: PDO::FETCH_COLUMN);
    }
}
