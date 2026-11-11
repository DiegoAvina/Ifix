<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingEstimateData;
use Lunar\Models\Contracts\Cart;

/**
 * Ensambla la estimación de envío que necesitan el carrito y el checkout,
 * reutilizando los servicios existentes en vez de duplicar el cálculo.
 */
class ShippingViewService
{
    public function __construct(
        protected PostalCodeService $postalCode,
        protected ShippingContextFactory $contextFactory,
        protected ShippingEstimateService $estimateService,
        protected CartWeightCalculator $weights,
        protected ShippingRateCalculator $calculator,
    ) {}

    public function estimateFor(float $cartTotal, int $itemCount = 0, float $weight = 0, ?string $postalCode = null): ShippingEstimateData
    {
        $context = $this->contextFactory->make(
            cartTotal: $cartTotal,
            weight: $weight,
            items: $itemCount,
            postalCode: $postalCode,
        );

        return $this->estimateService->estimate($context);
    }

    /**
     * Estimación para un carrito: su peso real y el C.P. de su dirección de envío.
     */
    public function estimateForCart(Cart $cart, float $cartTotal): ShippingEstimateData
    {
        return $this->estimateFor(
            cartTotal: $cartTotal,
            itemCount: (int) $cart->lines->sum('quantity'),
            weight: $this->weights->forCart($cart),
            postalCode: $cart->shippingAddress?->postcode,
        );
    }

    public function freeShippingFrom(): ?float
    {
        $from = $this->calculator->settings()->free_shipping_from;

        return $from !== null ? (float) $from : null;
    }

    public function hasPostalCode(): bool
    {
        return $this->postalCode->has();
    }

    public function currentPostalCode(): ?string
    {
        return $this->postalCode->get();
    }
}
