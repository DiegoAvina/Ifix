<?php

namespace App\Services\Storefront\Search\Queries;

use App\Services\Storefront\Search\FuzzyProductMatcher;
use Illuminate\Support\Collection;
use Lunar\Models\Product;

class ProductSuggestionQuery
{
    public function __construct(
        protected FuzzyProductMatcher $matcher,
    ) {}

    public function search(string $search, int $limit = 6): Collection
    {
        $search = mb_substr(trim($search), 0, 100);

        if ($search === '') {
            return collect();
        }

        $ids = $this->matcher->match($search, $limit);

        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->with([
                'brand',
                'variants.prices',
                'media',
                'defaultUrl',
            ])
            ->where('status', 'published')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
            ->values();
    }
}
