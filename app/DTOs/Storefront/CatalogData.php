<?php

namespace App\DTOs\Storefront;

class CatalogData
{
    /**
     * @param FilterData[] $filters
     * @param SortOptionData[] $sort
     */
    public function __construct(
        public ProductCollectionData $products,
        public array $filters,
        public array $sort,
        public ?string $search,
    ) {}
}