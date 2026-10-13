<?php

namespace App\Http\Controllers;

use App\Services\Storefront\ProductService;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function __invoke(
        ProductService $products,
        Product $product,
    ) {
        $product->load([
            'brand',
            'variants.prices',
            'media',
            'collections',
        ]);

        return view('products.show', [
            'product' => $products->map(
                $product,
                $products->related($product)
            ),
        ]);
    }
}