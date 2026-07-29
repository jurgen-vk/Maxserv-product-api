<?php

declare(strict_types=1);

namespace MaxServ\App\Dto\Product;

final readonly class ProductPriceRange
{
    public function __construct(
        public ?float $min,
        public ?float $max,
    ) {}
}