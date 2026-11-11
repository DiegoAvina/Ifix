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

    /**
     * Textos para buscadores de cada página de categoría (/categorias/{slug}).
     * Una categoría nueva creada en el panel usa textos genéricos con su nombre.
     */
    private const SEO = [
        'Protección cerámica' => [
            'title' => 'Recubrimiento cerámico y selladores para auto',
            'description' => 'Recubrimientos cerámicos 9H, ceras de grafeno y silicio, selladores para cristales y quick detailers IFIX. Brillo espejo, repelencia al agua y protección UV. Envíos a todo México.',
            'intro' => 'Protege la pintura y los cristales de tu auto con recubrimientos cerámicos, ceras con nanotecnología y selladores de larga duración. Brillo profundo, efecto hidrofóbico y protección contra rayos UV, desde mantenimiento rápido hasta protección de hasta tres años.',
        ],
        'Pulimentos' => [
            'title' => 'Pulimentos para auto: corte alto, medio y abrillantador',
            'description' => 'Pulimentos IFIX de corte alto y medio y abrillantador final para eliminar rayones, swirls y marcas de lavado. Corrección de pintura con acabado profesional. Envíos a todo México.',
            'intro' => 'Corrige la pintura de tu auto en tres pasos: un pulimento de corte alto para rayones profundos, uno de corte medio para swirls y marcas de lavado, y un abrillantador final para un acabado espejo sin rellenadores ni siliconas.',
        ],
        'Limpieza' => [
            'title' => 'Shampoo para auto y limpiadores de detallado',
            'description' => 'Shampoo ultra concentrado para auto, limpiador ácido para sarro y lluvia ácida, clay bar descontaminante y más productos de limpieza IFIX para detallado automotriz.',
            'intro' => 'Todo para un lavado y una descontaminación seguros: shampoo de alta espuma para prelavado y lavado de contacto, limpiador ácido para manchas de sarro y lluvia ácida, y clay bar para retirar contaminación incrustada antes de pulir o encerar.',
        ],
        'Restauradores' => [
            'title' => 'Restaurador de plásticos y abrillantador de llantas',
            'description' => 'Restauradores de plástico negro, acondicionadores con grafeno para interiores y exteriores y abrillantador de llantas base agua IFIX. Devuelve color y vida a tu auto.',
            'intro' => 'Recupera el color de plásticos resecos y protege los que están nuevos. Resinas de larga duración para plástico negro, acondicionadores con grafeno para interior y exterior, y abrillantador de llantas base agua que no deja residuos.',
        ],
        'Accesorios y kits' => [
            'title' => 'Accesorios para detallado automotriz',
            'description' => 'Microfibras, guantes descontaminantes y accesorios para detallado automotriz. Complementa tus productos IFIX. Envíos a todo México.',
            'intro' => 'Los accesorios correctos evitan rayones y te ayudan a aplicar cada producto como un profesional: toallas de microfibra, guantes descontaminantes y más.',
        ],
    ];

    public function __construct(
        protected StorefrontAvailabilityQuery $storefrontAvailability,
    ) {}

    /**
     * @return array<int, array{id: int, name: string, slug: ?string, icon: string, count: int, url: string}>
     */
    public function all(): array
    {
        return $this->storefrontAvailability
            ->apply(Collection::query())
            ->whereHas('group', fn (Builder $group) => $group->where('handle', self::GROUP_HANDLE))
            ->withCount(['products' => fn (Builder $products) => $this->storefrontAvailability
                ->apply($products->where('status', 'published'))])
            ->with('defaultUrl')
            ->defaultOrder()
            ->get()
            ->where('products_count', '>', 0)
            ->map(function (Collection $collection): array {
                $name = LunarAttribute::text($collection->attribute_data, 'name');
                $slug = $collection->defaultUrl?->slug;

                return [
                    'id' => $collection->id,
                    'name' => $name,
                    'slug' => $slug,
                    'icon' => self::ICONS[$name] ?? self::DEFAULT_ICON,
                    'count' => $collection->products_count,
                    // Página propia indexable; sin slug, el filtro del catálogo.
                    'url' => $slug
                        ? route('catalog.category', $slug)
                        : route('catalog.index', ['collection' => [$collection->id]]),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Categoría visible por su slug, con sus textos para buscadores.
     *
     * @return array{id: int, name: string, slug: string, icon: string, count: int, url: string, title: string, description: string, intro: string}|null
     */
    public function findBySlug(string $slug): ?array
    {
        $category = collect($this->all())->firstWhere('slug', $slug);

        return $category ? $category + $this->seo($category['name']) : null;
    }

    /**
     * Categoría visible por id (para redirigir el filtro viejo a su página).
     */
    public function find(int $id): ?array
    {
        return collect($this->all())->firstWhere('id', $id);
    }

    /**
     * @return array{title: string, description: string, intro: string}
     */
    private function seo(string $name): array
    {
        return self::SEO[$name] ?? [
            'title' => $name.' para detallado automotriz',
            'description' => "Compra {$name} para detallado automotriz en la tienda oficial IFIX. Productos profesionales con envíos a todo México.",
            'intro' => "Productos de {$name} para el cuidado y detallado profesional de tu auto.",
        ];
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
