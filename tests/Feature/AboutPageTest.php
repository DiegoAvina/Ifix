<?php

namespace Tests\Feature;

use App\Filament\Pages\AboutPageSettings;
use App\Models\AboutPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Canal, grupo de clientes y páginas legales: la página lee el catálogo real.
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_page_shows_default_content_before_being_edited(): void
    {
        $this->get('/nosotros')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Misión')
            ->assertSee('Visión')
            ->assertSee('Nuestros valores')
            ->assertSee('Honestidad')
            ->assertSee('Del lavado al acabado espejo')
            ->assertSee('Descontaminar');

        $this->get('/terminos-de-servicio')->assertOk()->assertSee('href="'.route('about').'"', false);
        $this->get('/sitemap.xml')->assertSee(route('about'), false);
    }

    public function test_content_is_escaped_and_empty_sections_are_hidden(): void
    {
        AboutPage::query()->create([
            'headline' => 'Hola <script>alert(1)</script>',
            'mission' => 'Nuestra misión <b>real</b>',
            'vision' => null,
            'values' => [['icon' => 'no-existe', 'title' => 'Respeto', 'description' => 'Siempre']],
            'stats' => [],
            'milestones' => [],
            'published' => true,
        ]);

        $this->get('/nosotros')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Nuestra misión &lt;b&gt;real&lt;/b&gt;', false)
            ->assertSee('Respeto')
            ->assertDontSee('Honestidad')
            ->assertDontSee('A dónde vamos')
            ->assertDontSee('Nuestra historia');
    }

    public function test_unpublished_page_returns_404_and_leaves_the_menu(): void
    {
        AboutPage::query()->create(['published' => false] + AboutPage::defaults());

        $this->get('/nosotros')->assertNotFound();
        $this->get('/terminos-de-servicio')->assertOk()->assertDontSee('href="'.route('about').'"', false);
        $this->get('/sitemap.xml')->assertDontSee(route('about'), false);
    }

    public function test_staff_can_add_and_remove_values_from_the_panel(): void
    {
        $this->actingAs(Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'admin' => true,
        ]), 'staff');

        $this->get('/lunar/nosotros')->assertOk()->assertSee('Agregar valor');

        Livewire::test(AboutPageSettings::class)
            ->set('data.headline', 'Somos IFIX')
            ->set('data.values', [
                'a' => ['icon' => 'eco', 'title' => 'Sostenibilidad', 'description' => 'Cuidamos el agua.'],
            ])
            ->set('data.stats', [
                'b' => ['value' => '+2,000', 'label' => 'clientes'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $page = AboutPage::query()->sole();
        $this->assertSame('Somos IFIX', $page->headline);
        $this->assertSame([['icon' => 'eco', 'title' => 'Sostenibilidad', 'description' => 'Cuidamos el agua.']], $page->values);

        $this->get('/nosotros')
            ->assertSee('Somos')
            ->assertSee('Sostenibilidad')
            ->assertSee('+2,000')
            ->assertDontSee('Honestidad');
    }
}
