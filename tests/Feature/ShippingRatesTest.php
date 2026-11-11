<?php

namespace Tests\Feature;

use App\Data\Shipping\ShippingContextData;
use App\Filament\Pages\ShippingSettingsPage;
use App\Models\Shipping\ShippingExtendedPostcode;
use App\Models\Shipping\ShippingSetting;
use App\Services\Shipping\ExtendedPostcodeImporter;
use App\Services\Shipping\ShippingEstimateService;
use App\Services\Shipping\ShippingRateCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Lunar\Admin\Models\Staff;
use Livewire\Livewire;
use Tests\TestCase;

class ShippingRatesTest extends TestCase
{
    use RefreshDatabase;

    private function quote(?string $postcode, float $total, float $kg = 1)
    {
        return app(ShippingRateCalculator::class)->quote($postcode, $total, $kg);
    }

    public function test_free_shipping_starts_exactly_at_the_threshold(): void
    {
        $service = app(ShippingEstimateService::class);

        $below = $service->estimate(new ShippingContextData(postalCode: '06600', cartTotal: 498.99, weight: 1));
        $at = $service->estimate(new ShippingContextData(postalCode: '06600', cartTotal: 499.00, weight: 1));

        $this->assertFalse($below->freeShipping);
        $this->assertSame(159.0, $below->shippingCost);
        $this->assertTrue($at->freeShipping);
        $this->assertSame(0.0, $at->shippingCost);
    }

    public function test_zone_is_chosen_by_the_first_two_digits_of_the_postcode(): void
    {
        $this->assertSame('Local (ZM Guadalajara)', $this->quote('44720', 100)->zoneName);
        $this->assertSame(79.0, $this->quote('45010', 100)->total);
        $this->assertSame('Regional', $this->quote('47000', 100)->zoneName);
        $this->assertSame('Lejana', $this->quote('97000', 100)->zoneName);
        $this->assertSame('Nacional', $this->quote('06600', 100)->zoneName);

        // Sin C.P. (o inválido) se usa la zona general.
        $this->assertSame('Nacional', $this->quote(null, 100)->zoneName);
        $this->assertFalse($this->quote('123', 100)->postcodeKnown);
    }

    public function test_heavier_orders_pay_the_weight_surcharge(): void
    {
        $this->assertSame(159.0, $this->quote('06600', 100, 3)->total);
        $this->assertSame(199.0, $this->quote('06600', 100, 4.5)->total);   // hasta 5 kg: +40
        $this->assertSame(249.0, $this->quote('06600', 100, 10)->total);    // hasta 10 kg: +90
        $this->assertSame(279.0, $this->quote('06600', 100, 11.2)->total);  // +90 + 2 kg × 15
    }

    public function test_extended_zone_adds_surcharge_and_days_and_free_shipping_may_not_cover_it(): void
    {
        ShippingExtendedPostcode::query()->create(['postcode' => '06600']);

        $paid = $this->quote('06600', 100);
        $this->assertTrue($paid->extendedZone);
        $this->assertSame(159.0 + 199.0, $paid->total);
        $this->assertSame(3 + 3, $paid->minDays);

        // Con envío gratis, por defecto solo se cobra el recargo.
        $free = $this->quote('06600', 600);
        $this->assertSame(199.0, $free->total);
        $this->assertFalse($free->freeShipping);

        ShippingSetting::query()->first()->update(['free_shipping_covers_extended' => true]);

        $covered = app(ShippingRateCalculator::class)->quote('06600', 600, 1);
        $this->assertSame(0.0, $covered->total);
        $this->assertTrue($covered->freeShipping);
    }

    public function test_settings_are_read_back_from_a_serializing_cache(): void
    {
        // Como en producción: la caché serializa y no reconstruye objetos.
        config(['cache.stores.array.serialize' => true]);
        Cache::forgetDriver('array');
        Cache::forget('shipping.settings');

        ShippingSetting::current();
        $cached = ShippingSetting::current();

        $this->assertInstanceOf(ShippingSetting::class, $cached);
        $this->assertSame('499.00', $cached->free_shipping_from);
        $this->assertSame(3, collect($cached->weight_brackets)->count());
    }

    public function test_free_shipping_can_be_disabled(): void
    {
        ShippingSetting::query()->first()->update(['free_shipping_from' => null]);

        $this->assertSame(159.0, app(ShippingRateCalculator::class)->quote('06600', 5000, 1)->total);
    }

    public function test_importer_takes_every_five_digit_postcode_once(): void
    {
        $csv = "cp,estado\n06600,CDMX\n97000;Yucatán\n 06600 \n123456\n1234\nC.P. 29000";

        $result = app(ExtendedPostcodeImporter::class)->import($csv);

        $this->assertSame(['found' => 3, 'added' => 3, 'total' => 3], $result);
        $this->assertEqualsCanonicalizing(['06600', '97000', '29000'], ShippingExtendedPostcode::query()->pluck('postcode')->all());

        $replaced = app(ExtendedPostcodeImporter::class)->import('44100', replace: true);
        $this->assertSame(1, $replaced['total']);
    }

    public function test_staff_can_manage_shipping_settings_from_the_panel(): void
    {
        $this->actingAs(Staff::query()->create([
            'first_name' => 'Admin', 'last_name' => 'IFIX', 'email' => 'admin@example.com',
            'password' => bcrypt('password'), 'admin' => true,
        ]), 'staff');

        $this->get('/lunar/shipping-zones')->assertOk()->assertSee('Local (ZM Guadalajara)');
        $this->get('/lunar/shipping-extended-postcodes')->assertOk()->assertSee('Importar lista');

        Livewire::test(ShippingSettingsPage::class)
            ->set('data.free_shipping_from', 899)
            ->set('data.extended_surcharge', 150)
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = ShippingSetting::query()->first();
        $this->assertSame('899.00', $settings->free_shipping_from);
        $this->assertSame('150.00', $settings->extended_surcharge);

        // El cambio se refleja de inmediato (la caché se limpia al guardar).
        $this->assertTrue(app(ShippingRateCalculator::class)->quote('06600', 899, 1)->freeShipping);
    }
}
