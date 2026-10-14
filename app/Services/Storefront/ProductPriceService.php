<?php

namespace App\Services\Storefront;

use App\Data\PriceData;
use Lunar\Models\Currency;

class ProductPriceService
{
    public function build($variant): PriceData
    {
        $currency = Currency::query()->where('default', true)->first();
        $price = optional(
            $variant?->prices
                ->where('currency_id', $currency?->id)
                ->first()
        );

        $amount = $price?->price?->value ?? 0;

        return new PriceData(
            raw: $amount / 100,
            formatted: '$' . number_format($amount / 100, 2),
            currency: $currency?->code ?? 'MXN',
        );
    }
}