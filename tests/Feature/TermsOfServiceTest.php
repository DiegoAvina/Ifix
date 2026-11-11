<?php

namespace Tests\Feature;

use App\Filament\Resources\LegalPageResource\Pages\EditLegalPage;
use App\Models\LegalPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Admin\Models\Staff;
use Livewire\Livewire;
use Tests\TestCase;

class TermsOfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\LegalPageSeeder::class);
    }

    public function test_terms_page_is_public_and_linked_from_footer_and_registration(): void
    {
        $this->get('/terminos-de-servicio')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Términos de servicio')
            ->assertSee('Ley Federal de Protección al Consumidor')
            ->assertSee('href="'.route('legal.terms').'"', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('href="'.route('legal.terms').'"', false)
            ->assertSee('href="'.route('legal.privacy').'"', false);
    }

    public function test_long_address_redirects_to_the_official_one(): void
    {
        $this->get('/legal/terminos-de-servicio')->assertRedirect(route('legal.terms'))->assertStatus(301);
        $this->get('/legal/aviso-de-privacidad')->assertRedirect(route('legal.privacy'))->assertStatus(301);
    }

    public function test_unpublished_terms_return_404(): void
    {
        LegalPage::query()->where('slug', LegalPage::TERMS)->update(['published' => false]);

        $this->get('/terminos-de-servicio')->assertNotFound();
    }

    public function test_seeder_does_not_overwrite_edited_terms(): void
    {
        LegalPage::query()->where('slug', LegalPage::TERMS)->update(['content' => '<p>Texto propio</p>']);

        $this->seed(\Database\Seeders\LegalPageSeeder::class);

        $this->assertSame('<p>Texto propio</p>', LegalPage::query()->where('slug', LegalPage::TERMS)->value('content'));
    }

    public function test_staff_can_edit_terms_in_the_panel_but_not_delete_them_or_change_their_address(): void
    {
        $staff = Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'admin' => true,
        ]);
        $this->actingAs($staff, 'staff');

        $this->get('/lunar/legal-pages')->assertOk()->assertSee('Términos de servicio');

        $page = LegalPage::query()->where('slug', LegalPage::TERMS)->sole();

        Livewire::test(EditLegalPage::class, ['record' => $page->getRouteKey()])
            ->assertActionHidden('delete')
            ->assertFormFieldIsDisabled('slug')
            ->fillForm(['content' => '<p>Envíos en 3 a 5 días hábiles</p>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(LegalPage::TERMS, $page->fresh()->slug);
        $this->get('/terminos-de-servicio')->assertSee('Envíos en 3 a 5 días hábiles');
    }
}
