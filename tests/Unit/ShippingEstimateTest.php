<?php

namespace Tests\Unit;

use App\Data\Shipping\ShippingContextData;
use App\Services\Shipping\ShippingEstimateService;
use Tests\TestCase;

class ShippingEstimateTest extends TestCase
{
    public function test_shipping_is_free_at_and_above_the_499_peso_threshold(): void
    {
        config([
            'shipping.free_shipping_from' => 499,
            'shipping.standard_cost' => 149,
        ]);

        $service = new ShippingEstimateService();

        $belowThreshold = $service->estimate(
            new ShippingContextData(postalCode: null, cartTotal: 498.99)
        );

        $atThreshold = $service->estimate(
            new ShippingContextData(postalCode: null, cartTotal: 499.00)
        );

        $aboveThreshold = $service->estimate(
            new ShippingContextData(postalCode: null, cartTotal: 700.00)
        );

        $this->assertFalse($belowThreshold->freeShipping);
        $this->assertSame(149, $belowThreshold->shippingCost);
        $this->assertTrue($atThreshold->freeShipping);
        $this->assertSame(0, $atThreshold->shippingCost);
        $this->assertTrue($aboveThreshold->freeShipping);
        $this->assertSame(0, $aboveThreshold->shippingCost);
    }
}
