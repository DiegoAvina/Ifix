<?php

namespace App\Services\Storefront\Catalog;

use Illuminate\Http\Request;

class UrlGenerator
{
    public function __construct(
        protected Request $request,
    ) {}

    /**
     * Agrega o elimina un valor de un filtro manteniendo el resto
     * de los parámetros de la URL.
     */
    public function toggleFilter(string $filter, string|int $value): string
    {
        $query = $this->request->query();

        $values = $query[$filter] ?? [];

        if (! is_array($values)) {
            $values = [$values];
        }

        $values = array_map('strval', $values);

        $value = (string) $value;

        if (in_array($value, $values, true)) {
            $values = array_values(
                array_filter(
                    $values,
                    fn ($v) => $v !== $value
                )
            );
        } else {
            $values[] = $value;
        }

        if (empty($values)) {
            unset($query[$filter]);
        } else {
            $query[$filter] = $values;
        }

        return url()->current() . (empty($query) ? '' : '?' . http_build_query($query));
    }
}