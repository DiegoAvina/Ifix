<?php

namespace App\Services\Storefront;

class HomeService
{
    public function __construct(
        protected HeroService $hero,
        protected BrandService $brands,
        protected ProductService $products,
        protected CollectionService $collections,

    ) {}

    public function data(): array
    {
        return [

            'slides' => $this->hero->all(),

            'brands' => $this->brands->all(),

            'products' => $this->products->featured(),

            'collections' => $this->collections->all(),
        ];
    }

   
}
