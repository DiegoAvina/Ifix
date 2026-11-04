<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function orderFor(User $user, string $description, int $quantity = 2): Order
    {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'reference' => fake()->unique()->numerify('0000####'),
            'status' => 'awaiting-payment',
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => ProductVariant::query()->firstOrFail()->id,
            'type' => 'physical',
            'description' => $description,
            'identifier' => 'IFX-TEST-500ML',
            'quantity' => $quantity,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'type' => 'shipping',
            'description' => 'Envío estándar',
        ]);

        return $order;
    }

    public function test_order_history_lists_what_was_ordered(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $user = User::factory()->create();
        $order = $this->orderFor($user, 'Shampoo de prueba 500 ml');
        $product = ProductVariant::query()->firstOrFail()->product;

        $this->actingAs($user)->get('/mis-pedidos')
            ->assertOk()
            ->assertSee("Pedido #{$order->reference}")
            ->assertSee('Shampoo de prueba 500 ml')
            ->assertSee('SKU IFX-TEST-500ML')
            ->assertSee('2 artículos')
            ->assertSee(route('catalog.show', $product->defaultUrl?->slug ?? $product->id))
            ->assertSee('Completar pago')
            ->assertDontSee('Envío estándar');
    }

    public function test_order_history_never_shows_other_customers_orders(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $user = User::factory()->create();
        $this->orderFor(User::factory()->create(), 'Producto de otra persona');

        $this->actingAs($user)->get('/mis-pedidos')
            ->assertOk()
            ->assertDontSee('Producto de otra persona')
            ->assertSee('Aún no tienes pedidos');
    }
}
