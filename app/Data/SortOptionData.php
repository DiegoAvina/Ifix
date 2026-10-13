<?php

namespace App\Data;

final readonly class SortOptionData
{
    public function __construct(

        public string $value,

        public string $label,

        public bool $selected = false,

    ) {
    }
}