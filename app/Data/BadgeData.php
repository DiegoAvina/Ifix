<?php

namespace App\Data;

final readonly class BadgeData
{
    public function __construct(

        public string $type,

        public string $label,

        public string $icon,

    ) {
    }
}