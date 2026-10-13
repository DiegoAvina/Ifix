<?php

namespace App\Services\Storefront\Catalog\Attributes;

use Illuminate\Database\Eloquent\Builder;

class AttributeQueryBuilder
{
    protected Builder $query;

    protected string $locale = 'es';

    public function for(Builder $query): self
    {
        $instance = clone $this;
        $instance->query = $query;

        return $instance;
    }

    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Construye el JSON Path utilizado por Lunar.
     *
     * Ejemplo:
     * $.name.value.es
     */
    protected function jsonPath(string $attribute): string
    {
        return '$.' . $attribute . '.value.' . $this->locale;
    }

    /**
     * Expresión SQL reutilizable para consultar atributos JSON.
     */
    protected function expression(string $attribute): string
    {
        return "LOWER(JSON_UNQUOTE(JSON_EXTRACT(attribute_data, '{$this->jsonPath($attribute)}')))";
    }

    /**
     * Contiene texto (case-insensitive).
     */
    public function contains(string $attribute, string $value): self
    {
        $this->query->whereRaw(
            $this->expression($attribute) . ' LIKE ?',
            [
                '%' . mb_strtolower($value) . '%',
            ]
        );

        return $this;
    }

    /**
     * Igual a (case-insensitive).
     */
    public function equals(string $attribute, string $value): self
    {
        $this->query->whereRaw(
            $this->expression($attribute) . ' = ?',
            [
                mb_strtolower($value),
            ]
        );

        return $this;
    }

    /**
     * Empieza con.
     */
    public function startsWith(string $attribute, string $value): self
    {
        $this->query->whereRaw(
            $this->expression($attribute) . ' LIKE ?',
            [
                mb_strtolower($value) . '%',
            ]
        );

        return $this;
    }

    /**
     * Termina con.
     */
    public function endsWith(string $attribute, string $value): self
    {
        $this->query->whereRaw(
            $this->expression($attribute) . ' LIKE ?',
            [
                '%' . mb_strtolower($value),
            ]
        );

        return $this;
    }

    public function query(): Builder
    {
        return $this->query;
    }
}