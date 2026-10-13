<?php

namespace App\Http\Controllers;

use App\Services\Storefront\Catalog\CatalogService;

class CatalogController extends Controller
{
    public function index(CatalogService $catalog)
    {
        return view(
            'catalog.index',
            [
                'catalog' => $catalog->data(),
            ]
        );
    }
}