<?php

namespace App\Services\Storefront\Catalog;

use App\Data\CatalogData;
use App\Services\Storefront\ProductService;
use App\Services\Storefront\Catalog\Query\ProductQueryService;

class CatalogService
{
    public function __construct(
        protected ProductService $products,
        protected ProductQueryService $productQuery,
        protected CatalogFilterService $filters,
        protected CatalogSortService $sort,
    ) {}

    public function data(): CatalogData
    {
        $query = $this->productQuery->build();

        return new CatalogData(
            products: $this->products->all($query),
            filters: $this->filters->build(),
            sort: $this->sort->options(),
            search: request('q'),
        );
    }
}