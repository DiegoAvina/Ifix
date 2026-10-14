<?php

namespace Database\Seeders;

use LogicException;
use Illuminate\Database\Seeder;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo products may only be seeded in local or testing environments.');
        }

        $language = Language::query()->where('default', true)->first();

        if (! $language) {
            $language = Language::query()->firstOrCreate(
                ['code' => 'es'],
                ['name' => 'Español', 'default' => true],
            );

            if (! $language->default) {
                $language->default = true;
                $language->save();
            }
        }

        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'webstore'],
            [
                'name' => 'Webstore',
                'default' => ! Channel::query()->where('default', true)->exists(),
                'url' => config('app.url'),
            ],
        );

        if (! Channel::query()->where('default', true)->exists()) {
            $channel->default = true;
            $channel->save();
        }

        $currency = Currency::query()->where('default', true)->first();

        if (! $currency) {
            $currency = Currency::query()->firstOrCreate(
                ['code' => 'MXN'],
                [
                    'name' => 'Peso mexicano',
                    'exchange_rate' => 1,
                    'decimal_places' => 2,
                    'enabled' => true,
                    'default' => true,
                ],
            );

            if (! $currency->default) {
                $currency->default = true;
                $currency->enabled = true;
                $currency->save();
            }
        }

        $taxClass = TaxClass::query()->where('default', true)->first()
            ?? TaxClass::query()->firstOrCreate(
                ['name' => 'General'],
                ['default' => true],
            );

        $customerGroup = CustomerGroup::query()->where('default', true)->first()
            ?? CustomerGroup::query()->firstOrCreate(
                ['handle' => 'retail'],
                ['name' => 'Retail', 'default' => true],
            );

        $collectionGroup = CollectionGroup::query()->firstOrCreate(
            ['handle' => 'main'],
            ['name' => 'Principal'],
        );
        $productType = ProductType::query()->firstOrCreate(['name' => 'Stock']);
        $brand = Brand::query()->firstOrCreate(['name' => 'IFIX']);

        $collection = Collection::query()
            ->where('collection_group_id', $collectionGroup->id)
            ->get()
            ->first(fn (Collection $candidate) => $candidate->translateAttribute('name') === 'Productos destacados');

        if (! $collection) {
            $collection = Collection::query()->create([
                'collection_group_id' => $collectionGroup->id,
                'attribute_data' => collect([
                    'name' => $this->translatedText('Productos destacados'),
                ]),
            ]);
        }

        $collection->update([
            'attribute_data' => collect([
                'name' => $this->translatedText('Productos destacados'),
            ]),
        ]);

        $availability = [
            'enabled' => true,
            'starts_at' => now(),
            'ends_at' => null,
        ];

        $collection->channels()->syncWithoutDetaching([
            $channel->id => $availability,
        ]);
        $collection->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => [...$availability, 'visible' => true],
        ]);

        $products = [
            [
                'sku' => 'IFX-ARM-9H',
                'name' => 'Armor 9H Recubrimiento Cerámico IFIX 3 Años',
                'description' => 'Recubrimiento cerámico de alta durabilidad que protege la pintura y realza su brillo hasta por tres años.',
                'price' => 72600,
                'compare_price' => 99900,
                'stock' => 18,
            ],
            [
                'sku' => 'IFX-CERA-SIL',
                'name' => 'Cera de Silicio IFIX Nanotecnología',
                'description' => 'Cera de aplicación sencilla con tecnología de silicio para un acabado brillante y repelencia al agua.',
                'price' => 64300,
                'compare_price' => null,
                'stock' => 24,
            ],
            [
                'sku' => 'IFX-BR1-500',
                'name' => 'IFIX BR1 Restaurador de Plásticos 500 ml',
                'description' => 'Restaurador y abrillantador que devuelve color y apariencia uniforme a plásticos exteriores.',
                'price' => 35000,
                'compare_price' => null,
                'stock' => 32,
            ],
            [
                'sku' => 'IFX-HD-POL',
                'name' => 'Heavy Duty IFIX Pulimento Restaurador de Pintura',
                'description' => 'Pulimento profesional para restaurar la claridad de la pintura y ayudar a remover marcas superficiales.',
                'price' => 44500,
                'compare_price' => 46890,
                'stock' => 14,
            ],
        ];

        foreach ($products as $position => $data) {
            $variant = ProductVariant::query()->where('sku', $data['sku'])->first();
            $product = $variant?->product;

            $attributes = collect([
                'name' => $this->translatedText($data['name']),
                'description' => $this->translatedText($data['description']),
            ]);

            if (! $product) {
                $product = Product::query()->create([
                    'product_type_id' => $productType->id,
                    'brand_id' => $brand->id,
                    'status' => 'published',
                    'attribute_data' => $attributes,
                ]);
            } else {
                $product->update([
                    'product_type_id' => $productType->id,
                    'brand_id' => $brand->id,
                    'status' => 'published',
                    'attribute_data' => $attributes,
                ]);
            }

            if (! $variant) {
                $variant = $product->variants()->create([
                    'tax_class_id' => $taxClass->id,
                    'sku' => $data['sku'],
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'shippable' => true,
                    'stock' => $data['stock'],
                    'purchasable' => 'in_stock',
                ]);
            } else {
                $variant->update([
                    'product_id' => $product->id,
                    'tax_class_id' => $taxClass->id,
                    'stock' => $data['stock'],
                    'purchasable' => 'in_stock',
                ]);
            }

            $product->channels()->syncWithoutDetaching([
                $channel->id => $availability,
            ]);
            $product->customerGroups()->syncWithoutDetaching([
                $customerGroup->id => [...$availability, 'visible' => true, 'purchasable' => true],
            ]);

            Price::query()->updateOrCreate(
                [
                    'priceable_type' => ProductVariant::morphName(),
                    'priceable_id' => $variant->id,
                    'currency_id' => $currency->id,
                    'customer_group_id' => $customerGroup->id,
                    'min_quantity' => 1,
                ],
                [
                    'price' => $data['price'],
                    'compare_price' => $data['compare_price'],
                ],
            );

            $collection->products()->syncWithoutDetaching([
                $product->id => ['position' => $position + 1],
            ]);
        }
    }

    private function translatedText(string $value): TranslatedText
    {
        return new TranslatedText(collect([
            'es' => new Text($value),
        ]));
    }
}
