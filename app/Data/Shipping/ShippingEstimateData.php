<?php

namespace App\Data\Shipping;

final readonly class ShippingEstimateData
{
    public function __construct(
        public string $postalCode,
        public \Illuminate\Support\Carbon $processingDate,
        public \Illuminate\Support\Carbon $from,
        public \Illuminate\Support\Carbon $to,
        public bool $freeShipping,
        public int $shippingCost,
        public string $message,
    ) {}
}
