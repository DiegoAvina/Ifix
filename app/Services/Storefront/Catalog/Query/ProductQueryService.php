<?php

namespace App\Services\Storefront\Catalog\Query;

use App\Services\Storefront\StorefrontAvailabilityQuery;
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
        protected StorefrontAvailabilityQuery $storefrontAvailability,

    ) {}

    public function build(): Builder
    {
        $query = Product::query()
            ->with([
                'brand',
                'variants.prices',
                'variants.values.option',
                'media',
                'defaultUrl',
            ])
            ->where('status', 'published');
        $query = $this->storefrontAvailability->apply($query);

        $query = $this->brand->apply($query);
        $query = $this->collection->apply($query);
        $query = $this->availability->apply($query);
        $query = $this->price->apply($query);
        $query = $this->search->apply($query);  
        $query = $this->sort->apply($query);

        return $query;
    }
    
}