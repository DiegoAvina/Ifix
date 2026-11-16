<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Página "Nosotros": misión, visión, valores, cifras e historia.
 * Una sola fila, editada en el panel de Lunar (App\Filament\Pages\AboutPageSettings).
 * Todo el contenido es texto plano: la vista lo escapa al imprimirlo.
 */
class AboutPage extends Model
{
    private const CACHE_KEY = 'about.page';

    /**
     * Íconos que se pueden elegir para cada valor (nombres de Material Symbols).
     * Lista cerrada: evita nombres mal escritos que se verían como texto.
     */
    public const ICONS = [
        'verified' => 'Calidad garantizada',
        'handshake' => 'Confianza / trato',
        'favorite' => 'Pasión',
        'auto_awesome' => 'Excelencia / brillo',
        'diamond' => 'Premium',
        'bolt' => 'Rapidez / energía',
        'eco' => 'Sostenibilidad',
        'groups' => 'Equipo / comunidad',
        'lightbulb' => 'Innovación',
        'shield' => 'Protección',
        'support_agent' => 'Servicio al cliente',
        'school' => 'Aprendizaje',
        'target' => 'Enfoque / precisión',
        'local_car_wash' => 'Detallado',
        'directions_car' => 'Autos',
        'workspace_premium' => 'Profesionalismo',
        'balance' => 'Honestidad',
        'rocket_launch' => 'Crecimiento',
    ];

    protected $fillable = [
        'eyebrow',
        'headline',
        'intro',
        'mission',
        'vision',
        'values',
        'process',
        'stats',
        'milestones',
        'closing_title',
        'closing_text',
        'published',
    ];

    protected $casts = [
        'values' => 'array',
        'process' => 'array',
        'stats' => 'array',
        'milestones' => 'array',
        'published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * La fila guardada o, si aún no se ha guardado nada en el panel, el
     * contenido inicial de defaults().
     */
    public static function current(): self
    {
        // Se cachean los atributos crudos, no el modelo (ver ShippingSetting).
        // El menú de todas las páginas lo consulta: si la tabla aún no existe
        // (migración pendiente), se usa el contenido inicial en vez de fallar.
        try {
            $attributes = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->first()?->getAttributes());
        } catch (QueryException) {
            $attributes = null;
        }

        if ($attributes) {
            return (new static)->newFromBuilder($attributes);
        }

        return new static(static::defaults());
    }

    /**
     * Contenido inicial, para que la página no salga vacía antes de editarla.
     */
    public static function defaults(): array
    {
        return [
            'eyebrow' => 'Nosotros',
            'headline' => 'Cuidamos tu auto como si fuera nuestro',
            'intro' => 'En IFIX reunimos productos profesionales de detallado automotriz para que cualquier persona logre resultados de taller desde su cochera.',
            'mission' => 'Acercar productos de detallado automotriz de calidad profesional, con asesoría honesta y envíos confiables, para que cada cliente disfrute un auto impecable.',
            'vision' => 'Ser la tienda de detallado automotriz de referencia en México, reconocida por la calidad de sus productos, la cercanía con su comunidad y la experiencia de compra.',
            'values' => [
                ['icon' => 'verified', 'title' => 'Calidad', 'description' => 'Solo vendemos productos que probamos y en los que confiamos.'],
                ['icon' => 'handshake', 'title' => 'Honestidad', 'description' => 'Te recomendamos lo que tu auto necesita, no lo más caro.'],
                ['icon' => 'support_agent', 'title' => 'Servicio', 'description' => 'Te acompañamos antes, durante y después de tu compra.'],
                ['icon' => 'auto_awesome', 'title' => 'Pasión', 'description' => 'Amamos los autos y el detalle bien hecho.'],
            ],
            'process' => [
                ['title' => 'Lavar', 'text' => 'Shampoo de alta espuma que levanta la suciedad sin rayar la pintura.', 'category' => 'limpieza'],
                ['title' => 'Descontaminar', 'text' => 'Clay bar y limpiador ácido para retirar sarro y contaminación incrustada.', 'category' => 'limpieza'],
                ['title' => 'Corregir', 'text' => 'Pulimentos de corte alto y medio que borran rayones y swirls.', 'category' => 'pulimentos'],
                ['title' => 'Proteger', 'text' => 'Recubrimiento cerámico 9H, ceras de grafeno y selladores hidrofóbicos.', 'category' => 'proteccion-ceramica'],
                ['title' => 'Restaurar', 'text' => 'Plásticos negros otra vez negros y llantas con brillo profundo.', 'category' => 'restauradores'],
            ],
            'stats' => [],
            'milestones' => [],
            'closing_title' => '¿Listo para que tu auto brille?',
            'closing_text' => 'Explora nuestro catálogo y encuentra todo para el cuidado de tu auto.',
            'published' => true,
        ];
    }

    /**
     * Elementos de una lista sin filas vacías y con ícono válido.
     *
     * @return array<int, array<string, string>>
     */
    public function valueItems(): array
    {
        return collect($this->values ?? [])
            ->filter(fn ($item) => filled($item['title'] ?? null))
            ->map(fn ($item) => [
                'icon' => array_key_exists($item['icon'] ?? '', self::ICONS) ? $item['icon'] : 'verified',
                'title' => (string) $item['title'],
                'description' => (string) ($item['description'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * Pasos del proceso; cada uno con la categoría de la tienda a la que
     * enlaza (si sigue visible), con su ícono y número de productos.
     *
     * @param  array<int, array{slug: ?string, name: string, icon: string, count: int, url: string}>  $categories
     */
    public function processItems(array $categories): array
    {
        $bySlug = collect($categories)->keyBy('slug');

        return collect($this->process ?? [])
            ->filter(fn ($item) => filled($item['title'] ?? null))
            ->map(fn ($item) => [
                'title' => (string) $item['title'],
                'text' => (string) ($item['text'] ?? ''),
                'category' => $bySlug->get($item['category'] ?? null),
            ])
            ->values()
            ->all();
    }

    public function statItems(): array
    {
        return collect($this->stats ?? [])
            ->filter(fn ($item) => filled($item['value'] ?? null) && filled($item['label'] ?? null))
            ->values()
            ->all();
    }

    public function milestoneItems(): array
    {
        return collect($this->milestones ?? [])
            ->filter(fn ($item) => filled($item['title'] ?? null))
            ->values()
            ->all();
    }
}
