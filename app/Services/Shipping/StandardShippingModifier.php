<?php

namespace App\Services\Shipping;

use Closure;
use Lunar\Base\ShippingModifier;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\TaxClass;

/**
 * Único método de envío de la tienda: una tarifa plana definida en
 * config/shipping.php, gratuita a partir de cierto monto. No integra
 * ninguna paquetería externa — solo expone esa regla como una
 * Lunar\DataTypes\ShippingOption real, que es lo que Lunar necesita
 * para poder crear una Order (Cart::createOrder() exige que el
 * carrito tenga una opción de envío aplicada cuando es "shippable").
 */
class StandardShippingModifier extends ShippingModifier
{
    public const IDENTIFIER = 'standard';

    public function handle(CartContract $cart, Closure $next)
    {
        /** @var \Lunar\Models\Cart $cart */

        // OJO: nunca llamar $cart->calculate() aquí. Este modifier se
        // ejecuta DENTRO del pipeline ApplyShipping, que es parte del
        // propio calculate(). Llamarlo de nuevo reentra en el mismo
        // pipeline (calculate -> ApplyShipping -> este modifier ->
        // calculate -> ...) y agota la memoria. Para cuando ApplyShipping
        // corre, CalculateLines (el paso anterior) ya dejó cada línea
        // con su total calculado, así que sumamos eso directamente.
        $decimalPlaces = $cart->currency?->decimal_places ?? 2;

        $subTotalMinorUnits = (int) $cart->lines->sum(
            fn ($line) => $line->total?->value ?? 0
        );

        $estimate = app(ShippingViewService::class)->estimateFor(
            cartTotal: $subTotalMinorUnits / (10 ** $decimalPlaces),
            itemCount: (int) $cart->lines->sum('quantity'),
        );

        $decimalPlaces = $cart->currency?->decimal_places ?? 2;

        ShippingManifest::addOption(new ShippingOption(
            name: 'Envío estándar',
            description: $estimate->freeShipping
                ? 'Envío gratuito'
                : 'Entrega mediante paquetería estándar',
            identifier: self::IDENTIFIER,
            price: new Price(
                (int) round($estimate->shippingCost * (10 ** $decimalPlaces)),
                $cart->currency,
                1,
            ),
            taxClass: TaxClass::query()->orderBy('id')->firstOrFail(),
            collect: false,
        ));

        return $next($cart);
    }
}
