<?php

namespace Tests\Feature;

use App\Services\Checkout\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

/**
 * No se puede pedir más de lo que hay en existencias: ni al agregar, ni al
 * cambiar la cantidad, ni al confirmar el pedido.
 */
class CartStockLimitsTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $this->variant = ProductVariant::query()
            ->whereHas('product', fn ($q) => $q->where('status', 'published'))
            ->firstOrFail();
        $this->variant->update(['stock' => 3, 'backorder' => 0, 'purchasable' => 'in_stock']);
    }

    private function add(int $quantity)
    {
        return $this->postJson(route('cart.items.store'), ['variant_id' => $this->variant->id, 'quantity' => $quantity]);
    }

    public function test_cannot_add_more_than_the_stock_at_once(): void
    {
        $this->add(4)->assertStatus(422)->assertJson(['message' => 'Solo hay 3 piezas disponibles.']);
        $this->add(3)->assertOk();
    }

    public function test_cannot_exceed_the_stock_adding_in_several_times(): void
    {
        $this->add(2)->assertOk();
        $this->add(2)->assertStatus(422)->assertJson(['message' => 'Solo hay 3 piezas disponibles.']);
        $this->add(1)->assertOk();
    }

    public function test_cannot_raise_the_quantity_in_the_cart_above_the_stock(): void
    {
        $line = $this->add(1)->assertOk()->json('cart.items.0.lineId');

        $this->patchJson(route('cart.items.update', ['line' => $line]), ['quantity' => 5])
            ->assertStatus(422)
            ->assertJson(['message' => 'Solo hay 3 piezas disponibles.']);

        $this->patchJson(route('cart.items.update', ['line' => $line]), ['quantity' => 3])->assertOk();
    }

    public function test_sold_out_products_cannot_be_added_unless_they_sell_always(): void
    {
        $this->variant->update(['stock' => 0]);
        $this->add(1)->assertStatus(422)->assertJson(['message' => 'Este producto está agotado.']);

        $this->variant->update(['purchasable' => 'always']);
        $this->add(1)->assertOk();
    }

    public function test_invalid_quantities_are_rejected(): void
    {
        $this->add(0)->assertStatus(422)->assertJsonPath('message', 'La cantidad debe ser al menos 1.');
        $this->add(-2)->assertStatus(422);
        $this->add(1000)->assertStatus(422);
    }

    private function cartWith(array $lineQuantities): Cart
    {
        $cart = Cart::query()->create([
            'currency_id' => Currency::query()->where('default', true)->value('id'),
            'channel_id' => Channel::query()->where('default', true)->value('id'),
        ]);

        foreach ($lineQuantities as $i => $quantity) {
            CartLine::query()->create([
                'cart_id' => $cart->id,
                'purchasable_type' => ProductVariant::morphName(),
                'purchasable_id' => $this->variant->id,
                'quantity' => $quantity,
                'meta' => ['linea' => $i],
            ]);
        }

        return $cart;
    }

    public function test_checkout_rejects_a_cart_whose_stock_ran_out_meanwhile(): void
    {
        $cart = $this->cartWith([3]);
        $this->variant->update(['stock' => 2]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Solo hay 2 piezas disponibles/');

        app(CheckoutService::class)->placeOrder($cart);
    }

    public function test_checkout_adds_up_the_same_product_split_in_several_lines(): void
    {
        // 2 + 2 = 4 piezas del mismo producto, con 3 en existencia.
        $cart = $this->cartWith([2, 2]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Solo hay 3 piezas disponibles/');

        app(CheckoutService::class)->placeOrder($cart);
    }
}
