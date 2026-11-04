<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'subtitle',
        'image',
        'button_text',
        'button_url',
        'active',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Slides activos y dentro de su periodo de vigencia. Sin fechas,
     * el slide se muestra mientras esté activo.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
