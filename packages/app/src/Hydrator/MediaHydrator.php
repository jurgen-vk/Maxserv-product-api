<?php

declare(strict_types=1);

namespace MaxServ\App\Hydrator;

use MaxServ\App\Entity\Media;
use MaxServ\App\Enum\MediaRole;
use MaxServ\App\Enum\MediaType;

final readonly class MediaHydrator
{
    public function hydrateFromDatabase(array $row): Media
    {
        return new Media(
            id: (int)$row['id'],
            entityType: $row['entity_type'],
            entityId: (int)$row['entity_id'],
            url: $row['url'],
            mediaType: MediaType::from($row['media_type']),
            sortOrder: (int)$row['sort_order'],
            role: $row['role'] !== null ? MediaRole::from($row['role']) : null,
        );
    }

    public function hydrateManyFromApi(?string $thumbnail, array $images, string $entityType, int $entityId): array
    {
        $items = [];
        $order = 0;

        if ($thumbnail !== null && $thumbnail !== '') {
            $items[] = new Media(
                id: null,
                entityType: $entityType,
                entityId: $entityId,
                url: $thumbnail,
                mediaType: MediaType::Image,
                sortOrder: $order++,
                role: MediaRole::Thumbnail,
            );
        }

        foreach (array_values(array: array_filter(array: $images)) as $url) {
            $items[] = new Media(
                id: null,
                entityType: $entityType,
                entityId: $entityId,
                url: $url,
                mediaType: MediaType::Image,
                sortOrder: $order++,
            );
        }

        return $items;
    }
}
