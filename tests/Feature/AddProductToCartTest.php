<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class AddProductToCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_lunar_product_can_be_added_to_the_cart(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $variant = ProductVariant::query()->firstOrFail();

        $this->postJson(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cart.quantity', 1);
    }
}
