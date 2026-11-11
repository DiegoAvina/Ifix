<?php

namespace Tests\Feature;

use App\Models\Shipping\ShippingExtendedPostcode;
use App\Models\Shipping\ShippingSetting;
use App\Services\Shipping\StandardShippingModifier;
use Lunar\Facades\ShippingManifest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

/**
 * El envío que Lunar suma al carrito (y que se cobra en el pedido) sale del
 * C.P. de la dirección de envío y del peso del carrito.
 */
class CartShippingByPostcodeTest extends TestCase
{
    use RefreshDatabase;

    private function cartTo(string $postcode, int $quantity = 1): Cart
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        // Sin envío gratis, para ver el costo completo en cualquier monto.
        ShippingSetting::query()->first()->update(['free_shipping_from' => null]);

        $variant = ProductVariant::query()->whereHas('product', fn ($q) => $q->where('status', 'published'))->firstOrFail();
        $variant->update(['stock' => 100, 'weight_value' => 2, 'weight_unit' => 'kg']);

        $cart = Cart::query()->create([
            'currency_id' => Currency::query()->where('default', true)->value('id'),
            'channel_id' => Channel::query()->where('default', true)->value('id'),
        ]);
        $cart->add($variant, $quantity);

        $cart->setShippingAddress([
            'first_name' => 'Ana',
            'last_name' => 'López',
            'line_one' => 'Calle 1',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postcode' => $postcode,
            'country_id' => Country::query()->where('iso2', 'MX')->value('id'),
            'contact_email' => 'ana@example.com',
            'contact_phone' => '5555555555',
        ]);

        // Igual que CheckoutService::useShippingAddress().
        $cart = $cart->refresh()->calculate();
        $cart->setShippingOption(ShippingManifest::getOption($cart, StandardShippingModifier::IDENTIFIER));

        return $cart->refresh()->calculate();
    }

    private function shipping(Cart $cart): float
    {
        return $cart->shippingSubTotal->value / 100;
    }

    public function test_cart_charges_the_zone_of_the_shipping_address(): void
    {
        // Mérida, 1 pieza de 2 kg.
        $this->assertSame(189.0, $this->shipping($this->cartTo('97000')));
    }

    public function test_weight_of_the_cart_is_charged(): void
    {
        // 3 piezas × 2 kg = 6 kg → rango "hasta 10 kg" (+$90) en zona local.
        $this->assertSame(79.0 + 90.0, $this->shipping($this->cartTo('44720', 3)));
    }

    public function test_extended_zone_surcharge_is_charged(): void
    {
        ShippingExtendedPostcode::query()->create(['postcode' => '29950']);

        $this->assertSame(189.0 + 199.0, $this->shipping($this->cartTo('29950')));
    }
}
