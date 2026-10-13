<?php

namespace App\Data\Cart;

final readonly class CartData
{
    /**
     * @param CartItemData[] $items
     */
    public function __construct(
        public int $quantity,
        public array $items,
        public string $subtotal,
        public string $tax,
        public string $shipping,
        public string $total,
    ) {}
}