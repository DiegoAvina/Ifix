<?php

namespace App\Services\Storefront\Catalog\Query;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

class SortQuery
{
    public function apply(Builder $query): Builder
    {
        return match (request('sort')) {

            'newest' => $query->latest(),

            'oldest' => $query->oldest(),

            'name_asc' => $query->orderBy('attribute_data->name->value->es'),

            'name_desc' => $query->orderByDesc('attribute_data->name->value->es'),

            'price_asc' => $this->orderByPrice($query, 'asc'),

            'price_desc' => $this->orderByPrice($query, 'desc'),

            default => $query->latest(),

        };
    }

    protected function orderByPrice(Builder $query, string $direction): Builder
    {
        $currency = Currency::query()->where('default', true)->first();

        if (! $currency) {
            return $query->latest();
        }

        $productTable = $query->getModel()->getTable();
        $variantTable = (new ProductVariant)->getTable();
        $priceTable = (new Price)->getTable();

        $price = Price::query()
            ->select("{$priceTable}.price")
            ->join($variantTable, "{$variantTable}.id", '=', "{$priceTable}.priceable_id")
            ->whereColumn("{$variantTable}.product_id", "{$productTable}.id")
            ->where("{$priceTable}.priceable_type", 'product_variant')
            ->where("{$priceTable}.currency_id", $currency->id)
            ->orderBy("{$variantTable}.id")
            ->orderBy("{$priceTable}.id")
            ->limit(1);

        return $query
            ->orderBy($price, $direction)
            ->orderBy("{$productTable}.id");
    }
}