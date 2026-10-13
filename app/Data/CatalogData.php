<?php

namespace App\Data;
use App\DTOs\Storefront\ProductCollectionData;

final readonly class CatalogData
{
    /**
     * 
     */
    public function __construct(
        public ProductCollectionData $products,
        public array $filters,
        public array $sort,
        public ?string $search,
    ) {}
    
}