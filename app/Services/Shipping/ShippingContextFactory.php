<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingContextData;

class ShippingContextFactory
{
    public function __construct(
        protected PostalCodeService $postalCodeService,
    ) {}

    public function make(
        float $cartTotal = 0,
        float $weight = 0,
        int $items = 0,
    ): ShippingContextData {

        return new ShippingContextData(

            postalCode: $this->postalCodeService->get(),

            cartTotal: $cartTotal,

            weight: $weight,

            items: $items,

        );
    }
}