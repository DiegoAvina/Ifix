<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Envío gratis aplicado a un pedido desde el panel (ver FreeShippingService).
 */
class OrderFreeShipping extends Model
{
    protected $fillable = ['order_id', 'active', 'state'];

    protected $casts = [
        'active' => 'boolean',
        'state' => 'array',
    ];
}
