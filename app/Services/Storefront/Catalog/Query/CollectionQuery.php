<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class CollectionQuery
{
    public function apply(Builder $query): Builder
    {
        $collection = request('collection');

        if (! $collection) {
            return $query;
        }

        return $query->whereHas('collections', function ($q) use ($collection) {

            $q->whereIn('lunar_collections.id', (array) $collection);

        });
    }
}