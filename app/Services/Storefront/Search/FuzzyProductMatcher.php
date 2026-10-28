<?php

namespace App\Services\Storefront\Search;

use App\Support\LunarAttribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Lunar\Models\Product;

/**
 * Búsqueda tolerante a errores de escritura: "pulimeto", "seramico",
 * "abrillantdor", "br1" o "shiny restorer" encuentran el producto aunque
 * el texto no coincida letra por letra con el nombre.
 *
 * El catálogo es pequeño, así que se arma en memoria (cacheado) un índice
 * de palabras normalizadas por producto y se puntúa cada uno contra la
 * búsqueda: coincidencia exacta, prefijo, subcadena, distancia de
 * Levenshtein y una normalización fonética del español (v/b, z/s, c/k,
 * h muda, ll/y...).
 */
class FuzzyProductMatcher
{
    public const CACHE_KEY = 'search.fuzzy-index';

    private const MIN_SCORE = 0.55;

    private const STOPWORDS = [
        'de', 'del', 'la', 'las', 'el', 'los', 'para', 'con', 'y', 'en', 'un', 'una', 'por', 'al',
    ];

    /**
     * Palabras que el cliente usa y el catálogo nombra distinto.
     */
    private const SYNONYMS = [
        'vidrio' => ['cristal', 'cristales', 'glass'],
        'vidrios' => ['cristal', 'cristales', 'glass'],
        'parabrisas' => ['cristal', 'cristales', 'glass'],
        'jabon' => ['shampoo'],
        'champu' => ['shampoo'],
        'espuma' => ['foam'],
        'wax' => ['cera'],
        'cera' => ['wax'],
        'ceramico' => ['ceramic'],
        'ceramica' => ['ceramic'],
        'grafeno' => ['graphene'],
        'llanta' => ['llantas'],
        'neumatico' => ['llantas'],
        'neumaticos' => ['llantas'],
        'rueda' => ['llantas'],
        'ruedas' => ['llantas'],
        'pulidor' => ['pulimento', 'compound'],
        'pulir' => ['pulimento', 'compound'],
        'compuesto' => ['compound', 'pulimento'],
        'abrillantador' => ['gloss', 'abrillantador'],
        'brillo' => ['gloss', 'abrillantador'],
        'arcilla' => ['clay', 'plastilina'],
        'recubrimiento' => ['coating'],
        'plasticos' => ['plastico'],
    ];

    /**
     * IDs de productos publicados ordenados por relevancia.
     *
     * @return array<int, int>
     */
    public function match(string $search, int $limit = 50): array
    {
        $tokens = $this->tokens($search);

        if ($tokens === []) {
            return [];
        }

        $scores = [];

        foreach ($this->index() as $entry) {
            $score = $this->score($tokens, $entry);

            if ($score >= self::MIN_SCORE) {
                $scores[$entry['id']] = $score;
            }
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, array{id: int, name: array<int, string>, extra: array<int, string>}>
     */
    protected function index(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            return Product::query()
                ->with(['brand', 'variants'])
                ->where('status', 'published')
                ->limit(2000)
                ->get()
                ->map(function (Product $product) {
                    $name = (string) LunarAttribute::text($product->attribute_data, 'name');
                    $description = strip_tags((string) LunarAttribute::text($product->attribute_data, 'description'));

                    $skus = $product->variants->pluck('sku')->filter()->all();

                    $nameWords = $this->words($name.' '.implode(' ', $skus));

                    // "BR-1" / "IFX-AXID-1L" también se indexan juntos ("br1").
                    foreach ([$name, ...$skus] as $text) {
                        foreach (preg_split('/\s+/', $text) ?: [] as $chunk) {
                            $joined = $this->normalize(preg_replace('/[^\pL\pN]+/u', '', $chunk) ?? '');

                            if (mb_strlen($joined) >= 2) {
                                $nameWords[] = $joined;
                            }
                        }
                    }

                    return [
                        'id' => $product->id,
                        'name' => array_values(array_unique($nameWords)),
                        'extra' => array_values(array_unique($this->words(
                            ($product->brand?->name ?? '').' '.$description
                        ))),
                    ];
                })
                ->all();
        });
    }

