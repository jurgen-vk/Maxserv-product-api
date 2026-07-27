<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Entity\Brand;
use MaxServ\Core\Database\Connection;
use PDO;

final readonly class BrandRepository
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function findOrCreateOneByName(string $name): Brand
    {
        $stmt = $this->connection->pdo->prepare(
            query: '
        INSERT INTO brands (name) 
        VALUES (:name) 
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
      ',
        );
        $stmt->execute(params: [':name' => $name]);

        return new Brand(id: (int)$this->connection->pdo->lastInsertId(), name: $name);
    }

    public function find(int $id): ?Brand
    {
        $stmt = $this->connection->pdo->prepare(
            query: 'SELECT * FROM brands WHERE id = :id',
        );
        $stmt->execute(params: [':id' => $id]);
        $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);

        return $row !== false
            ? new Brand(id: (int)$row['id'], name: $row['name'])
            : null;
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
            query: "SELECT * FROM brands WHERE id IN ($placeholders)",
        );
        $stmt->execute(params: $ids);

        $result = [];
        foreach ($stmt->fetchAll(mode: PDO::FETCH_ASSOC) as $row) {
            $result[] = new Brand(id: (int)$row['id'], name: $row['name']);
        }
        return $result;
    }

    public function findAll(): array
    {
        $stmt = $this->connection->pdo->query(
            query: 'SELECT * FROM brands ORDER BY name',
        );

        return array_map(
            callback: fn(array $row) => new Brand(id: (int)$row['id'], name: $row['name']),
            array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC),
        );
    }
}
