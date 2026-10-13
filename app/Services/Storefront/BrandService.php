<?php

namespace App\Services\Storefront;

use Lunar\Models\Brand;

class BrandService
{
    public function all(): array
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
}