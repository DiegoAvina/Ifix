<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\FilterOptionData;
use App\Services\Storefront\Catalog\UrlGenerator;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Brand;

class BrandFilter
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
        protected StorefrontAvailabilityQuery $storefrontAvailability,
    ) {}

    public function build(): FilterData
    {
        $selectedBrands = request()->input('brand', []);

        if (! is_array($selectedBrands)) {
            $selectedBrands = [$selectedBrands];
        }

        $selectedBrands = array_map('strval', $selectedBrands);

        // Solo cuenta lo que el cliente puede ver: sin borradores ni productos
        // fuera del canal, igual que el filtro de categorías. Una marca que
        // solo tiene borradores no aparece (daría 0 resultados al elegirla).
        $visible = fn (Builder $products) => $this->storefrontAvailability
            ->apply($products->where('status', 'published'));

        $brands = Brand::query()
            ->withCount(['products' => $visible])
            ->whereHas('products', $visible)
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