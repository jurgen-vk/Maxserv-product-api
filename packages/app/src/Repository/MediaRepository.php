<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Entity\Media;
use MaxServ\App\Hydrator\MediaHydrator;
use MaxServ\App\Utility\QueryUtility;
use MaxServ\Core\Database\Connection;
use PDO;

final readonly class MediaRepository
{
    public function __construct(
        private Connection $connection,
        private MediaHydrator $hydrator,
    ) {}

    public function save(Media $media): void
    {
        $sql = '
            INSERT INTO media
                (id, entity_type, entity_id, url, media_type, sort_order)
            VALUES
                (:id, :entityType, :entityId, :url, :mediaType, :sortOrder)
            AS new_row
            ON DUPLICATE KEY UPDATE
                entity_type = new_row.entity_type,
                entity_id   = new_row.entity_id,
                url         = new_row.url,
                media_type  = new_row.media_type,
                sort_order  = new_row.sort_order
        ';

        $this->connection->pdo->prepare(query: $sql)->execute(params: [
            ':id' => $media->id,
            ':entityType' => $media->entityType,
            ':entityId' => $media->entityId,
            ':url' => $media->url,
            ':mediaType' => $media->mediaType->value,
            ':sortOrder' => $media->sortOrder,
        ]);

        $media->id ??= (int)$this->connection->pdo->lastInsertId();
    }

    public function saveMany(array $media): void
    {
        if (empty($media)) {
            return;
        }

        $placeholders = QueryUtility::buildPositionalPlaceholders(
            rowCount: count($media),
            columnCount: 6,
        );

        $sql = "
            INSERT INTO media
                (id, entity_type, entity_id, url, media_type, sort_order)
            VALUES
                $placeholders
            AS new_row
            ON DUPLICATE KEY UPDATE
                entity_type = new_row.entity_type,
                entity_id   = new_row.entity_id,
                url         = new_row.url,
                media_type  = new_row.media_type,
                sort_order  = new_row.sort_order
        ";

        $values = [];
        foreach ($media as $m) {
            array_push(
                $values,
                $m->id,
                $m->entityType,
                $m->entityId,
                $m->url,
                $m->mediaType->value,
                $m->sortOrder,
            );
        }

        $this->connection->pdo->prepare(query: $sql)->execute(params: $values);
    }

    public function deleteByEntities(string $entityType, array $entityIds): void
    {
        if (empty($entityIds)) {
            return;
        }

        $placeholders = implode(
            separator: ', ',
            array: array_fill(start_index: 0, count: count($entityIds), value: '?'),
        );
        $this->connection->pdo->prepare(
            query: "
              DELETE FROM media 
              WHERE entity_type = ? 
              AND entity_id 
              IN ($placeholders)
            ",
        )->execute(params: [$entityType, ...$entityIds]);
    }

    public function findByEntity(string $entityType, int $entityId): array
    {
        $stmt = $this->connection->pdo->prepare(
            query: '
              SELECT * FROM media 
              WHERE entity_type = :entityType 
              AND entity_id = :entityId 
              ORDER BY sort_order
            ',
        );
        $stmt->execute(params: [':entityType' => $entityType, ':entityId' => $entityId]);

        return array_map(
            callback: fn(array $row) => $this->hydrator->hydrateFromDatabase(row: $row),
            array: $stmt->fetchAll(mode: PDO::FETCH_ASSOC),
        );
    }

    public function findFirstByEntities(string $entityType, array $entityIds): array
    {
        if (empty($entityIds)) {
            return [];
        }

        [$placeholders, $bindings] = QueryUtility::buildNamedPlaceholders(prefix: 'entityId', values: $entityIds);

        $stmt = $this->connection->pdo->prepare(
            query: "
              SELECT * FROM (
                SELECT *, ROW_NUMBER() OVER (PARTITION BY entity_id ORDER BY sort_order) AS rn
                FROM media
                WHERE entity_type = :entityType AND entity_id IN ($placeholders)
              ) ranked
              WHERE rn = 1
            ",
        );
        $stmt->execute(params: [':entityType' => $entityType, ...$bindings]);

        $media = [];
        foreach ($stmt->fetchAll(mode: PDO::FETCH_ASSOC) as $row) {
            $media[] = $this->hydrator->hydrateFromDatabase(row: $row);
        }

        return $media;
    }
}