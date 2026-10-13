<?php

namespace App\Services\Storefront\Catalog\Attributes;

use Illuminate\Database\Eloquent\Builder;

class AttributeSearchService
{
    public function contains(
        Builder $query,
        string $attribute,
        string $locale,
        string $value
    ): Builder {

        return $query->where(
            "attribute_data->{$attribute}->value->{$locale}",
            'like',
            "%{$value}%"
        );
    }
}