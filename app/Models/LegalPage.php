<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Página legal editable desde el panel de Lunar (LegalPageResource).
 * El contenido es HTML del editor: se imprime siempre pasado por
 * App\Support\SafeHtml::clean().
 */
class LegalPage extends Model
{
    public const PRIVACY = 'aviso-de-privacidad';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function url(): string
    {
        return $this->slug === self::PRIVACY
            ? route('legal.privacy')
            : route('legal.show', $this->slug);
    }
}
