<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingEstimateData;

/**
 * Ensambla la estimación de envío que necesita la vista del
 * checkout, reutilizando los servicios existentes en vez de
 * duplicar la lógica de cálculo.
 */
class ShippingViewService
{
    public function __construct(
        protected PostalCodeService $postalCode,
        protected ShippingContextFactory $contextFactory,
        protected ShippingEstimateService $estimateService,
    ) {}

    public function estimateFor(float $cartTotal, int $itemCount = 0): ShippingEstimateData
    {
        $context = $this->contextFactory->make(
            cartTotal: $cartTotal,
            items: $itemCount,
        );

        return $this->estimateService->estimate($context);
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
