<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class SearchQuery
{
    public function apply(Builder $query): Builder
    {
        $search = trim((string) request('q'));

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {

            $q->where('name', 'like', "%{$search}%")

              ->orWhereHas('variants', function (Builder $variant) use ($search) {

                    $variant->where('sku', 'like', "%{$search}%");

              })

              ->orWhereHas('brand', function (Builder $brand) use ($search) {

                    $brand->where('name', 'like', "%{$search}%");

              });

        });
    }
}