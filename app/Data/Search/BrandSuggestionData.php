<?php

namespace App\Data\Search;

final readonly class BrandSuggestionData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $logo,
        public string $url,
    ) {}
}