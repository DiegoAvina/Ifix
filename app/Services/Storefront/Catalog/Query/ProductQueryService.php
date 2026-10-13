<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Product;


class ProductQueryService
{
    public function __construct(
        protected BrandQuery $brand,
        protected CollectionQuery $collection,
        protected AvailabilityQuery $availability,
        protected PriceQuery $price,
        protected SearchQuery $search,
        protected SortQuery $sort,

    ) {}

    public function build(): Builder
    {
        $query = Product::query()
            ->with([
                'brand',
                'variants.prices',
                'media',
            ])
            ->where('status', 'published');

        $query = $this->brand->apply($query);
        $query = $this->collection->apply($query);
        $query = $this->availability->apply($query);
        $query = $this->price->apply($query);
        $query = $this->search->apply($query);  
        $query = $this->sort->apply($query);

        return $query;
    }
    
}