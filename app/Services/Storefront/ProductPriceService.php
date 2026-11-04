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
        $compare = $price?->compare_price?->value ?? 0;

        // Solo es oferta si el precio anterior es mayor al actual.
        $onSale = $amount > 0 && $compare > $amount;

        return new PriceData(
            raw: $amount / 100,
            formatted: '$' . number_format($amount / 100, 2),
            currency: $currency?->code ?? 'MXN',
            compareFormatted: $onSale ? '$' . number_format($compare / 100, 2) : null,
            discountPercent: $onSale ? (int) round((1 - $amount / $compare) * 100) : null,
        );
    }
}
