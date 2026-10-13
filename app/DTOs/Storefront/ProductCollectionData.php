<?php

namespace App\DTOs\Storefront;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductCollectionData
{
    /**
     * @param ProductData[] $items
     */
    public function __construct(
        public array $items,
        public LengthAwarePaginator $paginator,
    ) {}
}