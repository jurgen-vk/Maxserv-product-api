<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

class Product
{
    public float $discountPrice {
        get => $this->price * (1 - $this->discountPercentage / 100);
    }

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
        public array $media = [],
    ) {}

}
