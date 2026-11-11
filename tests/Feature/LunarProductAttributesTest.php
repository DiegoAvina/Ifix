<?php

namespace Tests\Feature;

use App\Support\LunarAttribute;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Attribute;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Livewire\Livewire;
use Tests\TestCase;

class LunarProductAttributesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->actingAs(Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'admin' => true,
        ]), 'staff');
    }

    public function test_name_and_description_attributes_exist_and_are_mapped_to_the_product_type(): void
    {
        foreach (['product', 'collection'] as $type) {
            $this->assertEqualsCanonicalizing(
                ['name', 'description'],
                Attribute::query()->where('attribute_type', $type)->pluck('handle')->all(),
            );
        }

        $this->assertEqualsCanonicalizing(
            ['name', 'description'],
            ProductType::query()->firstOrFail()->mappedAttributes->pluck('handle')->all(),
        );
    }

    public function test_staff_can_create_a_product_from_the_panel(): void
    {
        Livewire::test(ListProducts::class)
            ->callAction('create', [
                'name' => ['es' => 'Toalla de microfibra 40x40'],
                'product_type_id' => ProductType::query()->value('id'),
                'sku' => 'IFX-TEST-MICRO',
                'base_price' => 99,
            ])
            ->assertHasNoActionErrors();

        $product = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', 'IFX-TEST-MICRO'))->firstOrFail();

        $this->assertSame('draft', $product->status);
        $this->assertSame('Toalla de microfibra 40x40', LunarAttribute::text($product->attribute_data, 'name'));
    }

    public function test_staff_can_edit_the_name_and_description_of_a_product(): void
    {
        Livewire::test(ListProducts::class)->callAction('create', [
            'name' => ['es' => 'Producto de prueba'],
            'product_type_id' => ProductType::query()->value('id'),
            'sku' => 'IFX-TEST-EDIT',
            'base_price' => 150,
        ]);

        $product = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', 'IFX-TEST-EDIT'))->firstOrFail();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            // Igual que escribir en los campos "Nombre" y "Descripción" del panel.
            ->set('data.attribute_data.name.es', 'Producto renombrado')
            ->set('data.attribute_data.description.es', '<p>Nueva descripción</p>')
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertSame('Producto renombrado', LunarAttribute::text($product->attribute_data, 'name'));
        $this->assertStringContainsString('Nueva descripción', LunarAttribute::text($product->attribute_data, 'description'));
    }
}
