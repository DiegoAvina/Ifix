<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Services\Storefront\CategoryService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoProductsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\ListField;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Tests\TestCase;

class ProductSheetAndProcessTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoProductsSeeder::class);

        $group = CollectionGroup::query()->where('handle', CategoryService::GROUP_HANDLE)->firstOrFail();
        $pulimentos = Collection::query()->where('collection_group_id', $group->id)->get()
            ->first(fn (Collection $collection) => $collection->translateAttribute('name') === 'Pulimentos');

        $this->product = Product::query()->where('status', 'published')->orderBy('id')->firstOrFail();
        $this->product->collections()->detach();
        $pulimentos->products()->syncWithoutDetaching([$this->product->id => ['position' => 1]]);

        $this->artisan('seo:generate-urls')->assertSuccessful();
    }

    private function page()
    {
        return $this->get(route('catalog.show', $this->product->fresh()->defaultUrl->slug));
    }

    public function test_sheet_and_how_to_come_from_the_panel_fields(): void
    {
        $this->product->attribute_data = $this->product->attribute_data->merge([
            'name' => new TranslatedText(collect(['es' => new Text('Super Compound Pulimento 500 ml')])),
            'rendimiento' => new Text('Rinde 10 autos'),
            'superficie' => new Text(''),
            'modo_de_uso' => new ListField(['Lava y seca el auto.', '', 'Aplica con pulidora a baja velocidad.']),
        ]);
        $this->product->save();

        $this->page()->assertOk()
            // Contenido tomado del nombre; superficie vacía no aparece.
            ->assertSeeInOrder(['Contenido', '500 ml', 'Rendimiento', 'Rinde 10 autos'])
            ->assertDontSee('Superficie')
            ->assertSee('Cómo se usa')
            ->assertSeeInOrder(['Lava y seca el auto.', 'Aplica con pulidora a baja velocidad.'])
            ->assertDontSee('Garantía del fabricante');
    }

    public function test_product_shows_its_step_and_the_steps_around_it(): void
    {
        $this->page()->assertOk()
            ->assertDontSee('Cómo se usa')
            ->assertSee('¿En qué paso se usa?')
            ->assertSeeInOrder(['Lavar', 'Descontaminar', 'Corregir', 'Este producto', 'Proteger'])
            ->assertSeeInOrder(['Antes de este paso', 'Descontaminar', 'Siguiente paso', 'Proteger']);
    }

    public function test_process_is_hidden_when_the_category_is_not_in_any_step(): void
    {
        AboutPage::query()->create(['process' => []] + AboutPage::defaults());

        $this->page()->assertOk()->assertDontSee('¿En qué paso se usa?');
    }
}
