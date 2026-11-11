<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Admin\Models\Staff;
use Tests\TestCase;

class PanelBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_login_shows_ifix_branding_in_spanish(): void
    {
        $this->get('/lunar/login')
            ->assertOk()
            ->assertSee('lang="es"', false)
            ->assertSee('images/ifix-logo-dark.svg', false)
            ->assertDontSee('lunar-logo', false);
    }

    public function test_panel_menu_is_in_spanish_and_store_locale_is_untouched(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->actingAs(Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'admin' => true,
        ]), 'staff');

        $this->get('/lunar/orders')
            ->assertOk()
            ->assertSee('IFIX')
            ->assertDontSee('- Lunar')
            ->assertSee('Tienda en línea')
            ->assertSee('Catálogo')
            ->assertSee('Ventas');

        // La tienda conserva su propio idioma y diseño.
        $this->get('/aviso-de-privacidad')->assertOk()->assertSee('lang="es-MX"', false);
    }
}