    /**
     * Promedio del mejor puntaje de cada palabra buscada. Las palabras del
     * nombre/SKU pesan completo; marca y descripción pesan menos.
     *
     * @param  array<int, string>  $tokens
     */
    protected function score(array $tokens, array $entry): float
    {
        $total = 0.0;
        $misses = 0;

        foreach ($tokens as $token) {
            $best = 0.0;

            foreach ([$token, ...(self::SYNONYMS[$token] ?? [])] as $variant) {
                $best = max(
                    $best,
                    $this->bestWordScore($variant, $entry['name']),
                    $this->bestWordScore($variant, $entry['extra']) * 0.7,
                );
            }

            if ($best < 0.5) {
                $misses++;
            }

            $total += $best;
        }

        // Si la mayoría de las palabras no tiene relación, no es este producto.
        if ($misses > intdiv(count($tokens), 2)) {
            return 0.0;
        }

        return $total / count($tokens);
    }

    /**
     * @param  array<int, string>  $words
     */
    protected function bestWordScore(string $token, array $words): float
    {
        $best = 0.0;
        $tokenPhonetic = $this->phonetic($token);
        $length = mb_strlen($token);

        foreach ($words as $word) {
            if ($word === $token) {
                return 1.0;
            }

            $score = 0.0;

            if (str_starts_with($word, $token)) {
                $score = $length >= 2 ? 0.95 : 0.0;
            } elseif ($length >= 3 && str_contains($word, $token)) {
                $score = 0.75;
            } elseif ($this->phonetic($word) === $tokenPhonetic) {
                $score = 0.9;
            } elseif ($length >= 3) {
                $score = max(
                    $this->typoScore($token, $word),
                    // Palabra a medio escribir y con error: "pulimneto" → "pulimento".
                    $this->typoScore($token, mb_substr($word, 0, $length)) * 0.85,
                    $this->typoScore($tokenPhonetic, $this->phonetic($word)) * 0.9,
                );
            }

            $best = max($best, $score);
        }

        return $best;
    }

    protected function typoScore(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }

        $length = max(strlen($a), strlen($b));
        $allowed = $length <= 4 ? 1 : ($length <= 7 ? 2 : 3);
        $distance = levenshtein($a, $b);

        if ($distance > $allowed) {
            return 0.0;
        }

        return 0.85 - ($distance - 1) * 0.12;
    }

    /**
     * @return array<int, string>
     */
    protected function tokens(string $search): array
    {
        $words = $this->words(mb_substr($search, 0, 100));

        $words = array_values(array_filter(
            $words,
            fn (string $word) => ! in_array($word, self::STOPWORDS, true)
        ));

        return array_slice(array_values(array_unique($words)), 0, 8);
    }

    /**
     * @return array<int, string>
     */
    protected function words(string $text): array
    {
        $words = preg_split('/\s+/', $this->normalize($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($words, fn (string $word) => strlen($word) >= 1));
    }

    /**
     * Minúsculas, sin acentos ni signos.
     */
    protected function normalize(string $text): string
    {
        $text = Str::lower(Str::ascii($text));

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '');
    }

    /**
     * Clave fonética simple para el español: agrupa letras que suenan
     * igual o que se confunden al escribir rápido.
     */
    protected function phonetic(string $word): string
    {
        $word = strtr($word, ['ch' => '#', 'll' => 'y', 'qu' => 'k', 'ph' => 'f', 'sh' => '#']);
        $word = preg_replace('/c(?=[ei])/', 's', $word) ?? $word;
        $word = preg_replace('/g(?=[ei])/', 'j', $word) ?? $word;
        $word = strtr($word, ['c' => 'k', 'z' => 's', 'v' => 'b', 'w' => 'u', 'h' => '', 'x' => 'ks', 'y' => 'i']);

        // Letras dobles: "abrillantador" / "abrilantador".
        return preg_replace('/(.)\1+/', '$1', $word) ?? $word;
    }
}
