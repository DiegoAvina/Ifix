<?php

namespace App\Data\Order;

final readonly class OrderLineData
{
    public function __construct(
        public int $id,
        public string $description,
        public ?string $identifier,
        public int $quantity,
        public string $unitPrice,
        public string $total,
        public string $type,
    ) {}
}
