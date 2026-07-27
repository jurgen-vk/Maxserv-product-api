<?php

declare(strict_types=1);

namespace MaxServ\App\Hydrator;

use MaxServ\App\Entity\Media;
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
        );
    }

    public function hydrateManyFromApi(?string $thumbnail, array $images, string $entityType, int $entityId): array
    {
        $urls = array_values(array: array_filter(array: [$thumbnail, ...$images]));

        return array_map(
            fn(string $url, int $order) => new Media(
                id: null,
                entityType: $entityType,
                entityId: $entityId,
                url: $url,
                mediaType: MediaType::Image,
                sortOrder: $order,
            ),
            $urls,
            array_keys(array: $urls),
        );
    }
}
