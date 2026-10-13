<?php

namespace App\Data\Order;

final readonly class OrderData
{
    /**
     * @param OrderLineData[] $lines
     */
    public function __construct(
        public int $id,
        public ?string $reference,
        public string $status,
        public string $statusLabel,
        public bool $isPlaced,
        public \Illuminate\Support\Carbon $createdAt,
        public array $lines,
        public string $subtotal,
        public string $tax,
        public string $shipping,
        public string $total,
        public ?OrderAddressData $shippingAddress,
        public ?OrderAddressData $billingAddress,
    ) {}
}
