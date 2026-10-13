<?php

namespace App\Data;

final readonly class BrandData
{
    public function __construct(

        public ?int $id,

        public ?string $name,

    ) {
    }
}