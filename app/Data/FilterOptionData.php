<?php

namespace App\Data;

final readonly class FilterOptionData
{
    public function __construct(
        public string|int $value,
        public string $label,
        public int $count,
        public bool $selected,
        public string $url,
    ) {}
}