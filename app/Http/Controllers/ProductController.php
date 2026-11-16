<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Services\Storefront\CategoryService;
use App\Services\Storefront\ProductService;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function __invoke(
        ProductService $products,
        StorefrontAvailabilityQuery $availability,
        CategoryService $categories,
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

        // Primera categoría visible del producto, para la ruta de navegación.
        $categoryList = $categories->all();
        $visibleCategories = collect($categoryList)->keyBy('id');
        $category = $product->collections
            ->sortBy('id')
            ->map(fn ($collection) => $visibleCategories->get($collection->id))
            ->filter()
            ->first();

        return view('products.show', [
            'product' => $products->map(
                $product,
                $products->related($product)
            ),
            'category' => $category,
            // Paso del proceso de detallado (se edita en el panel: Nosotros).
            'process' => AboutPage::current()->processFor($category, $categoryList),
        ]);
    }
}