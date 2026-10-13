<?php

namespace App\Data;

final readonly class PriceData
{
    public function __construct(

        public float $raw,

        public string $formatted,

        public string $currency,

    ) {
    }
}