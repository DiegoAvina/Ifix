<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class PriceQuery
{
    public function apply(Builder $query): Builder
    {
        $min = request('min_price');
        $max = request('max_price');

        if ($min === null && $max === null) {
            return $query;
        }

        return $query->whereHas('variants.prices', function (Builder $priceQuery) use ($min, $max) {

            if ($min !== null && $min !== '') {
                $priceQuery->where('price', '>=', (int) ($min * 100));
            }

            if ($max !== null && $max !== '') {
                $priceQuery->where('price', '<=', (int) ($max * 100));
            }

        });
    }

    
}