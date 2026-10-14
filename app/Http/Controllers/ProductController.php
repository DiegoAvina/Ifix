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