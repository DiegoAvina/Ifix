<?php

namespace App\Data;

final readonly class ProductData
{
    public function __construct(

        public int $id,

        public string $name,

        public ?string $slug,

        public BrandData $brand,

        public PriceData $price,

        public InventoryData $inventory,

        public ?int $variantId,

        public ?ImageData $image,

        /** @var ImageData[] */
        public array $gallery,

        /** @var BadgeData[] */
        public array $badges,

        public ?string $description,

        /** @var SpecificationData[] */
        public array $specifications,

        /** @var ProductData[] */
        public array $relatedProducts,

        public string $url,

        public string $addToCartUrl,

        /**
         * Ficha técnica (grupo "Ficha técnica" del panel); solo los datos capturados.
         *
         * @var array<int, array{icon: string, label: string, value: string}>
         */
        public array $facts = [],

        /**
         * Modo de uso: pasos en orden.
         *
         * @var string[]
         */
        public array $howTo = [],

    ) {}
}