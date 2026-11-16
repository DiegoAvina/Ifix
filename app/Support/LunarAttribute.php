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

    /**
     * Valor de un atributo de cualquier tipo (Text, TranslatedText, ListField…):
     * texto limpio, o lista de textos no vacíos para ListField. Null si está vacío.
     */
    public static function plain(
        Collection|array $attributes,
        string $field,
        string $locale = 'es'
    ): string|array|null {

        $attribute = $attributes[$field] ?? null;

        if (! $attribute) {
            return null;
        }

        $value = $attribute instanceof \Lunar\FieldTypes\TranslatedText
            ? self::text($attributes, $field, $locale)
            : $attribute->getValue();

        if (is_array($value) || is_object($value)) {
            // ListField se captura en el panel como tabla Clave / Valor. Se acepta
            // "1" => "Lava el auto", "Lavar" => "Lava el auto" o una sola columna llena.
            $items = [];
            foreach ((array) $value as $key => $item) {
                $key = trim((string) $key);
                $item = is_scalar($item) ? trim((string) $item) : '';
                $label = preg_match('/^(paso\s*)?\d+[.)]?$/iu', $key) ? '' : $key;

                $text = match (true) {
                    $item !== '' && $label !== '' => "{$label}: {$item}",
                    $item !== '' => $item,
                    default => $label,
                };

                if ($text !== '') {
                    $items[] = $text;
                }
            }

            return $items ?: null;
        }

        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : null;
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
