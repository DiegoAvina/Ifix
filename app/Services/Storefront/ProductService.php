<?php

namespace App\Services\Storefront;

use Lunar\Models\Collection;
use App\Support\LunarAttribute;
use App\Data\ProductData;
use App\Data\BrandData;
use Illuminate\Database\Eloquent\Builder;
use App\DTOs\Storefront\ProductCollectionData;
use Lunar\Models\Product;

class ProductService
{
    public function __construct(
        protected ProductImageService $images,
        protected ProductPriceService $prices,
        protected ProductInventoryService $inventory,
        protected ProductBadgeService $badges,
    ) {}

    public function featured(): array
    {
        $collection = Collection::with([
            'products.brand',
            'products.variants.prices',
            'products.media',
        ])->find(config('storefront.featured_collection_id'));

        if (! $collection) {
            return [];
        }

        return $collection->products
            ->map(fn ($product) => $this->map($product))
            ->toArray();
    }

    public function map($product, array $relatedProducts = []): ProductData
    {
        $variant = $product->variants->first();

        return new ProductData(

            id: $product->id,

            name: LunarAttribute::text(
                $product->attribute_data,
                'name'
            ),

            slug: $product->id,

            brand: new BrandData(
                id: $product->brand?->id,
                name: $product->brand?->name,
            ),

            price: $this->prices->build($variant),

            inventory: $this->inventory->build($variant),

            // NUEVO
            variantId: $variant?->id,

            image: $this->images->primary($product),

            gallery: $this->images->gallery($product),

            badges: $this->badges->build(
                $product,
                $variant
            ),

            description: LunarAttribute::text(
                $product->attribute_data,
                'description'
            ),

            specifications: [],

            relatedProducts: $relatedProducts,

            url: route(
                'catalog.show',
                $product->id
            ),

            // Lo dejamos como estaba
            addToCartUrl: url('/carrito/items'),

        );
    }

    /**
     * @return ProductData[]
     */
    public function all(Builder $query): ProductCollectionData
    {
        $paginator = $query
            ->paginate(12)
            ->withQueryString();

        return new ProductCollectionData(
            items: $paginator
                ->getCollection()
                ->map(fn (Product $product) => $this->map($product))
                ->toArray(),

            paginator: $paginator,
        );
    }

    public function find(int $id): ProductData
    {
        $product = Product::query()
            ->with([
                'brand',
                'variants.prices',
                'media',
                'collections',
            ])
            ->findOrFail($id);

        return $this->map(
            $product,
            $this->related($product)
        );
    }

    /**
     * Productos relacionados con el actual.
     *
     * Prioriza productos de la misma marca (la señal más fuerte
     * de relación) y completa el resto con productos de las
     * mismas colecciones si hacen falta más resultados.
     *
     * @return ProductData[]
     */
    public function related(Product $product, int $limit = 4): array
    {
        $base = fn () => Product::query()
            ->with([
                'brand',
                'variants.prices',
                'media',
            ])
            ->where('status', 'published')
            ->where('id', '!=', $product->id);

        $byBrand = $product->brand_id
            ? $base()->where('brand_id', $product->brand_id)
                ->inRandomOrder()
                ->limit($limit)
                ->get()
            : collect();

        $related = $byBrand;

        $collectionIds = $product->collections->pluck('id');

        if ($related->count() < $limit && $collectionIds->isNotEmpty()) {

            $excludedIds = $related->pluck('id')->push($product->id);

            $byCollection = $base()
                ->whereNotIn('id', $excludedIds)
                ->whereHas('collections', function (Builder $q) use ($collectionIds) {
                    $q->whereIn('lunar_collections.id', $collectionIds);
                })
                ->inRandomOrder()
                ->limit($limit - $related->count())
                ->get();

            $related = $related->concat($byCollection);
        }

        return $related
            ->map(fn (Product $related) => $this->map($related))
            ->toArray();
    }
}