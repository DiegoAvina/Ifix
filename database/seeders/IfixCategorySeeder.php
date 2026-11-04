<?php

namespace Database\Seeders;

use App\Services\Storefront\CategoryService;
use Illuminate\Database\Seeder;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\CustomerGroup;
use Lunar\Models\ProductVariant;

/**
 * Categorías de la tienda y su asignación inicial por SKU. Se puede volver
 * a correr: no duplica categorías ni quita lo asignado desde el panel.
 *
 * Depende de LunarStorefrontSeeder; los SKU que no existan se omiten.
 */
class IfixCategorySeeder extends Seeder
{
    private const CATEGORIES = [
        'Protección cerámica' => [
            'IFX-ARMOR-50',
            'IFX-GLASS-125',
            'IFX-NANO-500',
            'IFX-CERA-SIL-200',
            'IFX-CERA-GRF-200',
        ],
        'Pulimentos' => [
            'IFX-HEAVY-500',
            'IFX-SUPER-500',
            'IFX-GLOSS-500',
        ],
        'Limpieza' => [
            'IFX-FOAM-1L',
            'IFX-AXID-1L',
            'IFX-CLAY-BAR',
        ],
        'Restauradores' => [
            'IFX-BR1-500ML',
            'IFX-SHINY-250',
            'IFX-VRT-473',
            'IFX-BLACK-1L',
        ],
        // Sin productos todavía: no se muestra en la tienda hasta asignarle alguno.
        'Accesorios y kits' => [],
    ];

    public function run(): void
    {
        $channel = Channel::query()->where('handle', 'webstore')->firstOrFail();
        $customerGroup = CustomerGroup::query()->where('default', true)->firstOrFail();

        $group = CollectionGroup::query()->firstOrCreate(
            ['handle' => CategoryService::GROUP_HANDLE],
            ['name' => 'Categorías'],
        );

        $availability = [
            'enabled' => true,
            'starts_at' => now(),
            'ends_at' => null,
        ];

        foreach (self::CATEGORIES as $name => $skus) {
            $category = Collection::query()
                ->where('collection_group_id', $group->id)
                ->get()
                ->first(fn (Collection $candidate) => $candidate->translateAttribute('name') === $name);

            if (! $category) {
                $category = Collection::query()->create([
                    'collection_group_id' => $group->id,
                    'attribute_data' => collect([
                        'name' => new TranslatedText(collect(['es' => new Text($name)])),
                    ]),
                ]);

                $category->channels()->syncWithoutDetaching([
                    $channel->id => $availability,
                ]);
                $category->customerGroups()->syncWithoutDetaching([
                    $customerGroup->id => [...$availability, 'visible' => true],
                ]);
            }

            foreach ($skus as $position => $sku) {
                $productId = ProductVariant::query()->where('sku', $sku)->value('product_id');

                if ($productId) {
                    $category->products()->syncWithoutDetaching([
                        $productId => ['position' => $position + 1],
                    ]);
                }
            }
        }
    }
}
