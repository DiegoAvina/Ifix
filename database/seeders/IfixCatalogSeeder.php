<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

/**
 * Catálogo real de IFIX (Catálogo 2026). Precios en centavos MXN tal como
 * aparecen en el PDF; las imágenes son las páginas del catálogo y viven en
 * database/seeders/images/ifix.
 *
 * Depende de LunarStorefrontSeeder y DefaultTaxConfigurationSeeder.
 */
class IfixCatalogSeeder extends Seeder
{
    /**
     * Stock inicial para productos nuevos. Ajustar desde el panel de Lunar;
     * volver a correr el seeder no sobrescribe el stock existente.
     */
    private const INITIAL_STOCK = 10;

    /**
     * SKUs de DemoProductsSeeder que se retiran de la tienda.
     */
    private const DEMO_SKUS = [
        'IFX-ARM-9H',
        'IFX-CERA-SIL',
        'IFX-BR1-500',
        'IFX-HD-POL',
    ];

    private const PRODUCTS = [
        [
            'sku' => 'IFX-AXID-1L',
            'image' => 'axid.jpg',
            'name' => 'AXID Limpiador Ácido 1 L',
            'description' => 'Limpiador líquido de base ácida para eliminar gotas de sarro y lluvia ácida. Efecto inmediato. Únicamente para cristales, rines, emblemas, tolvas y pintura.',
            'price' => 29900,
        ],
        [
            'sku' => 'IFX-GLASS-125',
            'image' => 'glass-shield.jpg',
            'name' => 'Glass Shield Sellador para Cristales 125 ml',
            'description' => 'Sellador cerámico repelente al agua. Reduce el uso de limpiaparabrisas y previene manchas de gotas secas. Para cristales y espejos. Rinde 5 autos.',
            'price' => 19900,
        ],
        [
            'sku' => 'IFX-BR1-500ML',
            'image' => 'br1.jpg',
            'name' => 'BR-1 Restaurador de Plástico Negro 500 ml',
            'description' => 'Resina de alta duración diseñada para restaurar plásticos resecos y desgastados. Hasta un año de duración. Únicamente para color negro.',
            'price' => 29900,
        ],
        [
            'sku' => 'IFX-SHINY-250',
            'image' => 'shiny-restorer.jpg',
            'name' => 'Shiny Restorer Acondicionador de Plásticos 250 ml',
            'description' => 'Hidrata y acondiciona plásticos. Previene el desgaste y deja un terminado satinado. Para cualquier color; recomendable para interiores.',
            'price' => 23000,
        ],
        [
            'sku' => 'IFX-VRT-473',
            'image' => 'vrt.jpg',
            'name' => 'VRT Acondicionador con Grafeno 473 ml',
            'description' => 'Ideal para conservar plásticos nuevos. Uso interior y exterior. Mejora el terminado con cada aplicación. Protege plásticos y genera repelencia al agua.',
            'price' => 29900,
        ],
        [
            'sku' => 'IFX-BLACK-1L',
            'image' => 'black-llantas.jpg',
            'name' => 'Black Llantas Abrillantador de Llantas 1 L',
            'description' => 'Abrillantador y acondicionador de llantas base agua, sin solventes ni pigmentos. No deja residuos.',
            'price' => 19900,
        ],
        [
            'sku' => 'IFX-CLAY-BAR',
            'image' => 'clay-bar.jpg',
            'name' => 'Clay Bar Plastilina Descontaminante con Lubricante',
            'description' => 'Elimina partículas de suciedad incrustadas para evitar microrayones y tener buena adherencia de ceras o recubrimientos. Reutilizable. Incluye lubricante Clay Lub de 500 ml.',
            'price' => 29900,
        ],
        [
            'sku' => 'IFX-CERA-SIL-200',
            'image' => 'cera-de-silicio.jpg',
            'name' => 'Cera de Silicio Amazing Ceramic Wax 200 g',
            'description' => 'Cera con nanotecnología. Brillo terminado espejo y repelencia al agua. 3 meses de duración con protección contra rayos UV.',
            'price' => 63000,
        ],
        [
            'sku' => 'IFX-CERA-GRF-200',
            'image' => 'cera-de-grafeno.jpg',
            'name' => 'Cera de Grafeno Amazing Graphene Wax 200 g',
            'description' => 'Cera con nanopartículas de grafeno resistente a los rayos del sol, con terminado espejo y repelencia al agua. Aroma a chocolate. 6 meses de duración: alta protección y máxima calidad.',
            'price' => 78000,
        ],
        [
            'sku' => 'IFX-NANO-500',
            'image' => 'nanosealant.jpg',
            'name' => 'Nanosealant Quick Detailer 500 ml',
            'description' => 'Añade un toque de brillo y suavidad a la pintura después del lavado. Fácil y rápida aplicación. Brillo inmediato, ideal para mantenimiento, estética y protección.',
            'price' => 35000,
        ],
        [
            'sku' => 'IFX-HEAVY-500',
            'image' => 'heavy-duty.jpg',
            'name' => 'Heavy Duty Pulimento de Corte Alto 500 ml',
            'description' => 'Ideal para remover rayones profundos y daño considerable en pintura. Alta abrasividad. Rinde 10 autos.',
            'price' => 40000,
        ],
        [
            'sku' => 'IFX-SUPER-500',
            'image' => 'super-compound.jpg',
            'name' => 'Super Compound Pulimento de Corte Medio 500 ml',
            'description' => 'Remueve marcas de lavado, swirls y correcciones ligeras en la pintura. Rinde 10 autos.',
            'price' => 40000,
        ],
        [
            'sku' => 'IFX-GLOSS-500',
            'image' => 'intense-gloss.jpg',
            'name' => 'Intense Gloss Abrillantador Final 500 ml',
            'description' => 'Para un terminado brillante y profesional después de pulir. Sin rellenadores ni siliconas. Rinde 10 autos.',
            'price' => 40000,
        ],
        [
            'sku' => 'IFX-FOAM-1L',
            'image' => 'mr-foam.jpg',
            'name' => 'Mr. Foam Shampoo Ultra Concentrado 1 L',
            'description' => 'Genera alta espuma para prelavado y está adicionado con lubricante para lavado de contacto. Apto para cualquier tipo de pintura. Para uso con hidrolavadora y cubeta. Rinde 40 lavadas.',
            'price' => 15000,
        ],
        [
            'sku' => 'IFX-ARMOR-50',
            'image' => 'armor-9h.jpg',
            'name' => 'Armor 9H Ceramic Coating 50 ml',
            'description' => 'Recubrimiento cerámico con nanotecnología. Protección avanzada hasta por tres años. Brillo, repelencia y cuidado. Se recomienda experiencia previa para aplicarlo.',
            'price' => 60000,
        ],
    ];

