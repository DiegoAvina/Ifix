<?php

namespace Tests\Feature;

use App\Services\Storefront\ProductPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class ProductPricePriorityTest extends TestCase
{
    use RefreshDatabase;

    private function price(ProductVariant $variant, int $amount, ?int $group): void
    {
        Price::query()->create([
            'priceable_type' => ProductVariant::morphName(),
            'priceable_id' => $variant->id,
            'currency_id' => Currency::query()->where('default', true)->value('id'),
            'customer_group_id' => $group,
            'min_quantity' => 1,
            'price' => $amount,
        ]);
    }

    public function test_retail_price_wins_over_base_price_and_base_is_used_when_alone(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $variant = ProductVariant::query()->firstOrFail();
        $variant->prices()->delete();

        // Solo precio base (así quedan los productos creados con "New Product").
        $this->price($variant, 15000, null);
        $this->assertSame(150.0, app(ProductPriceService::class)->build($variant->fresh('prices'))->raw);

        // Con precio de Retail, ese manda, igual que en el cobro del carrito.
        $this->price($variant, 29900, CustomerGroup::query()->where('default', true)->value('id'));
        $this->assertSame(299.0, app(ProductPriceService::class)->build($variant->fresh('prices'))->raw);
    }
}
