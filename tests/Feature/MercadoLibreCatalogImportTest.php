<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class MercadoLibreCatalogImportTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'source_id',
        'source_url',
        'name',
        'brand',
        'description',
        'category_path',
        'presentation',
        'sku',
        'marketplace_price_mxn',
        'marketplace_compare_price_mxn',
        'marketplace_price_observed_at',
        'image_url',
        'image_rights_verified',
        'store_price_mxn',
        'store_compare_price_mxn',
        'stock',
        'data_verified',
        'stock_verified',
    ];

    public function test_unverified_import_is_idempotently_staged_without_creating_lunar_products(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $file = $this->writeCsv([[
            'source_id' => 'MLM1234567890',
            'source_url' => 'https://www.mercadolibre.com.mx/fixture',
            'name' => 'Fixture only',
            'data_verified' => '0',
            'stock_verified' => '0',
        ]]);

        try {
            $this->artisan('ifix:catalog:import', ['file' => $file])->assertSuccessful();
            $this->artisan('ifix:catalog:import', ['file' => $file])->assertSuccessful();
        } finally {
            unlink($file);
        }

        $this->assertDatabaseCount('ifix_catalog_imports', 1);
        $this->assertSame('pending_review', DB::table('ifix_catalog_imports')->value('import_status'));
        $this->assertDatabaseCount('lunar_products', 0);
    }

    public function test_publish_option_never_creates_a_product_from_unverified_data(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $file = $this->writeCsv([[
            'source_id' => 'MLM1234567891',
            'source_url' => 'https://www.mercadolibre.com.mx/fixture',
            'name' => 'Fixture only',
            'data_verified' => '0',
            'stock_verified' => '0',
        ]]);

        try {
            $this->artisan('ifix:catalog:import', [
                'file' => $file,
                '--publish' => true,
            ])->assertSuccessful();
        } finally {
            unlink($file);
        }

        $this->assertDatabaseCount('lunar_products', 0);
        $this->assertSame('pending_review', DB::table('ifix_catalog_imports')->value('import_status'));
    }

    public function test_duplicate_skus_across_source_items_are_rejected(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $file = $this->writeCsv([
            [
                'source_id' => 'MLM1234567893',
                'source_url' => 'https://www.mercadolibre.com.mx/fixture-one',
                'sku' => 'TEST-DUPLICATE-001',
            ],
            [
                'source_id' => 'MLM1234567894',
                'source_url' => 'https://www.mercadolibre.com.mx/fixture-two',
                'sku' => 'test-duplicate-001',
            ],
        ]);

        try {
            $this->artisan('ifix:catalog:import', ['file' => $file])->assertFailed();
        } finally {
            unlink($file);
        }

        $this->assertDatabaseCount('ifix_catalog_imports', 1);
        $this->assertDatabaseCount('lunar_products', 0);
    }

    public function test_reimport_does_not_overwrite_price_or_stock_after_product_creation(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $file = $this->writeCsv([[
            'source_id' => 'MLM1234567892',
            'source_url' => 'https://www.mercadolibre.com.mx/fixture',
            'name' => 'Synthetic test item',
            'brand' => 'Synthetic test brand',
            'description' => 'Test-only description.',
            'category_path' => 'Test category > Test subcategory',
            'presentation' => 'Test pack',
            'sku' => 'TEST-ONLY-001',
            'marketplace_price_mxn' => '12.50',
            'marketplace_price_observed_at' => '2026-10-14',
            'image_rights_verified' => '1',
            'store_price_mxn' => '10.00',
            'stock' => '3',
            'data_verified' => '1',
            'stock_verified' => '1',
        ]]);

        try {
            $exitCode = Artisan::call('ifix:catalog:import', [
                'file' => $file,
                '--publish' => true,
            ]);
            $output = Artisan::output();

            $this->assertSame(0, $exitCode, $output);
            $this->assertSame(1, Product::query()->count(), $output);
            $product = Product::query()->firstOrFail();
            $variant = ProductVariant::query()->firstOrFail();
            $this->assertSame('draft', $product->status);
            $this->assertFalse(app(StorefrontAvailabilityQuery::class)
                ->apply(Product::query()->whereKey($product->id)->where('status', 'published'))
                ->exists());
            $this->assertSame(3, $variant->stock);
            $this->assertTrue($product->productOptions()->exists());
            $this->assertTrue($variant->values()->exists());
            $this->assertNotNull($product->defaultUrl?->slug);
            $this->assertTrue($product->collections()->exists());
            $price = $variant->prices()->firstOrFail();
            $this->assertSame(1000, (int) $price->getRawOriginal('price'));

            $variant->update(['stock' => 9]);
            $variant->prices()->firstOrFail()->update(['price' => 1234]);

            Storage::fake('public');
            $image = UploadedFile::fake()->image('synthetic-test-image.jpg');
            $product->addMedia($image->getPathname())
                ->usingFileName('synthetic-test-image.jpg')
                ->toMediaCollection('images');

            $this->artisan('ifix:catalog:import', [
                'file' => $file,
                '--publish' => true,
            ])->assertSuccessful();
        } finally {
            unlink($file);
        }

        $this->assertDatabaseCount('lunar_products', 1);
        $this->assertSame('published', $product->fresh()->status);
        $this->assertTrue(app(StorefrontAvailabilityQuery::class)
            ->apply(Product::query()->whereKey($product->id))
            ->exists());
        $presentation = collect(app(\App\Services\Storefront\ProductService::class)
            ->find($product->id)->specifications)
            ->first(fn ($specification) => $specification->label === 'Presentación');
        $this->assertSame('Test pack', $presentation?->value);
        $this->assertSame(9, $variant->fresh()->stock);
        $this->assertSame(1234, (int) $variant->prices()->firstOrFail()->getRawOriginal('price'));
    }

    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ifix-catalog-');
        $stream = fopen($path, 'wb');

        fputcsv($stream, self::HEADERS, ',', '"', '');

        foreach ($rows as $row) {
            $values = array_map(
                static fn (string $header) => $row[$header] ?? '',
                self::HEADERS,
            );
            fputcsv($stream, $values, ',', '"', '');
        }

        fclose($stream);

        return $path;
    }
}
