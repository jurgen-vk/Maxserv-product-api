<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Entity\Category;
use MaxServ\Core\Database\Connection;
use PDO;

class CategoryRepository
{
  public function __construct(
    private readonly Connection $connection,
  ) {
  }

  public function findOrCreateOneByName(string $name): Category
  {
    $stmt = $this->connection->pdo->prepare(
      query: 'INSERT INTO categories (name) VALUES (:name) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
    );
    $stmt->execute(params: [':name' => $name]);

    return new Category(id: (int) $this->connection->pdo->lastInsertId(), name: $name);
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
    $stmt = $this->connection->pdo->prepare(query: "SELECT * FROM categories WHERE id IN ($placeholders)");
    $stmt->execute(params: $ids);

    $result = [];
    foreach ($stmt->fetchAll(mode: PDO::FETCH_ASSOC) as $row) {
      $result[(int) $row['id']] = new Category(id: (int) $row['id'], name: $row['name']);
    }
    return $result;
  }

  public function findAll(): array
  {
    $stmt = $this->connection->pdo->query(query: 'SELECT * FROM categories ORDER BY name');

    return array_map(
      callback: fn(array $row) => new Category(id: (int) $row['id'], name: $row['name']),
      array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC)
    );
  }
}
