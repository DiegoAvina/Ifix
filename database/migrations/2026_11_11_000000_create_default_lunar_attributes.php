<?php

use Illuminate\Database\Migrations\Migration;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

/**
 * Atributos "Nombre" y "Descripción" de productos y categorías, los mismos
 * que crea `lunar:install`. Esta tienda se instaló sin ellos, así que el
 * panel no mostraba esos campos y "New Product" fallaba al crear.
 *
 * Los productos y categorías ya guardan su nombre y descripción con estos
 * mismos identificadores (attribute_data.name / .description), así que no
 * se modifica ningún dato existente. Se puede volver a correr sin duplicar.
 */
return new class extends Migration
{
    public function up(): void
    {
        $groups = [
            Product::morphName() => AttributeGroup::query()->firstOrCreate(
                ['handle' => 'details'],
                ['attributable_type' => Product::morphName(), 'name' => collect(['es' => 'Detalles', 'en' => 'Detalles']), 'position' => 1],
            ),
            Collection::morphName() => AttributeGroup::query()->firstOrCreate(
                ['handle' => 'collection_details'],
                ['attributable_type' => Collection::morphName(), 'name' => collect(['es' => 'Detalles', 'en' => 'Detalles']), 'position' => 1],
            ),
        ];

        foreach ($groups as $type => $group) {
            foreach ([
                ['handle' => 'name', 'label' => 'Nombre', 'required' => true, 'richtext' => false, 'system' => true, 'position' => 1],
                ['handle' => 'description', 'label' => 'Descripción', 'required' => false, 'richtext' => true, 'system' => false, 'position' => 2],
            ] as $attribute) {
                Attribute::query()->firstOrCreate(
                    ['attribute_type' => $type, 'handle' => $attribute['handle']],
                    [
                        'attribute_group_id' => $group->id,
                        'position' => $attribute['position'],
                        // "en" también en español: el panel de Lunar está en inglés y toma esa clave.
                        'name' => ['es' => $attribute['label'], 'en' => $attribute['label']],
                        'description' => ['es' => '', 'en' => ''],
                        'section' => 'main',
                        'type' => TranslatedText::class,
                        'required' => $attribute['required'],
                        'default_value' => null,
                        'configuration' => ['richtext' => $attribute['richtext']],
                        'system' => $attribute['system'],
                    ],
                );
            }
        }

        // El formulario de producto muestra los atributos de su tipo.
        $productAttributes = Attribute::query()->where('attribute_type', Product::morphName())->pluck('id');

        ProductType::query()->each(
            fn (ProductType $type) => $type->mappedAttributes()->syncWithoutDetaching($productAttributes)
        );
    }

    public function down(): void
    {
        // No se borran: sin ellos el panel deja de poder editar nombres y descripciones.
    }
};
