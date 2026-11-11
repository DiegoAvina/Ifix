<?php

namespace App\Data\Shipping;

/**
 * Resultado del cálculo de envío. Montos en pesos.
 */
final readonly class ShippingQuoteData
{
    public function __construct(
        public string $zoneName,
        public float $basePrice,
        public float $weightSurcharge,
        public float $extendedSurcharge,
        public float $total,
        public bool $freeShipping,
        public bool $extendedZone,
        public bool $postcodeKnown,
        public float $weightKg,
        public int $minDays,
        public int $maxDays,
        public int $processingDays,
    ) {}
}
