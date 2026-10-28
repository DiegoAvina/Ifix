<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normaliza los parámetros públicos del catálogo antes de que lleguen a
 * los filtros: ?search[]=x, ?min_price=abc y similares provocaban
 * errores 500 (y llenaban el log) en vez de simplemente ignorarse.
 */
class NormalizeCatalogQuery
{
    private const TEXT = ['search', 'sort'];

    private const NUMBER = ['min_price', 'max_price'];

    private const IDS = ['brand', 'collection'];

    public function handle(Request $request, Closure $next): Response
    {
        $query = $request->query();

        foreach (self::TEXT as $key) {
            if (array_key_exists($key, $query)) {
                $query[$key] = is_string($query[$key]) ? mb_substr($query[$key], 0, 100) : null;
            }
        }

        foreach (self::NUMBER as $key) {
            if (array_key_exists($key, $query)) {
                $query[$key] = is_string($query[$key]) && is_numeric($query[$key]) && $query[$key] >= 0
                    ? $query[$key]
                    : null;
            }
        }

        foreach (self::IDS as $key) {
            if (! array_key_exists($key, $query)) {
                continue;
            }

            $value = $query[$key];

            $query[$key] = is_array($value)
                ? array_values(array_filter($value, fn ($id) => is_string($id) && ctype_digit($id)))
                : (is_string($value) && ctype_digit($value) ? $value : null);
        }

        $query = array_filter($query, fn ($value) => $value !== null);

        $request->query->replace($query);

        return $next($request);
    }
}
