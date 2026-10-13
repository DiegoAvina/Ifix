<?php

namespace App\Services\Storefront\Search\Queries;

use App\Services\Storefront\Catalog\Attributes\AttributeQueryBuilder;
use Illuminate\Support\Collection;
use Lunar\Models\Product;

class ProductSuggestionQuery
{
    public function __construct(
        protected AttributeQueryBuilder $attributes,
    ) {}

    public function search(string $search, int $limit = 6): Collection
    {
        $search = trim($search);

        if ($search === '') {
            return collect();
        }

        return Product::query()
            ->with([
                'brand',
                'variants.prices',
                'media',
            ])
            ->where('status', 'published')
            ->where(function ($query) use ($search) {

                $this->attributes
                    ->for($query)
                    ->locale('es')
                    ->contains('name', $search);

                $query->orWhereHas('variants', function ($variant) use ($search) {
                    $variant->where('sku', 'like', "%{$search}%");
                });

            })
            ->limit($limit)
            ->get();
    }
}