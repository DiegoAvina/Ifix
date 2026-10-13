<?php

namespace App\DTOs\Storefront;

class PaginationData
{
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
        public bool $hasMorePages,
    ) {}
}