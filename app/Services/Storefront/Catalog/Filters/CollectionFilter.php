<?php

namespace App\Services\Storefront\Catalog\Filters;

use App\Data\FilterData;
use App\Data\FilterOptionData;
use App\Services\Storefront\Catalog\UrlGenerator;
use App\Services\Storefront\CategoryService;

class CollectionFilter
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
        protected CategoryService $categories,
    ) {}

    public function build(): FilterData
    {
        $selectedCollections = request()->input('collection', []);

        if (! is_array($selectedCollections)) {
            $selectedCollections = [$selectedCollections];
        }

        $selectedCollections = array_map('strval', $selectedCollections);

        $options = array_map(fn (array $category) => new FilterOptionData(
            value: $category['id'],
            label: $category['name'],
            count: $category['count'],
            selected: in_array((string) $category['id'], $selectedCollections, true),
            url: $this->urlGenerator->toggleFilter('collection', $category['id']),
        ), $this->categories->all());

        return new FilterData(
            key: 'collection',
            title: 'Categorías',
            options: $options,
        );
    }
}
