<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\FilterOptionData;
use App\Services\Storefront\Catalog\UrlGenerator;
use Lunar\Models\Brand;

class BrandFilter
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {}

    public function build(): FilterData
    {
        $selectedBrands = request()->input('brand', []);

        if (! is_array($selectedBrands)) {
            $selectedBrands = [$selectedBrands];
        }

        $selectedBrands = array_map('strval', $selectedBrands);

        $brands = Brand::query()
            ->withCount('products')
            ->whereHas('products')
            ->orderBy('name')
            ->get();

        $options = $brands->map(function ($brand) use ($selectedBrands) {

            return new FilterOptionData(
                value: $brand->id,
                label: $brand->name,
                count: $brand->products_count,
                selected: in_array((string) $brand->id, $selectedBrands, true),
                url: $this->urlGenerator->toggleFilter('brand', $brand->id),
            );

        })->toArray();

        return new FilterData(
            key: 'brand',
            title: 'Marcas',
            options: $options,
        );
    }
}