<?php

namespace App\Support;

use Lunar\Models\ProductVariant;

/**
 * Traduce las reglas reales de un ProductVariant de Lunar
 * (min_quantity, quantity_increment, purchasable, stock)
 * a valores simples que puede usar el storefront.
 *
 * Basado en la API real de Lunar 1.3.0:
 * - ProductVariant::canBeFulfilledAtQuantity(int $quantity): bool
 * - ProductVariant::getTotalInventory(): int
 * - ProductVariant::$purchasable es un string: 'always' | 'in_stock' | 'in_stock_or_on_backorder'
 *
 * @see \Lunar\Models\ProductVariant
 */
class VariantQuantityRules
{
    /**
     * Tope absoluto que Lunar aplica en CartLineQuantity
     * cuando el producto es comprable sin límite ("always").
     */
    protected const UNLIMITED_CEILING = 1_000_000;

    public static function minQuantity(?ProductVariant $variant): int
    {
        return max(1, (int) ($variant?->min_quantity ?? 1));
    }

    public static function quantityIncrement(?ProductVariant $variant): int
    {
        return max(1, (int) ($variant?->quantity_increment ?? 1));
    }

    public static function maxQuantity(?ProductVariant $variant): int
    {
        if (! $variant) {
            return 0;
        }

        if ($variant->purchasable === 'always') {
            return self::UNLIMITED_CEILING;
        }

        return max(0, $variant->getTotalInventory());
    }

    /**
     * ¿Se puede comprar al menos la cantidad mínima?
     */
    public static function isAvailable(?ProductVariant $variant): bool
    {
        if (! $variant) {
            return false;
        }

        return $variant->canBeFulfilledAtQuantity(
            self::minQuantity($variant)
        );
    }

    /**
     * ¿Permite comprar más allá del stock físico?
     * (purchasable = 'always' o 'in_stock_or_on_backorder')
     */
    public static function allowsBackorder(?ProductVariant $variant): bool
    {
        if (! $variant) {
            return false;
        }

        return $variant->purchasable !== 'in_stock';
    }

    /**
     * La cantidad válida más pequeña posible: el múltiplo de
     * quantity_increment más cercano hacia arriba que además
     * respeta min_quantity.
     */
    public static function floorQuantity(?ProductVariant $variant): int
    {
        return self::normalize(
            self::minQuantity($variant),
            $variant
        );
    }

    /**
     * Ajusta una cantidad arbitraria a la cantidad válida más
     * cercana (hacia arriba) según min_quantity y quantity_increment.
     */
    public static function normalize(int $quantity, ?ProductVariant $variant): int
    {
        $min = self::minQuantity($variant);
        $increment = self::quantityIncrement($variant);

        $quantity = max($quantity, $min);

        $remainder = $quantity % $increment;

        if ($remainder !== 0) {
            $quantity += $increment - $remainder;
        }

        return $quantity;
    }
}