    public function run(): void
    {
        $this->removeDemoProducts();

        $channel = Channel::query()->where('handle', 'webstore')->firstOrFail();
        $currency = Currency::query()->where('default', true)->firstOrFail();
        $customerGroup = CustomerGroup::query()->where('default', true)->firstOrFail();
        $taxClass = TaxClass::query()->where('default', true)->firstOrFail();
        $productType = ProductType::query()->orderBy('id')->firstOrFail();
        $brand = Brand::query()->firstOrCreate(['name' => 'IFIX']);
        $collectionGroup = CollectionGroup::query()->firstOrCreate(
            ['handle' => 'main'],
            ['name' => 'Principal'],
        );

        $collection = Collection::query()
            ->where('collection_group_id', $collectionGroup->id)
            ->get()
            ->first(fn (Collection $candidate) => $candidate->translateAttribute('name') === 'Productos destacados')
            ?? Collection::query()->create([
                'collection_group_id' => $collectionGroup->id,
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

        foreach (self::PRODUCTS as $position => $data) {
            $variant = ProductVariant::query()->where('sku', $data['sku'])->first();
            $product = $variant?->product;

            $attributes = [
                'product_type_id' => $productType->id,
                'brand_id' => $brand->id,
                'status' => 'published',
                'attribute_data' => collect([
                    'name' => $this->translatedText($data['name']),
                    'description' => $this->translatedText($data['description']),
                ]),
            ];

            if ($product) {
                $product->update($attributes);
            } else {
                $product = Product::query()->create($attributes);
            }

            if (! $variant) {
                $variant = $product->variants()->create([
                    'tax_class_id' => $taxClass->id,
                    'sku' => $data['sku'],
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'shippable' => true,
                    'stock' => self::INITIAL_STOCK,
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
                    'compare_price' => null,
                ],
            );

            $collection->products()->syncWithoutDetaching([
                $product->id => ['position' => $position + 1],
            ]);

            $imagePath = database_path('seeders/images/ifix/'.$data['image']);
            $media = $product->getFirstMedia('images');

            // Reemplaza la foto cuando el archivo del seeder cambió.
            if (! $media || $media->size !== filesize($imagePath)) {
                $product->clearMediaCollection('images');
                $product->addMedia($imagePath)
                    ->preservingOriginal()
                    ->usingName($data['name'])
                    ->withCustomProperties(['primary' => true])
                    ->toMediaCollection('images');
            }
        }
    }

    private function removeDemoProducts(): void
    {
        ProductVariant::query()
            ->whereIn('sku', self::DEMO_SKUS)
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter()
            ->unique('id')
            ->each(function (Product $product): void {
                $product->variants->each(function (ProductVariant $variant): void {
                    $variant->prices()->delete();
                });

                $product->collections()->detach();
                $product->channels()->detach();
                $product->customerGroups()->detach();
                $product->clearMediaCollection('images');
                $product->variants()->delete();
                $product->forceDelete();
            });
    }

    private function translatedText(string $value): TranslatedText
    {
        return new TranslatedText(collect([
            'es' => new Text($value),
        ]));
    }
}
