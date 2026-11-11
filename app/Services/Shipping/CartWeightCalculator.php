<?php

namespace App\Services\Shipping;

use App\Models\Shipping\ShippingSetting;
use Lunar\Models\Contracts\Cart;

/**
 * Peso total del carrito en kg: el peso capturado de cada variante (pestaña
 * Envío del producto en el panel) por su cantidad. Si una variante no tiene
 * peso, se usa el peso por pieza de "Ajustes de envío".
 */
class CartWeightCalculator
{
    private const TO_KG = ['kg' => 1, 'g' => 0.001, 'lbs' => 0.453592, 'lb' => 0.453592, 'oz' => 0.0283495];

    public function forCart(Cart $cart): float
    {
        $default = (float) ShippingSetting::current()->default_item_weight_kg;

        return (float) $cart->lines->sum(function ($line) use ($default) {
            $variant = $line->purchasable;
            $value = (float) ($variant?->weight_value ?? 0);
            $unit = strtolower((string) ($variant?->weight_unit ?: 'kg'));
            $kg = $value > 0 ? $value * (self::TO_KG[$unit] ?? 1) : $default;

            return $kg * (int) $line->quantity;
        });
    }
}
