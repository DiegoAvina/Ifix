<?php

namespace Tests\Feature;

use App\Services\Storefront\CategoryService;
use App\Services\Storefront\CollectionService;
use Database\Seeders\IfixCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class ProductCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Asigna dos productos reales a categorías de ejemplo y devuelve
     * [producto de Limpieza, producto de Pulimentos].
     */
    private function assignProducts(): array
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $products = Product::query()->orderBy('id')->take(2)->get();

        $this->category('Limpieza')->products()->syncWithoutDetaching([$products[0]->id => ['position' => 1]]);
        $this->category('Pulimentos')->products()->syncWithoutDetaching([$products[1]->id => ['position' => 1]]);

        return [$products[0], $products[1]];
    }

    private function category(string $name): Collection
    {
        return Collection::query()
            ->whereHas('group', fn ($group) => $group->where('handle', CategoryService::GROUP_HANDLE))
            ->get()
            ->firstOrFail(fn (Collection $collection) => $collection->translateAttribute('name') === $name);
    }

    private function names(): array
    {
        return array_column(app(CategoryService::class)->all(), 'name');
    }

    public function test_seeder_creates_the_categories_once_and_keeps_panel_assignments(): void
    {
        [$cleaner] = $this->assignProducts();

        $this->seed(IfixCategorySeeder::class);

        $this->assertSame(5, CollectionGroup::query()->where('handle', CategoryService::GROUP_HANDLE)->firstOrFail()->collections()->count());
        $this->assertTrue($this->category('Limpieza')->products()->whereKey($cleaner->id)->exists());
    }

    public function test_seeder_assigns_known_skus_and_skips_missing_ones(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $variant = ProductVariant::query()->firstOrFail();
        $variant->update(['sku' => 'IFX-AXID-1L']);

        $this->seed(IfixCategorySeeder::class);

        $this->assertTrue($this->category('Limpieza')->products()->whereKey($variant->product_id)->exists());
    }

    public function test_only_categories_with_products_for_sale_are_listed_in_panel_order(): void
    {
        [$cleaner, $polish] = $this->assignProducts();

        $this->assertSame(['Pulimentos', 'Limpieza'], $this->names());

        $polish->update(['status' => 'draft']);

        $this->assertSame(['Limpieza'], $this->names());
    }

    public function test_home_and_navbar_show_categories_without_repeating_them_as_sections(): void
    {
        $this->assignProducts();

        $this->assertNotContains('Limpieza', array_column(app(CollectionService::class)->all(), 'name'));

        $url = e($this->category('Limpieza')->id);

        $this->get('/')
            ->assertOk()
            ->assertSee('Limpieza')
            ->assertSee('collection%5B0%5D='.$url, false)
            ->assertDontSee('Accesorios y kits');
    }

    public function test_catalog_filters_by_one_or_several_categories(): void
    {
        [$cleaner, $polish] = $this->assignProducts();

        $cleanerName = $cleaner->translateAttribute('name');
        $polishName = $polish->translateAttribute('name');
        $limpieza = $this->category('Limpieza')->id;
        $pulimentos = $this->category('Pulimentos')->id;

        $this->get(route('catalog.index', ['collection' => [$limpieza]]))
            ->assertOk()
            ->assertSee($cleanerName)
            ->assertDontSee($polishName);

        $this->get(route('catalog.index', ['collection' => [$limpieza, $pulimentos]]))
            ->assertOk()
            ->assertSee($cleanerName)
            ->assertSee($polishName);
    }

    public function test_catalog_chips_only_offer_categories(): void
    {
        $this->assignProducts();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Limpieza (1)')
            ->assertDontSee('Productos destacados (');
    }
}
