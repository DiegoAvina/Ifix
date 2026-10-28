<?php

namespace App\Services\Storefront\Catalog\Query;

use App\Services\Storefront\Catalog\Attributes\AttributeQueryBuilder;
use App\Services\Storefront\Search\FuzzyProductMatcher;
use Illuminate\Database\Eloquent\Builder;

class SearchQuery
{


    public function __construct(
        protected AttributeQueryBuilder $attributes,
        protected FuzzyProductMatcher $matcher,
    ) {}

    public function apply(Builder $query): Builder
    {
        // ?search[]=x llegaría como array; y se acota el largo del LIKE.
        $search = request('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        if ($search === '') {
            return $query;
        }

        // Coincidencias aproximadas (errores de escritura) además de las exactas.
        $fuzzyIds = $this->matcher->match($search);

        return $query->where(function (Builder $builder) use ($search, $fuzzyIds) {

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

            if ($fuzzyIds !== []) {
                $builder->orWhereIn($builder->getModel()->getQualifiedKeyName(), $fuzzyIds);
            }
        });
    }

}
