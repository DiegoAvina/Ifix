<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;

/**
 * Código postal de zona extendida (poblaciones donde la paquetería cobra
 * un cargo extra). La lista se importa desde el panel.
 */
class ShippingExtendedPostcode extends Model
{
    protected $fillable = ['postcode'];
}
