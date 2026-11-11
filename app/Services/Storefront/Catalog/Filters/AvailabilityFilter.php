<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\FilterOptionData;
use App\Services\Storefront\Catalog\UrlGenerator;

class AvailabilityFilter
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {}

    public function build(): FilterData
    {
        // Comparación como texto: con `== 0`, PHP daba "Agotado" por
        // marcado cuando no había filtro (null == 0 es verdadero).
        $selected = (string) request('availability');

        return new FilterData(

            key: 'availability',

            title: 'Disponibilidad',

            options: [

                new FilterOptionData(
                    value: 1,
                    label: 'Disponible',
                    count: 0,
                    selected: $selected === '1',
                    url: $this->urlGenerator->toggleFilter('availability', 1),
                ),

                new FilterOptionData(
                    value: 0,
                    label: 'Agotado',
                    count: 0,
                    selected: $selected === '0',
                    url: $this->urlGenerator->toggleFilter('availability', 0),
                ),

            ],

        );
    }
}