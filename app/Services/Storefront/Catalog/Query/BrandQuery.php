<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class BrandQuery
{
    public function apply(Builder $query): Builder
    {
        $brands = request()->input('brand', []);

        if (! is_array($brands)) {
            $brands = [$brands];
        }

        $brands = array_filter($brands);

        if (empty($brands)) {
            return $query;
        }

        return $query->whereIn('brand_id', $brands);
    }
}