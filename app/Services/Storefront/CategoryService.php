<?php

namespace App\Services\Storefront;

use App\Support\LunarAttribute;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Collection;
use Lunar\Models\Product;

/**
 * Categorías de la tienda: colecciones del grupo "Categorías" de Lunar.
 * Se gestionan en el panel (Catalog > Collection Groups); las que no
 * tienen productos a la venta no se muestran.
 */
class CategoryService
{
    public const GROUP_HANDLE = 'categorias';

    private const ICONS = [
        'Protección cerámica' => 'shield',
        'Pulimentos' => 'flare',
        'Limpieza' => 'water_drop',
        'Restauradores' => 'auto_fix_high',
        'Accesorios y kits' => 'inventory_2',
    ];

    private const DEFAULT_ICON = 'category';

    private const DESCRIPTIONS = [
        'Protección cerámica' => 'Selladores y recubrimientos de larga duración',
        'Pulimentos' => 'Corrige rayones y recupera el brillo',
        'Limpieza' => 'Shampoos y limpiadores para cada superficie',
        'Restauradores' => 'Devuelve color y vida a plásticos y llantas',
        'Accesorios y kits' => 'Todo lo que necesitas para el detallado',
    ];

    public function __construct(
        protected StorefrontAvailabilityQuery $storefrontAvailability,
    ) {}

    /**
     * @return array<int, array{id: int, name: string, icon: string, count: int, url: string}>
     */
    public function all(): array
    {
        return $this->storefrontAvailability
            ->apply(Collection::query())
            ->whereHas('group', fn (Builder $group) => $group->where('handle', self::GROUP_HANDLE))
            ->withCount(['products' => fn (Builder $products) => $this->storefrontAvailability
                ->apply($products->where('status', 'published'))])
            ->defaultOrder()
            ->get()
            ->where('products_count', '>', 0)
            ->map(function (Collection $collection): array {
                $name = LunarAttribute::text($collection->attribute_data, 'name');

                return [
                    'id' => $collection->id,
                    'name' => $name,
                    'icon' => self::ICONS[$name] ?? self::DEFAULT_ICON,
                    'count' => $collection->products_count,
                    'url' => route('catalog.index', ['collection' => [$collection->id]]),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Categorías con descripción y la foto de un producto visible, para las
     * tarjetas del inicio. Va aparte de all() porque el navbar llama a all()
     * en cada página y no necesita imágenes.
     *
     * @return array<int, array{id: int, name: string, icon: string, count: int, url: string, description: ?string, image: ?string}>
     */
    public function forHome(): array
    {
        return array_map(function (array $category): array {
            $product = $this->storefrontAvailability
                ->apply(Product::query()->where('status', 'published'))
                ->whereHas('collections', fn (Builder $collections) => $collections->whereKey($category['id']))
                ->whereHas('media', fn (Builder $media) => $media->where('collection_name', 'images'))
                ->with('media')
                ->orderBy('id')
                ->first();

            return $category + [
                'description' => self::DESCRIPTIONS[$category['name']] ?? null,
                'image' => $product?->getFirstMediaUrl('images') ?: null,
            ];
        }, $this->all());
    }
}
