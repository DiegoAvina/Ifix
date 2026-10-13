<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingContextData;
use App\Data\Shipping\ShippingEstimateData;

class ShippingEstimateService
{
    public function estimate(
        ShippingContextData $context
    ): ShippingEstimateData {

        $processing = now()->addDays(
            config('shipping.processing_days')
        );

        $from = $processing
            ->copy()
            ->addDays(config('shipping.delivery.min_days'));

        $to = $processing
            ->copy()
            ->addDays(config('shipping.delivery.max_days'));

        $freeShipping = $context->cartTotal >= config('shipping.free_shipping_from');

        return new ShippingEstimateData(

            postalCode: $context->postalCode ?? 'Sin definir',

            processingDate: $processing,

            from: $from,

            to: $to,

            freeShipping: $freeShipping,

            shippingCost: $freeShipping ? 0 : 149,

            message: $freeShipping
                ? 'Envío gratuito'
                : 'Costo calculado al finalizar la compra'

        );
    }
}
