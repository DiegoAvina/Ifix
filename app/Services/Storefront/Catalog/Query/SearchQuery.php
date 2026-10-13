<?php

namespace App\Services\Storefront\Catalog\Query;

use App\Services\Storefront\Catalog\Attributes\AttributeQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class SearchQuery
{

    
    public function __construct(
        protected AttributeQueryBuilder $attributes,
    ) {}

    public function apply(Builder $query): Builder
    {
        $search = trim((string) request('search'));

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($search) {

            $this->attributes
                ->for($builder)
                ->locale('es')
                ->contains('name', $search);

            $builder->orWhereHas('variants', function (Builder $variant) use ($search) {
                $variant->where('sku', 'like', "%{$search}%");
            });

            $builder->orWhereHas('brand', function (Builder $brand) use ($search) {
                $brand->where('name', 'like', "%{$search}%");
            });
        });
    }
    
}
