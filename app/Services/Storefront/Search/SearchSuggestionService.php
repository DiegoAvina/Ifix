<?php

namespace App\Services\Storefront\Search;

use App\Data\Search\ProductSuggestionData;
use App\Services\Storefront\Search\Queries\ProductSuggestionQuery;
use App\Support\LunarAttribute;

class SearchSuggestionService
{
    public function __construct(
        protected ProductSuggestionQuery $products,
    ) {}

    public function search(string $search): array
    {
        return $this->products
            ->search($search)
            ->map(function ($product) {

                $variant = $product->variants->first();

                return new ProductSuggestionData(
                    id: $product->id,

                    name: LunarAttribute::text(
                        $product->attribute_data,
                        'name'
                    ),

                    sku: $variant?->sku ?? '',

                    image: $product->thumbnail?->getUrl(),

                    brand: $product->brand?->name,

                    price: $variant?->prices->first()?->price?->formatted() ?? '',

                    inStock: true,

                    url: route(
                        '#',
                        $product
                    ),
                );

            })
            ->values()
            ->all();
    }
}