<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

class LunarStorefrontSeeder extends Seeder
{
    public function run(): void
    {
        $hasDefaultChannel = Channel::query()->where('default', true)->exists();
        $webstore = Channel::query()->firstOrCreate(
            ['handle' => 'webstore'],
            [
                'name' => 'Webstore',
                'default' => ! $hasDefaultChannel,
                'url' => config('app.url'),
            ],
        );
        if (! $hasDefaultChannel && Product::query()->doesntExist()) {
            $webstore->default = true;
            $webstore->save();
        }

        $hasDefaultCurrency = Currency::query()->where('default', true)->exists();
        $mxn = Currency::query()->firstOrCreate(
            ['code' => 'MXN'],
            [
                'name' => 'Peso mexicano',
                'exchange_rate' => 1,
                'decimal_places' => 2,
                'enabled' => true,
                'default' => ! $hasDefaultCurrency,
            ],
        );
        if (! $hasDefaultCurrency || Price::query()->doesntExist()) {
            Currency::query()->where('default', true)->update(['default' => false]);
            $mxn->update([
                'default' => true,
                'enabled' => true,
                'exchange_rate' => 1,
                'decimal_places' => 2,
            ]);
        } elseif ($mxn->default) {
            Currency::query()
                ->where('default', true)
                ->where('id', '!=', $mxn->id)
                ->update(['default' => false]);
        }

        $spanish = Language::query()->firstOrCreate(
            ['code' => 'es'],
            ['name' => 'Español', 'default' => false],
        );

        Language::query()
            ->where('default', true)
            ->where('id', '!=', $spanish->id)
            ->update(['default' => false]);

        if (! $spanish->default) {
            $spanish->default = true;
            $spanish->save();
        }

        if (! CustomerGroup::query()->where('default', true)->exists()) {
            $retail = CustomerGroup::query()->firstOrCreate(
                ['handle' => 'retail'],
                ['name' => 'Retail', 'default' => false],
            );
            $retail->default = true;
            $retail->save();
        }

        CollectionGroup::query()->firstOrCreate(
            ['handle' => 'main'],
            ['name' => 'Principal'],
        );

        if (! ProductType::query()->exists()) {
            ProductType::query()->create(['name' => 'Productos iFix']);
        }

        Brand::query()->firstOrCreate(['name' => 'IFIX']);
    }
}
