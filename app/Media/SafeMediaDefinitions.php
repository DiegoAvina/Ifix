<?php

namespace App\Media;

use Lunar\Base\StandardMediaDefinitions;
use Spatie\MediaLibrary\HasMedia;

/**
 * Igual que la definición estándar de Lunar, pero la colección de
 * imágenes solo acepta formatos raster. El panel de Lunar sube archivos
 * sin restringir el tipo; un SVG o HTML se publicaría en /storage y
 * podría ejecutar JavaScript en nuestro dominio.
 */
class SafeMediaDefinitions extends StandardMediaDefinitions
{
    public const ACCEPTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public function registerMediaCollections(HasMedia $model): void
    {
        parent::registerMediaCollections($model);

        foreach ($model->mediaCollections as $collection) {
            if ($collection->name === config('lunar.media.collection')) {
                $collection->acceptsMimeTypes(self::ACCEPTED_MIME_TYPES);
            }
        }
    }
}
