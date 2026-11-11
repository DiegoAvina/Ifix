<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;

/**
 * Zona de envío: un grupo de prefijos de código postal (2 dígitos = estado)
 * con su precio y días de entrega. Se edita en el panel (Zonas de envío).
 */
class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'postcode_prefixes',
        'price',
        'min_days',
        'max_days',
        'is_default',
        'position',
    ];

    protected $casts = [
        'postcode_prefixes' => 'array',
        'price' => 'decimal:2',
        'min_days' => 'integer',
        'max_days' => 'integer',
        'is_default' => 'boolean',
        'position' => 'integer',
    ];
}
