<?php

namespace App\Services\Cart;

use App\Data\Cart\CartData;
use App\Data\Cart\CartItemData;
use App\Support\LunarAttribute;
use App\Support\Money;
use App\Support\VariantQuantityRules;
use App\Services\Shipping\ShippingViewService;
use Lunar\DataTypes\Price;
use Lunar\Facades\CartSession;

class CartViewService
{
    public function __construct(
        protected ShippingViewService $shippingView,
    ) {}

    /**
     * Obtener el carrito actual.
     */
    public function get(): CartData
    {
        $cart = CartSession::current();

        if (! $cart) {
            return new CartData(
                quantity: 0,
                items: [],
                subtotal: $this->money(null),
                tax: $this->money(null),
                shipping: $this->money(null),
                total: $this->money(null),
                freeShipping: false,
                subtotalAmount: 0,
                amountUntilFreeShipping: (float) config('shipping.free_shipping_from'),
                freeShippingFrom: (int) config('shipping.free_shipping_from'),
                estimatedShipping: (int) config('shipping.standard_cost'),
            );
        }

        $cart->load([
            'lines.purchasable.product.media',
            'lines.purchasable.prices',
        ]);

        /*
         * Lunar calcula los precios reales del carrito.
         */
        $cart->calculate();

        $items = $cart->lines
            ->map(fn ($line) => $this->mapLine($line))
            ->values()
            ->toArray();

        $subtotalAmount = $cart->subTotal?->decimal() ?? 0.0;
        $shippingEstimate = $this->shippingView->estimateFor(
            cartTotal: $subtotalAmount,
            itemCount: (int) $cart->lines->sum('quantity'),
        );

        return new CartData(
            quantity: (int) $cart->lines->sum('quantity'),

            items: $items,

            subtotal: $this->money(
                $cart->subTotal
            ),

            tax: $this->money(
                $cart->taxTotal
            ),

            shipping: $this->money(
                $cart->shippingTotal
            ),

            total: $this->money(
                $cart->total
            ),

            freeShipping: $shippingEstimate->freeShipping,

            subtotalAmount: $subtotalAmount,

            amountUntilFreeShipping: max(
                0,
                (float) config('shipping.free_shipping_from') - $subtotalAmount,
            ),

            freeShippingFrom: (int) config('shipping.free_shipping_from'),

            estimatedShipping: $shippingEstimate->shippingCost,
        );
    }

    /**
     * Obtener el carrito para respuestas JSON.
     *
     * Reutilizamos la misma representación
     * que utiliza la vista del carrito.
     */
    public function data(): CartData
    {
        return $this->get();
    }

    /**
     * Transformar una línea de Lunar a nuestro DTO.
     */
    protected function mapLine($line): CartItemData
    {
        $variant = $line->purchasable;

        $product = $variant?->product;

        $name = $product
            ? LunarAttribute::text(
                $product->attribute_data,
                'name'
            )
            : 'Producto';

        $image = $product
            ? $product->getFirstMediaUrl('images')
            : null;

        $unitPrice = $this->resolveUnitPrice($line);

        $lineTotal = $line->total ?? null;

        return new CartItemData(
            lineId: $line->id,

            variantId: $variant?->id ?? 0,

            name: $name ?: 'Producto',

            sku: $variant?->sku,

            quantity: (int) $line->quantity,

            unitPrice: $this->money(
                $unitPrice
            ),

            total: $this->money(
                $lineTotal
            ),

            image: $image ?: null,

            minQuantity: VariantQuantityRules::minQuantity($variant),

            quantityIncrement: VariantQuantityRules::quantityIncrement($variant),

            maxQuantity: VariantQuantityRules::maxQuantity($variant),
        );
    }

    /**
     * Obtener el precio unitario de la línea.
     *
     * Lunar calcula el precio real.
     */
    protected function resolveUnitPrice($line): ?Price
    {
        if (
            isset($line->unit_price) &&
            $line->unit_price instanceof Price
        ) {
            return $line->unit_price;
        }

        if (
            isset($line->unitPrice) &&
            $line->unitPrice instanceof Price
        ) {
            return $line->unitPrice;
        }

        return null;
    }

    /**
     * Formatear un Price de Lunar.
     */
    protected function money(?Price $price): string
    {
        return Money::format($price);
    }
}