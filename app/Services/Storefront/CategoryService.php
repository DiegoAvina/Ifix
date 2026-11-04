<?php

namespace App\Services\Storefront;

use App\Support\LunarAttribute;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Collection;

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
}
