<?php

namespace App\Services;

use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Product;

class StorefrontService
{
    /**
     * Marcas
     */
    public function brands(): array
    {
        return Brand::query()

            ->orderBy('name')

            ->get()

            ->map(function ($brand) {

                return [

                    'id' => $brand->id,

                    'name' => $brand->name,

                ];
            })

            ->toArray();
    }
    /**
     * Productos Destacados
     */
    public function featuredProducts(): array
    {
        return [];
    }

    /**
     * Colecciones
     */
    public function collections(): array
    {
        return [

    'featured_collection_slug' => 'destacados',

];
    }
}
