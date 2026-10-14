<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class CheckoutCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_and_links_a_customer_for_an_existing_user_without_one(): void
    {
        $user = User::factory()->create([
            'name' => 'Legacy Customer',
        ]);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Tu carrito está vacío.');

        $this->assertDatabaseHas('lunar_customers', [
            'first_name' => 'Legacy',
            'last_name' => 'Customer',
        ]);

        $this->assertDatabaseHas('lunar_customer_user', [
            'user_id' => $user->id,
        ]);

        $this->assertSame(1, $user->customers()->count());
    }

    public function test_checkout_reuses_an_existing_customer(): void
    {
        $user = User::factory()->create();
        $customer = \Lunar\Models\Customer::create([
            'first_name' => 'Existing',
            'last_name' => 'Customer',
        ]);
        $customer->users()->syncWithoutDetaching([$user->id]);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(1, $user->customers()->count());
    }

    public function test_checkout_country_selector_contains_mexico_as_the_default(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user);

        CartSession::manager()->add(
            purchasable: ProductVariant::query()->firstOrFail(),
            quantity: 1,
        );

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('<option value="'.\Lunar\Models\Country::query()->where('iso2', 'MX')->value('id').'" selected>', false)
            ->assertSee('México');
    }
}
