<?php

namespace App\Services\Storefront\Catalog;

use App\Services\Storefront\Catalog\Filters\AvailabilityFilter;
use App\Services\Storefront\Catalog\Filters\BrandFilter;
use App\Services\Storefront\Catalog\Filters\CollectionFilter;
use App\Services\Storefront\Catalog\Filters\PriceFilter;

class CatalogFilterService
{
    public function __construct(

        protected BrandFilter $brands,

        protected CollectionFilter $collections,

        protected AvailabilityFilter $availability,

        protected PriceFilter $price,

    ) {
    }

    /**
     * Construye todos los filtros del catálogo.
     */
    public function build(): array
    {
        return [

            $this->brands->build(),

            $this->collections->build(),

            $this->availability->build(),

            $this->price->build(),

        ];
    }
}