<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class SortQuery
{
    public function apply(Builder $query): Builder
    {
        return match (request('sort')) {

            'newest' => $query->latest(),

            'oldest' => $query->oldest(),

            'name_asc' => $query->orderBy('attribute_data->name->value->es'),

            'name_desc' => $query->orderByDesc('attribute_data->name->value->es'),

            default => $query->latest(),

        };
    }
}