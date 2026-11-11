<?php

namespace Tests\Feature;

use App\Services\Storefront\Catalog\Filters\AvailabilityFilter;
use App\Services\Storefront\Catalog\Query\AvailabilityQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class CatalogAvailabilityFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{Disponible: bool, Agotado: bool}
     */
    private function selectedWith(array $query): array
    {
        $this->app->instance('request', Request::create('/productos', 'GET', $query));

        return collect(app(AvailabilityFilter::class)->build()->options)
            ->mapWithKeys(fn ($option) => [$option->label => $option->selected])
            ->all();
    }

    public function test_no_option_is_checked_without_a_filter(): void
    {
        $this->assertSame(['Disponible' => false, 'Agotado' => false], $this->selectedWith([]));
        $this->assertSame(['Disponible' => false, 'Agotado' => false], $this->selectedWith(['availability' => '']));
    }

    public function test_only_the_chosen_option_is_checked(): void
    {
        $this->assertSame(['Disponible' => true, 'Agotado' => false], $this->selectedWith(['availability' => '1']));
        $this->assertSame(['Disponible' => false, 'Agotado' => true], $this->selectedWith(['availability' => '0']));
    }

    public function test_brand_filter_ignores_draft_products(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $published = Product::query()->where('status', 'published')->firstOrFail();
        $brand = $published->brand;
        $publishedCount = $brand->products()->where('status', 'published')->count();

        // Un borrador de la misma marca y una marca que solo tiene borradores.
        $draft = $published->replicate();
        $draft->status = 'draft';
        $draft->save();
        $onlyDrafts = \Lunar\Models\Brand::query()->create(['name' => 'Solo borradores']);
        $published->replicate()->fill(['status' => 'draft', 'brand_id' => $onlyDrafts->id])->save();

        $this->app->instance('request', Request::create('/productos', 'GET'));
        $options = collect(app(\App\Services\Storefront\Catalog\Filters\BrandFilter::class)->build()->options)->keyBy('label');

        $this->assertSame($publishedCount, $options[$brand->name]->count);
        $this->assertFalse($options->has('Solo borradores'));
    }

    public function test_query_filters_by_stock_and_ignores_empty_values(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $soldOut = ProductVariant::query()->firstOrFail();
        $soldOut->update(['stock' => 0]);
        ProductVariant::query()->whereKeyNot($soldOut->id)->update(['stock' => 5]);

        $ids = function (array $query): array {
            $this->app->instance('request', Request::create('/productos', 'GET', $query));

            return app(AvailabilityQuery::class)->apply(Product::query())->pluck('id')->sort()->values()->all();
        };

        $all = Product::query()->pluck('id')->sort()->values()->all();

        $this->assertSame($all, $ids([]));
        $this->assertSame($all, $ids(['availability' => '']));
        $this->assertSame([$soldOut->product_id], $ids(['availability' => '0']));
        $this->assertNotContains($soldOut->product_id, $ids(['availability' => '1']));
    }
}
