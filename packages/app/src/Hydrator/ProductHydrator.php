<?php

declare(strict_types=1);

namespace MaxServ\App\Hydrator;

use MaxServ\App\Entity\Brand;
use MaxServ\App\Entity\Category;
use MaxServ\App\Entity\Product;

class ProductHydrator
{
    public function hydrateFromDatabase(
        array $row,
        Category $category,
        ?Brand $brand,
        array $media = [],
    ): Product {
        return new Product(
            id: (int)$row['id'],
            title: $row['title'],
            description: $row['description'],
            price: (float)$row['price'],
            discountPercentage: (float)$row['discount_percentage'],
            rating: (float)$row['rating'],
            stock: (int)$row['stock'],
            category: $category,
            brand: $brand,
            media: $media,
        );
    }

    public function hydrateFromApi(array $data, Category $category, ?Brand $brand, array $media = []): Product
    {
        return new Product(
            id: isset($data['id']) ? (int)$data['id'] : null,
            title: $data['title'],
            description: $data['description'] ?? '',
            price: (float)$data['price'],
            discountPercentage: (float)($data['discountPercentage'] ?? 0),
            rating: (float)($data['rating'] ?? 0),
            stock: (int)($data['stock'] ?? 0),
            category: $category,
            brand: $brand,
            media: $media,
        );
    }
}
