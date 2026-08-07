<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

use MaxServ\App\Enum\MediaRole;
use MaxServ\App\Enum\MediaType;

final class Media
{
    public function __construct(
        public ?int $id,
        public string $entityType,
        public int $entityId,
        public string $url,
        public MediaType $mediaType,
        public int $sortOrder,
        public ?MediaRole $role = null,
    ) {}
}
