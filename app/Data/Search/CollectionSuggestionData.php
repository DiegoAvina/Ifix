<?php

namespace App\Data\Search;

final readonly class CollectionSuggestionData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $image,
        public string $url,
    ) {}
}