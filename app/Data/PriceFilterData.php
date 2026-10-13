<?php

namespace App\Data;

final readonly class PriceFilterData
{
    public function __construct(

        public float $min,

        public float $max,

        public float $selectedMin,

        public float $selectedMax,

    ) {
    }
}