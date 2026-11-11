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
        // Pesos (puede tener centavos).
        public float $shippingCost,
        public string $message,
        public string $zoneName = 'Estándar',
        public bool $extendedZone = false,
        public float $extendedSurcharge = 0,
        public bool $postcodeKnown = false,
    ) {}
}
