<?php

namespace App\Data\Shipping;

final readonly class ShippingContextData
{
    public function __construct(
        public ?string $postalCode,
        public float $cartTotal,
        public float $weight = 0,
        public int $items = 0,
        public ?string $country = 'MX',
        public ?string $state = null,
        public ?string $city = null,
    ) {}
}