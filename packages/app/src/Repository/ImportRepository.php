<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Entity\Import;
use MaxServ\App\Hydrator\ImportHydrator;
use MaxServ\Core\Database\Connection;
use PDO;

class ImportRepository
{
  public function __construct(
    private readonly Connection $connection,
    private readonly ImportHydrator $hydrator,
  ) {
  }

  public function find(int $id): ?Import
  {
    $stmt = $this->connection->pdo->prepare(query: 'SELECT * FROM imports WHERE id = :id');
    $stmt->execute(params: [':id' => $id]);
    $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);

    return $row !== false ? $this->hydrator->hydrateFromDatabase(row: $row) : null;
  }

  public function save(Import $import): void
  {
    $sql = '
      INSERT INTO imports
        (id, type, status, started_at, completed_at, count)
      VALUES
        (:id, :type, :status, :startedAt, :completedAt, :count)
      AS new_row
      ON DUPLICATE KEY UPDATE
        type         = new_row.type,
        status       = new_row.status,
        started_at   = new_row.started_at,
        completed_at = new_row.completed_at,
        count        = new_row.count
    ';

    $this->connection->pdo->prepare(query: $sql)->execute(params: [
      ':id'          => $import->id,
      ':type'        => $import->type,
      ':status'      => $import->status->value,
      ':startedAt'   => $import->startedAt->format('Y-m-d H:i:s'),
      ':completedAt' => $import->completedAt?->format('Y-m-d H:i:s'),
      ':count'       => $import->count,
    ]);

    $import->id ??= (int) $this->connection->pdo->lastInsertId();
  }

  public function delete(Import $import): void
  {
    $this->deleteByIds(ids: [$import->id]);
  }

  public function findMany(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = implode(
      separator: ', ',
      array: array_fill(start_index: 0, count: count($ids), value: '?')
    );
    $stmt = $this->connection->pdo->prepare(query: "SELECT * FROM imports WHERE id IN ($placeholders)");
    $stmt->execute(params: $ids);

    $result = [];
    foreach ($stmt->fetchAll(mode: PDO::FETCH_ASSOC) as $row) {
      $result[(int) $row['id']] = $this->hydrator->hydrateFromDatabase(row: $row);
    }
    return $result;
  }

  public function saveMany(array $imports): void
  {
    if (empty($imports)) {
      return;
    }

    $placeholders = implode(
      separator: ', ',
      array: array_fill(start_index: 0, count: count($imports), value: '(?, ?, ?, ?, ?, ?)')
    );
    $sql = "
      INSERT INTO imports
        (id, type, status, started_at, completed_at, count)
      VALUES
        $placeholders
      AS new_row
      ON DUPLICATE KEY UPDATE
        type         = new_row.type,
        status       = new_row.status,
        started_at   = new_row.started_at,
        completed_at = new_row.completed_at,
        count        = new_row.count
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
        $import->count,
      );
    }

    $this->connection->pdo->prepare(query: $sql)->execute(params: $values);
  }

  public function deleteMany(array $imports): void
  {
    $this->deleteByIds(ids: array_map(callback: fn(Import $import) => $import->id, array: $imports));
  }

  public function deleteByIds(array $ids): void
  {
    if (empty($ids)) {
      return;
    }

    $placeholders = implode(
      separator: ', ',
      array: array_fill(start_index: 0, count: count($ids), value: '?')
    );
    $this->connection->pdo->prepare(query: "DELETE FROM imports WHERE id IN ($placeholders)")->execute(params: $ids);
  }

  public function findAll(): array
  {
    $stmt = $this->connection->pdo->query(query: 'SELECT * FROM imports ORDER BY started_at DESC');

    return array_map(
      callback: fn(array $row) => $this->hydrator->hydrateFromDatabase(row: $row),
      array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC)
    );
  }
}
