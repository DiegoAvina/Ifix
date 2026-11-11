<?php

namespace App\Services\Storefront;

use App\Data\PriceData;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

class ProductPriceService
{
    private ?Currency $currency = null;

    private ?int $defaultGroupId = null;

    private bool $resolved = false;

    public function build($variant): PriceData
    {
        $this->resolveDefaults();

        $currency = $this->currency;

        // Misma prioridad que usa Lunar al cobrar en el carrito: el precio del
        // grupo de clientes (Retail) gana sobre el precio base sin grupo. Así
        // el precio que se muestra siempre es el que se cobra, aunque en el
        // panel un producto tenga ambos (los creados con "New Product" solo
        // tienen precio base).
        $price = $variant?->prices
            ->where('currency_id', $currency?->id)
            ->filter(fn ($price) => (int) $price->min_quantity <= 1)
            ->filter(fn ($price) => $price->customer_group_id === null || $price->customer_group_id === $this->defaultGroupId)
            ->sortBy(fn ($price) => $price->customer_group_id === $this->defaultGroupId ? 0 : 1)
            ->first();

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

    /**
     * Una sola consulta por petición (antes se buscaba la moneda en cada tarjeta).
     */
    private function resolveDefaults(): void
    {
        if ($this->resolved) {
            return;
        }

        $this->currency = Currency::query()->where('default', true)->first();
        $this->defaultGroupId = CustomerGroup::query()->where('default', true)->value('id');
        $this->resolved = true;
    }
}
