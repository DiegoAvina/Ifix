<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Order;

/**
 * Registro de idempotencia para notificaciones de proveedores de pago.
 *
 * No es un reemplazo de Lunar\Models\Transaction — Transaction sigue
 * siendo el registro de negocio del pago. Esta tabla solo evita
 * procesar la misma notificación entrante más de una vez.
 */
class PaymentWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'payment_id',
        'status',
        'order_id',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
