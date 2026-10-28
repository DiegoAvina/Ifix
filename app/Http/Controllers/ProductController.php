<?php

namespace App\Http\Controllers;

use App\Services\Storefront\ProductService;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function __invoke(
        ProductService $products,
        StorefrontAvailabilityQuery $availability,
        Product $product,
    ) {
        abort_unless(
            $product->status === 'published'
                && $availability->apply(Product::query()->whereKey($product->id))->exists(),
            404,
        );

        // Una sola URL por producto: /productos/{id} redirige al slug.
        $slug = $product->defaultUrl?->slug;

        if ($slug && request()->route()->originalParameter('product') !== $slug) {
            return redirect()->route('catalog.show', $slug, 301);
        }

        $product->load([
            'brand',
            'variants.prices',
            'variants.values.option',
            'media',
            'collections',
            'defaultUrl',
        ]);

        return view('products.show', [
            'product' => $products->map(
                $product,
                $products->related($product)
            ),
        ]);
    }
}