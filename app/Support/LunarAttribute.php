<?php

namespace App\Support;

use Illuminate\Support\Collection;

class LunarAttribute
{
    public static function text(
        Collection|array $attributes,
        string $field,
        string $locale = 'es'
    ): ?string {

        $attribute = $attributes[$field] ?? null;

        if (! $attribute) {
            return null;
        }

        $translations = $attribute->getValue();

        return $translations[$locale]?->getValue();
    }

    public static function html(
        Collection|array $attributes,
        string $field,
        string $locale = 'es'
    ): ?string {

        return self::text(
            $attributes,
            $field,
            $locale
        );
    }

    public static function exists(
        Collection|array $attributes,
        string $field
    ): bool {

        return isset($attributes[$field]);
    }
}
