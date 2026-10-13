<?php

namespace App\Services\Storefront;

use App\Data\PriceData;

class ProductPriceService
{
    public function build($variant): PriceData
    {
        $price = optional(
            $variant?->prices
                ->where('currency_id', 1)
                ->first()
        );

        $amount = $price?->price?->value ?? 0;

        return new PriceData(
            raw: $amount / 100,
            formatted: '$' . number_format($amount / 100, 2),
            currency: 'MXN',
        );
    }
}