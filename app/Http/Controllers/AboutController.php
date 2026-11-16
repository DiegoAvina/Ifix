<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Services\Storefront\BrandService;
use App\Services\Storefront\CategoryService;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Illuminate\View\View;
use Lunar\Models\Product;

class AboutController extends Controller
{
    public function __invoke(
        CategoryService $categories,
        BrandService $brands,
        StorefrontAvailabilityQuery $availability,
    ): View {
        $page = AboutPage::current();

        abort_unless($page->published, 404);

        $categoryList = $categories->all();
        $brandList = $brands->all();

        return view('about.show', [
            'page' => $page,
            'values' => $page->valueItems(),
            'process' => $page->processItems($categoryList),
            'stats' => $page->statItems(),
            'milestones' => $page->milestoneItems(),
            // Datos reales de la tienda: cambian solos al editar el catálogo en Lunar.
            'brands' => $brandList,
            'shelf' => [
                'products' => $availability->apply(Product::query()->where('status', 'published'))->count(),
                'categories' => count($categoryList),
                'brands' => count($brandList),
            ],
        ]);
    }
}
