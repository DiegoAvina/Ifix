<?php

namespace App\Services\Storefront\Catalog;

use App\Data\SortOptionData;

class CatalogSortService
{
    /**
     * @return SortOptionData[]
     */
    public function options(): array
    {
        $current = request('sort', 'newest');

        return [

            new SortOptionData(
                value: 'newest',
                label: 'Más recientes',
                selected: $current === 'newest',
            ),

            new SortOptionData(
                value: 'price_asc',
                label: 'Precio: menor a mayor',
                selected: $current === 'price_asc',
            ),

            new SortOptionData(
                value: 'price_desc',
                label: 'Precio: mayor a menor',
                selected: $current === 'price_desc',
            ),

            new SortOptionData(
                value: 'name_asc',
                label: 'Nombre A-Z',
                selected: $current === 'name_asc',
            ),

            new SortOptionData(
                value: 'name_desc',
                label: 'Nombre Z-A',
                selected: $current === 'name_desc',
            ),

        ];
    }
}