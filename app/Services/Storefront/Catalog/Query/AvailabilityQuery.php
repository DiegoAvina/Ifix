<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class AvailabilityQuery
{
    public function apply(Builder $query): Builder
    {
        $available = request('availability');

        if ($available === null) {
            return $query;
        }

        return $query->whereHas('variants', function ($q) use ($available) {

            if ($available) {

                $q->where('stock', '>', 0);

            } else {

                $q->where('stock', 0);

            }

        });
    }
}