<?php

namespace App\Services\Storefront;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Collection;
use App\Support\LunarAttribute;

class CollectionService
{
    public function __construct(
        protected StorefrontAvailabilityQuery $storefrontAvailability,
    ) {}

    public function all(): array
    {
        $products = fn (BelongsToMany $relation) => $this->storefrontAvailability
            ->apply($relation->getQuery()->where('status', 'published'));

        return $this->storefrontAvailability
            ->apply(Collection::query())
            ->with([
                'products' => $products,
                'products.brand',
                'products.variants.prices',
                'products.variants.values.option',
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