<?php

namespace Tests\Feature;

use App\Services\Cart\CartService;
use App\Services\Storefront\CollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class UnverifiedProductVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_products_cannot_be_viewed_or_added_to_cart(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $product = Product::query()->firstOrFail();
        $variant = ProductVariant::query()->where('product_id', $product->id)->firstOrFail();
        $product->update(['status' => 'draft']);

        $this->get(route('catalog.show', $product->id))->assertNotFound();
        $this->get(route('catalog.show', $product->defaultUrl->slug))->assertNotFound();

        try {
            app(CartService::class)->addProduct($variant);
            $this->fail('A draft product must not be added to a cart.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('El producto ya no está disponible.', $exception->getMessage());
        }
    }

    public function test_draft_products_are_excluded_from_homepage_collections(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        Product::query()->update(['status' => 'draft']);

        $collections = app(CollectionService::class)->all();

        foreach ($collections as $collection) {
            $this->assertSame([], $collection['products']);
        }
    }
}
