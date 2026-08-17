<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

final class Product
{
    public function __construct(
        public string $title,
        public string $description,
        public float $price,
        public float $discountPercentage,
        public float $rating,
        public int $stock,
        public Category $category,
        public ?Brand $brand,
        public ?int $id = null,
        public ?Media $thumbnail = null,
        public array $media = [],
        public ?float $discountPrice = null,
    ) {}
}
