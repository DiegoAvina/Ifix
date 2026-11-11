<?php

namespace Tests\Feature;

use App\Services\Storefront\CategoryService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoProductsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Tests\TestCase;

class SeoCategoryPagesTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private Collection $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoProductsSeeder::class);

        $group = CollectionGroup::query()->where('handle', CategoryService::GROUP_HANDLE)->firstOrFail();
        $this->category = Collection::query()->where('collection_group_id', $group->id)->get()
            ->first(fn (Collection $collection) => $collection->translateAttribute('name') === 'Pulimentos');
        $this->product = Product::query()->where('status', 'published')->orderBy('id')->firstOrFail();
        $this->category->products()->syncWithoutDetaching([$this->product->id => ['position' => 1]]);

        // Los seeders de prueba crean productos sin slug: el comando los genera.
        $this->artisan('seo:generate-urls')->assertSuccessful();
    }

    public function test_category_page_is_indexable_with_its_own_texts(): void
    {
        $response = $this->get('/categorias/pulimentos')->assertOk();

        $response->assertSee('<title>Pulimentos para auto: corte alto, medio y abrillantador | IFIX</title>', false)
            ->assertSee('<link rel="canonical" href="'.route('catalog.category', 'pulimentos').'">', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('"@type":"CollectionPage"', false)
            ->assertSee($this->product->defaultUrl->slug);
    }

    public function test_old_category_filter_redirects_to_the_category_page(): void
    {
        $this->get('/productos?collection[]='.$this->category->id)
            ->assertRedirect(route('catalog.category', 'pulimentos'))
            ->assertStatus(301);
    }

    public function test_category_page_with_extra_filters_is_not_indexed(): void
    {
        $this->get('/categorias/pulimentos?sort=price_asc')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->get('/categorias/no-existe')->assertNotFound();
    }

    public function test_product_id_url_redirects_to_its_slug_and_shows_category_breadcrumb(): void
    {
        $slug = $this->product->fresh()->defaultUrl->slug;

        $this->get('/productos/'.$this->product->id)->assertRedirect(route('catalog.show', $slug))->assertStatus(301);

        $this->get('/productos/'.$slug)
            ->assertOk()
            ->assertSee('href="'.route('catalog.category', 'pulimentos').'"', false)
            ->assertSee('"name":"Pulimentos"', false);
    }

    public function test_sitemap_lists_categories_products_and_privacy_notice(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('catalog.category', 'pulimentos'), false)
            ->assertSee(route('catalog.show', $this->product->fresh()->defaultUrl->slug), false)
            ->assertSee(route('legal.privacy'), false);
    }
}
