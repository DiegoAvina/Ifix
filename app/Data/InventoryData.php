<?php

namespace App\Data;

final readonly class InventoryData
{
    public function __construct(

        public ?string $sku,

        public int $stock,

        public bool $available,

        public bool $backorder,

        /**
         * Valor real de ProductVariant::$purchasable en Lunar:
         * 'always' | 'in_stock' | 'in_stock_or_on_backorder'.
         */
        public string $purchasable,

        /**
         * Cantidad mínima permitida por compra (ProductVariant::$min_quantity).
         */
        public int $minQuantity,

        /**
         * Incremento requerido entre cantidades válidas
         * (ProductVariant::$quantity_increment).
         */
        public int $quantityIncrement,

        /**
         * Cantidad máxima que realmente puede comprarse ahora mismo.
         */
        public int $maxQuantity,

    ) {
    }
}
