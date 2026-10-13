<?php

namespace App\Data\Search;

final readonly class ProductSuggestionData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $sku,
        public ?string $image,
        public ?string $brand,
        public string $price,
        public bool $inStock,
        public string $url,
    ) {}
}