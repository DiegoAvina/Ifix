<?php

namespace App\Data;

final readonly class PriceData
{
    public function __construct(

        public float $raw,

        public string $formatted,

        public string $currency,

        public ?string $compareFormatted = null,

        public ?int $discountPercent = null,

    ) {
    }
}
