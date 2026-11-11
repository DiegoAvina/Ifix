<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;

class AvailabilityQuery
{
    public function apply(Builder $query): Builder
    {
        $available = (string) request('availability');

        // Solo "1" (disponible) o "0" (agotado); vacío o cualquier otro valor no filtra.
        if (! in_array($available, ['0', '1'], true)) {
            return $query;
        }

        $available = $available === '1';

        return $query->whereHas('variants', function ($q) use ($available) {

            if ($available) {

                $q->where('stock', '>', 0);

            } else {

                $q->where('stock', 0);

            }

        });
    }
}