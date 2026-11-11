<?php

namespace App\Http\Controllers;

use App\Services\Storefront\Catalog\CatalogService;
use App\Services\Storefront\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, CatalogService $catalog, CategoryService $categories): View|RedirectResponse
    {
        // /productos?collection[]=3 (solo la categoría) es la misma página que
        // /categorias/pulimentos: se redirige para que Google indexe una sola URL.
        $query = collect($request->query())->except('page');
        $collection = $query->get('collection');

        if ($query->keys()->all() === ['collection'] && is_array($collection) && count($collection) === 1) {
            $category = $categories->find((int) $collection[0]);

            if ($category && $category['slug']) {
                return redirect()->to(
                    route('catalog.category', array_filter([$category['slug'], 'page' => $request->query('page')])),
                    301,
                );
            }
        }

        return view('catalog.index', [
            'catalog' => $catalog->data(),
            'categories' => $categories->all(),
            'category' => null,
        ]);
    }

    /**
     * Página de categoría indexable (/categorias/{slug}): el mismo catálogo
     * con la categoría fija y textos propios para buscadores.
     */
    public function category(Request $request, string $slug, CatalogService $catalog, CategoryService $categories): View
    {
        $category = $categories->findBySlug($slug);

        abort_unless($category, 404);

        // Los filtros leen la categoría de la query; se marca como fija para
        // que no aparezca en los enlaces de paginación.
        $request->query->set('collection', [(string) $category['id']]);
        $request->attributes->set('catalog_fixed_query', ['collection']);

        return view('catalog.index', [
            'catalog' => $catalog->data(),
            'categories' => $categories->all(),
            'category' => $category,
        ]);
    }
}
