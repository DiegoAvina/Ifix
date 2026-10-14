<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Country;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;

class DefaultTaxConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        if (! TaxClass::query()->where('default', true)->exists()) {
            $taxClass = TaxClass::query()->firstOrCreate(
                ['name' => 'General'],
                ['default' => false],
            );
            $taxClass->default = true;
            $taxClass->save();
        }

        $taxZone = TaxZone::query()->where('default', true)->first();

        if (! $taxZone) {
            $taxZone = TaxZone::query()->firstOrCreate(
                ['name' => 'Default Tax Zone'],
                [
                    'zone_type' => 'country',
                    'price_display' => 'tax_exclusive',
                    'active' => true,
                    'default' => true,
                ],
            );

            if (! $taxZone->default) {
                $taxZone->default = true;
                $taxZone->active = true;
                $taxZone->save();
            }
        }

        Country::query()->each(function (Country $country) use ($taxZone): void {
            $taxZone->countries()->firstOrCreate([
                'country_id' => $country->id,
            ]);
        });
    }
}
