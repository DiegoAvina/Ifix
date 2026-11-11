<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Ajustes generales de envío (una sola fila). Se editan en el panel
 * (Ajustes de envío).
 */
class ShippingSetting extends Model
{
    protected $fillable = [
        'origin_postcode',
        'free_shipping_from',
        'free_shipping_covers_extended',
        'extended_surcharge',
        'extended_extra_days',
        'processing_days',
        'default_item_weight_kg',
        'weight_brackets',
        'extra_per_kg',
    ];

    protected $casts = [
        'free_shipping_from' => 'decimal:2',
        'free_shipping_covers_extended' => 'boolean',
        'extended_surcharge' => 'decimal:2',
        'extended_extra_days' => 'integer',
        'processing_days' => 'integer',
        'default_item_weight_kg' => 'decimal:3',
        'weight_brackets' => 'array',
        'extra_per_kg' => 'decimal:2',
    ];

    /**
     * La fila de ajustes; si no existe (p. ej. base nueva sin migrar datos),
     * valores por defecto de config/shipping.php.
     */
    private const CACHE_KEY = 'shipping.settings';

    protected static function booted(): void
    {
        // El monto de envío gratis sale en el menú de cada página: se cachea
        // y se refresca al guardar en el panel.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        // Se cachean los datos crudos, no el modelo: la caché de Laravel no
        // reconstruye objetos al leer (serializable_classes), por seguridad.
        $attributes = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->first()?->getAttributes());

        if ($attributes) {
            return (new static)->newFromBuilder($attributes);
        }

        return new static([
            'origin_postcode' => '44720',
            'free_shipping_from' => config('shipping.free_shipping_from'),
            'free_shipping_covers_extended' => false,
            'extended_surcharge' => 0,
            'extended_extra_days' => 0,
            'processing_days' => config('shipping.processing_days', 1),
            'default_item_weight_kg' => 1,
            'weight_brackets' => [],
            'extra_per_kg' => 0,
        ]);
    }
}
