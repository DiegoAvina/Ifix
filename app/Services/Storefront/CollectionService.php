<?php

namespace App\Services\Storefront;

use Lunar\Models\Collection;
use App\Support\LunarAttribute;

class CollectionService
{
    public function all(): array
    {
        return Collection::query()
            ->with([
                'products.brand',
                'products.variants.prices',
                'products.media',
            ])
            ->get()
            ->map(fn ($collection) => [
                'id' => $collection->id,

                'name' => LunarAttribute::text(
                    $collection->attribute_data,
                    'name'
                ),

                'products' => $collection->products
                    ->map(fn ($product) => app(ProductService::class)
                        ->map($product))
                    ->toArray(),

            ])
            ->toArray();
    }
}