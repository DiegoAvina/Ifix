<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingContextData;
use App\Data\Shipping\ShippingEstimateData;

class ShippingEstimateService
{
    public function __construct(
        protected ShippingRateCalculator $calculator,
    ) {}

    public function estimate(ShippingContextData $context): ShippingEstimateData
    {
        $quote = $this->calculator->quote($context->postalCode, $context->cartTotal, $context->weight);

        $processing = now()->addDays($quote->processingDays);

        $message = match (true) {
            $quote->freeShipping => 'Envío gratuito',
            $quote->extendedZone && $quote->total === $quote->extendedSurcharge => 'Envío gratis + cargo por zona extendida',
            $quote->extendedZone => 'Incluye cargo por zona extendida',
            ! $quote->postcodeKnown => 'Estimado; el costo final depende de tu código postal',
            default => 'Envío a '.$quote->zoneName,
        };

        return new ShippingEstimateData(
            postalCode: $context->postalCode ?? 'Sin definir',
            processingDate: $processing,
            from: $processing->copy()->addDays($quote->minDays),
            to: $processing->copy()->addDays($quote->maxDays),
            freeShipping: $quote->freeShipping,
            shippingCost: $quote->total,
            message: $message,
            zoneName: $quote->zoneName,
            extendedZone: $quote->extendedZone,
            extendedSurcharge: $quote->extendedSurcharge,
            postcodeKnown: $quote->postcodeKnown,
        );
    }
}
