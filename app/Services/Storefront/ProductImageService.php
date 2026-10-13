<?php

namespace App\Services\Storefront;

use App\Data\ImageData;

class ProductImageService
{
    /**
     * Imagen principal.
     */
    public function primary($product): ?ImageData
    {
        $media = $product->getFirstMedia('images');

        if (! $media) {
            return null;
        }

        return new ImageData(

            url: $media->getUrl(),

            name: $media->name,

            alt: $media->getCustomProperty('name') ?: $media->name,

            primary: true,

        );
    }

    /**
     * Galería.
     *
     * @return ImageData[]
     */
    public function gallery($product): array
    {
        return $product
            ->getMedia('images')
            ->map(function ($media) {

                return new ImageData(

                    url: $media->getUrl(),

                    name: $media->name,

                    alt: $media->getCustomProperty('name') ?: $media->name,

                    primary: (bool) $media->getCustomProperty('primary', false),

                );

            })
            ->toArray();
    }
}