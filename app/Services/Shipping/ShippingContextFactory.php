<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingContextData;

class ShippingContextFactory
{
    public function __construct(
        protected PostalCodeService $postalCodeService,
    ) {}

    /**
     * El C.P. de la dirección de envío manda; si aún no hay dirección, el que
     * el cliente escribió en el estimador (sesión).
     */
    public function make(
        float $cartTotal = 0,
        float $weight = 0,
        int $items = 0,
        ?string $postalCode = null,
    ): ShippingContextData {
        return new ShippingContextData(
            postalCode: $postalCode ?: $this->postalCodeService->get(),
            cartTotal: $cartTotal,
            weight: $weight,
            items: $items,
        );
    }
}
