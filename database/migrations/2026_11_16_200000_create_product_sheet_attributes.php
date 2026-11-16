<?php

use Illuminate\Database\Migrations\Migration;
use Lunar\FieldTypes\ListField;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

/**
 * Grupo "Ficha técnica" en el formulario de producto del panel de Lunar:
 * contenido, rendimiento, superficie, aplicación y modo de uso (lista de
 * pasos). Se muestran en la página del producto; los vacíos no aparecen.
 * Se puede volver a correr sin duplicar.
 */
return new class extends Migration
{
    public const ATTRIBUTES = [
        ['handle' => 'contenido', 'label' => 'Contenido', 'help' => 'Ej.: 500 ml, 1 L, 200 g. Si se deja vacío, se toma del nombre.', 'type' => Text::class],
        ['handle' => 'rendimiento', 'label' => 'Rendimiento', 'help' => 'Ej.: Rinde 10 autos.', 'type' => Text::class],
        ['handle' => 'superficie', 'label' => 'Superficie', 'help' => 'Ej.: Pintura, cristales, plásticos.', 'type' => Text::class],
        ['handle' => 'aplicacion', 'label' => 'Aplicación', 'help' => 'Ej.: A mano o con pulidora.', 'type' => Text::class],
        ['handle' => 'modo_de_uso', 'label' => 'Modo de uso', 'help' => 'Un paso por renglón, en orden. En «Clave» el número del paso (1, 2, 3…) y en «Valor» la indicación.', 'type' => ListField::class],
    ];

    public function up(): void
    {
        $group = AttributeGroup::query()->firstOrCreate(
            ['handle' => 'ficha_tecnica'],
            ['attributable_type' => Product::morphName(), 'name' => collect(['es' => 'Ficha técnica', 'en' => 'Ficha técnica']), 'position' => 2],
        );

        $ids = collect(self::ATTRIBUTES)->map(fn (array $attribute, int $i) => Attribute::query()->firstOrCreate(
            ['attribute_type' => Product::morphName(), 'handle' => $attribute['handle']],
            [
                'attribute_group_id' => $group->id,
                'position' => $i + 1,
                // "en" también en español: el panel puede tomar esa clave.
                'name' => ['es' => $attribute['label'], 'en' => $attribute['label']],
                'description' => ['es' => $attribute['help'], 'en' => $attribute['help']],
                'section' => 'main',
                'type' => $attribute['type'],
                'required' => false,
                'default_value' => null,
                'configuration' => $attribute['type'] === Text::class ? ['richtext' => false] : [],
                'system' => false,
                'searchable' => false,
                'filterable' => false,
            ],
        )->id);

        // El formulario de producto muestra los atributos de su tipo.
        ProductType::query()->each(
            fn (ProductType $type) => $type->mappedAttributes()->syncWithoutDetaching($ids)
        );
    }

    public function down(): void
    {
        $attributes = Attribute::query()
            ->where('attribute_type', Product::morphName())
            ->whereIn('handle', collect(self::ATTRIBUTES)->pluck('handle'))
            ->get();

        ProductType::query()->each(fn (ProductType $type) => $type->mappedAttributes()->detach($attributes->pluck('id')));
        $attributes->each->delete();
        AttributeGroup::query()->where('handle', 'ficha_tecnica')->delete();
    }
};
