<?php

namespace App\Services\Shipping;

use App\Data\Shipping\ShippingQuoteData;
use App\Models\Shipping\ShippingExtendedPostcode;
use App\Models\Shipping\ShippingSetting;
use App\Models\Shipping\ShippingZone;
use Illuminate\Support\Collection;

/**
 * Calcula el costo de envío con las tarifas del panel:
 *
 *   precio de la zona (por los 2 primeros dígitos del C.P.)
 * + recargo por peso del pedido
 * + recargo de zona extendida (si el C.P. está en la lista)
 *
 * Envío gratis a partir del monto configurado. Ese beneficio cubre el
 * precio de la zona y el peso; el recargo de zona extendida solo si así
 * se configura.
 */
class ShippingRateCalculator
{
    private ?ShippingSetting $settings = null;

    private ?Collection $zones = null;

    public function quote(?string $postcode, float $cartTotal, float $weightKg): ShippingQuoteData
    {
        $settings = $this->settings();
        $postcode = $this->normalize($postcode);
        $zone = $this->zoneFor($postcode);

        $extended = $postcode !== null
            && ShippingExtendedPostcode::query()->where('postcode', $postcode)->exists();

        $base = (float) ($zone?->price ?? config('shipping.standard_cost'));
        $weightSurcharge = $this->weightSurcharge($weightKg, $settings);
        $extendedSurcharge = $extended ? (float) $settings->extended_surcharge : 0.0;

        $freeFrom = $settings->free_shipping_from !== null ? (float) $settings->free_shipping_from : null;
        $free = $freeFrom !== null && $cartTotal >= $freeFrom;

        $total = $free
            ? ($settings->free_shipping_covers_extended ? 0.0 : $extendedSurcharge)
            : $base + $weightSurcharge + $extendedSurcharge;

        $extraDays = $extended ? $settings->extended_extra_days : 0;

        return new ShippingQuoteData(
            zoneName: $zone?->name ?? 'Estándar',
            basePrice: $base,
            weightSurcharge: $weightSurcharge,
            extendedSurcharge: $extendedSurcharge,
            total: round($total, 2),
            freeShipping: $free && $total == 0.0,
            extendedZone: $extended,
            postcodeKnown: $postcode !== null,
            weightKg: round($weightKg, 3),
            minDays: ($zone?->min_days ?? config('shipping.delivery.min_days')) + $extraDays,
            maxDays: ($zone?->max_days ?? config('shipping.delivery.max_days')) + $extraDays,
            processingDays: $settings->processing_days,
        );
    }

    public function settings(): ShippingSetting
    {
        return $this->settings ??= ShippingSetting::current();
    }

    private function zoneFor(?string $postcode): ?ShippingZone
    {
        $this->zones ??= ShippingZone::query()->orderBy('position')->get();

        if ($postcode !== null) {
            $prefix = substr($postcode, 0, 2);
            $match = $this->zones->first(
                fn (ShippingZone $zone) => in_array($prefix, array_map('strval', $zone->postcode_prefixes ?? []), true)
            );

            if ($match) {
                return $match;
            }
        }

        return $this->zones->firstWhere('is_default', true) ?? $this->zones->first();
    }

    private function weightSurcharge(float $weightKg, ShippingSetting $settings): float
    {
        $brackets = collect($settings->weight_brackets ?? [])
            ->map(fn ($b) => ['max_kg' => (float) ($b['max_kg'] ?? 0), 'surcharge' => (float) ($b['surcharge'] ?? 0)])
            ->sortBy('max_kg')
            ->values();

        if ($brackets->isEmpty()) {
            return 0.0;
        }

        $bracket = $brackets->first(fn ($b) => $weightKg <= $b['max_kg']);

        if ($bracket) {
            return $bracket['surcharge'];
        }

        // Más pesado que el último rango: su recargo + cada kg extra.
        $last = $brackets->last();

        return $last['surcharge'] + ceil($weightKg - $last['max_kg']) * (float) $settings->extra_per_kg;
    }

    private function normalize(?string $postcode): ?string
    {
        $postcode = preg_replace('/\D/', '', (string) $postcode);

        return strlen($postcode) === 5 ? $postcode : null;
    }
}
