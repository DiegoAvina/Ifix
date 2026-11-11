<?php

namespace Tests\Feature;

use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Models\HeroSlide;
use App\Services\Storefront\HeroService;
use App\Services\Storefront\ProductPriceService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    private function slide(array $attributes = []): HeroSlide
    {
        return HeroSlide::query()->create([
            'title' => 'Oferta de prueba',
            'image' => 'hero-slides/oferta.jpg',
            ...$attributes,
        ]);
    }

    private function variantWithPrice(int $price, ?int $comparePrice): object
    {
        $currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

        $model = new Price([
            'currency_id' => $currency->id,
            'price' => $price,
            'compare_price' => $comparePrice,
        ]);
        $model->setRelation('currency', $currency);

        return (object) ['prices' => collect([$model])];
    }

    public function test_only_active_slides_within_their_dates_are_shown_in_order(): void
    {
        $this->slide(['title' => 'Segunda', 'sort_order' => 2]);
        $this->slide(['title' => 'Primera', 'sort_order' => 1]);
        $this->slide(['title' => 'Vigente', 'sort_order' => 3, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $this->slide(['title' => 'Inactiva', 'active' => false]);
        $this->slide(['title' => 'Futura', 'starts_at' => now()->addDay()]);
        $this->slide(['title' => 'Vencida', 'ends_at' => now()->subMinute()]);

        $this->assertSame(
            ['Primera', 'Segunda', 'Vigente'],
            array_column(app(HeroService::class)->all(), 'title'),
        );
    }

    public function test_slide_defaults_use_offer_label_public_storage_and_catalog_link(): void
    {
        $this->slide();

        $slide = app(HeroService::class)->all()[0];

        $this->assertSame('Oferta', $slide['eyebrow']);
        $this->assertStringEndsWith('/storage/hero-slides/oferta.jpg', $slide['image']);
        $this->assertSame(route('catalog.index'), $slide['primaryBtn']['url']);
    }

    public function test_homepage_renders_carousel_only_with_several_slides(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->slide(['title' => 'Oferta uno']);

        $this->get('/')->assertOk()->assertSee('Oferta uno')->assertDontSee('data-hero-carousel', false);

        $this->slide(['title' => 'Oferta dos']);

        $this->get('/')->assertOk()->assertSee('Oferta dos')->assertSee('data-hero-carousel', false);
    }

    public function test_homepage_falls_back_when_all_slides_expired(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->slide(['title' => 'Oferta vencida', 'ends_at' => now()->subMinute()]);

        $this->get('/')
            ->assertOk()
            ->assertSee('El detalle hace la diferencia')
            ->assertDontSee('Oferta vencida');
    }

    public function test_price_exposes_previous_price_and_discount_when_on_sale(): void
    {
        $price = app(ProductPriceService::class)->build($this->variantWithPrice(29900, 39900));

        $this->assertSame('$299.00', $price->formatted);
        $this->assertSame('$399.00', $price->compareFormatted);
        $this->assertSame(25, $price->discountPercent);
    }

    public function test_price_without_higher_previous_price_is_not_on_sale(): void
    {
        foreach ([null, 0, 29900, 19900] as $comparePrice) {
            Currency::query()->delete();

            $price = app(ProductPriceService::class)->build($this->variantWithPrice(29900, $comparePrice));

            $this->assertNull($price->compareFormatted);
            $this->assertNull($price->discountPercent);
        }
    }

    public function test_staff_can_manage_slides_from_the_lunar_panel(): void
    {
        $staff = Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => 'password',
            'admin' => true,
        ]);

        $this->actingAs($staff, 'staff')
            ->get('/lunar/hero-slides')
            ->assertOk()
            ->assertSee('Banners del inicio');
    }

    public function test_panel_form_stores_mexico_time_as_utc_and_validates_dates(): void
    {
        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('lunar'));

        $staff = Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => 'password',
            'admin' => true,
        ]);
        $this->actingAs($staff, 'staff');

        $data = [
            'title' => 'Buen Fin',
            'image' => UploadedFile::fake()->image('oferta.jpg', 2400, 1050),
            'sort_order' => 0,
            'active' => true,
            'starts_at' => '2026-11-13 10:00:00',
        ];

        Livewire::test(CreateHeroSlide::class)
            ->fillForm([...$data, 'ends_at' => '2026-11-13 09:00:00'])
            ->call('create')
            ->assertHasFormErrors(['ends_at']);

        Livewire::test(CreateHeroSlide::class)
            ->fillForm([...$data, 'ends_at' => '2026-11-16 23:59:00'])
            ->call('create')
            ->assertHasNoFormErrors();

        $slide = HeroSlide::query()->sole();

        $this->assertSame('2026-11-13 16:00:00', $slide->starts_at->format('Y-m-d H:i:s'));
        Storage::disk('public')->assertExists($slide->image);
    }
}
