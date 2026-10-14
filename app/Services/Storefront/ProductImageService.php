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
            return $this->placeholder($product);
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
        $images = $product
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

        return $images ?: [$this->placeholder($product)];
    }

    private function placeholder($product): ImageData
    {
        $name = $product->translateAttribute('name') ?: 'Producto IFIX';

        return new ImageData(
            url: asset('images/demo-detailing-product.svg'),
            name: $name,
            alt: $name,
            primary: true,
        );
    }
}