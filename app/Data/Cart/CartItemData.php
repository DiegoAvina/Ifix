<?php

namespace App\Data\Cart;

final readonly class CartItemData
{
    public function __construct(
        public int $lineId,
        public int $variantId,
        public string $name,
        public ?string $sku,
        public int $quantity,
        public string $unitPrice,
        public string $total,
        public ?string $image,

        /**
         * Cantidad mínima permitida para esta línea (ProductVariant::$min_quantity).
         */
        public int $minQuantity = 1,

        /**
         * Incremento requerido entre cantidades válidas
         * (ProductVariant::$quantity_increment).
         */
        public int $quantityIncrement = 1,

        /**
         * Cantidad máxima que realmente puede comprarse ahora mismo.
         */
        public int $maxQuantity = 1,
    ) {}
}
