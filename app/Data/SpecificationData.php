<?php

namespace App\Data;

final readonly class SpecificationData
{
    public function __construct(
        public string $label,
        public string $value,
    ) {}
}