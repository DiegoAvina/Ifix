<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\PriceFilterData;
use Lunar\Models\Price;

class PriceFilter
{
    public function build(): FilterData
    {
        $min = (Price::min('price') ?? 0) / 100;
        $max = (Price::max('price') ?? 0) / 100;

        return new FilterData(

            key: 'price',

            title: 'Precio',

            extra: new PriceFilterData(

                min: $min,

                max: $max,

                selectedMin: request('min_price', $min),

                selectedMax: request('max_price', $max),

            ),

        );
    }
}