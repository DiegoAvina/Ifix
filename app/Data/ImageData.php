<?php

namespace App\Data;

final readonly class ImageData
{
    public function __construct(

        /**
         * URL de la imagen.
         */
        public string $url,

        /**
         * Nombre del archivo.
         */
        public string $name,

        /**
         * Texto alternativo.
         */
        public ?string $alt,

        /**
         * Es la imagen principal.
         */
        public bool $primary,

    ) {
    }
}