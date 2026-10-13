<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\FilterOptionData;
use App\Services\Storefront\Catalog\UrlGenerator;
use App\Support\LunarAttribute;
use Lunar\Models\Collection;

class CollectionFilter
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {}

    public function build(): FilterData
    {
        $selectedCollections = request()->input('collection', []);

        if (! is_array($selectedCollections)) {
            $selectedCollections = [$selectedCollections];
        }

        $selectedCollections = array_map('strval', $selectedCollections);

        $collections = Collection::query()
            ->withCount('products')
            ->whereHas('products')
            ->get()
            ->sortBy(fn ($collection) => LunarAttribute::text(
                $collection->attribute_data,
                'name'
            ))
            ->values();

        $options = $collections->map(function ($collection) use ($selectedCollections) {

            $name = LunarAttribute::text(
                $collection->attribute_data,
                'name'
            );

            return new FilterOptionData(
                value: $collection->id,
                label: $name,
                count: $collection->products_count,
                selected: in_array((string) $collection->id, $selectedCollections, true),
                url: $this->urlGenerator->toggleFilter('collection', $collection->id),
            );

        })->toArray();

        return new FilterData(
            key: 'collection',
            title: 'Colecciones',
            options: $options,
        );
    }
}