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

    public const TERMS = 'terminos-de-servicio';

    /**
     * Páginas con dirección propia (/aviso-de-privacidad, /terminos-de-servicio)
     * enlazadas desde el registro, el checkout, el footer y los correos: su
     * dirección no se cambia y no se pueden borrar desde el panel.
     */
    public const FIXED = [
        self::PRIVACY => 'legal.privacy',
        self::TERMS => 'legal.terms',
    ];

    public function isFixed(): bool
    {
        return array_key_exists((string) $this->slug, self::FIXED);
    }

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
        return $this->isFixed()
            ? route(self::FIXED[$this->slug])
            : route('legal.show', $this->slug);
    }
}
