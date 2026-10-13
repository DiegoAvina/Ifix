<?php

namespace App\Support;

use Lunar\DataTypes\Price;

/**
 * Formatea un Lunar\DataTypes\Price de forma consistente en todo
 * el storefront (carrito, checkout, confirmación de pedido).
 */
class Money
{
    public static function format(?Price $price): string
    {
        if (! $price) {
            return '$0.00';
        }

        $currency = $price->currency;

        $decimalPlaces = $currency?->decimal_places ?? 2;

        $value = $price->value / (10 ** $decimalPlaces);

        $code = $currency?->code ?? 'MXN';

        return '$' . number_format(
            $value,
            $decimalPlaces,
            '.',
            ','
        ) . ' ' . $code;
    }
}
