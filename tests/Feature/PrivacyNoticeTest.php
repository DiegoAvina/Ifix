<?php

namespace Tests\Feature;

use App\Filament\Resources\LegalPageResource\Pages\EditLegalPage;
use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Admin\Models\Staff;
use Livewire\Livewire;
use Tests\TestCase;

class PrivacyNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\LegalPageSeeder::class);
    }

    private function registration(array $overrides = []): array
    {
        return [
            'name' => 'Ana López',
            'email' => 'ana@ejemplo.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }

    private function staff(): Staff
    {
        return Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => 'password',
            'admin' => true,
        ]);
    }

    public function test_privacy_notice_page_shows_the_panel_content(): void
    {
        $this->get('/aviso-de-privacidad')
            ->assertOk()
            ->assertSee('Aviso de privacidad')
            ->assertSee('Derechos ARCO')
            ->assertSee('Última actualización');
    }

    public function test_panel_html_is_sanitized_before_showing_it(): void
    {
        LegalPage::query()->where('slug', LegalPage::PRIVACY)->update([
            'content' => '<p>Texto legal</p><script>alert(1)</script><a href="javascript:alert(1)" onclick="x()">enlace</a>',
        ]);

        $this->get('/aviso-de-privacidad')
            ->assertOk()
            ->assertSee('Texto legal')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('onclick', false);
    }

    public function test_unpublished_page_is_not_shown(): void
    {
        LegalPage::query()->where('slug', LegalPage::PRIVACY)->update(['published' => false]);

        $this->get('/aviso-de-privacidad')->assertNotFound();
    }

    public function test_register_form_and_footer_link_to_the_notice(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('name="privacy"', false)
            ->assertSee(route('legal.privacy'), false);

        // El footer del layout de la tienda.
        $this->get('/aviso-de-privacidad')->assertOk()->assertSee('href="'.route('legal.privacy').'"', false);
    }

    public function test_account_cannot_be_created_without_accepting_the_notice(): void
    {
        $this->post('/register', $this->registration())
            ->assertSessionHasErrors(['privacy' => 'Para crear tu cuenta debes aceptar los términos de servicio y el aviso de privacidad.']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@ejemplo.test']);
    }

    public function test_acceptance_date_is_stored_and_cannot_be_forged(): void
    {
        $this->freezeSecond();

        $this->post('/register', $this->registration([
            'privacy' => '1',
            'privacy_accepted_at' => '2000-01-01 00:00:00',
        ]))->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'ana@ejemplo.test')->sole();
        $this->assertTrue($user->privacy_accepted_at->equalTo(now()));
    }

    public function test_staff_can_edit_the_notice_from_the_lunar_panel_but_not_delete_it(): void
    {
        $this->actingAs($this->staff(), 'staff');

        $this->get('/lunar/legal-pages')->assertOk()->assertSee('Aviso de privacidad');

        $page = LegalPage::query()->where('slug', LegalPage::PRIVACY)->sole();

        Livewire::test(EditLegalPage::class, ['record' => $page->getRouteKey()])
            ->assertActionHidden('delete')
            ->fillForm(['title' => 'Aviso de privacidad integral', 'content' => '<p>Nuevo texto</p>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();
        $this->assertSame('Aviso de privacidad integral', $page->title);
        $this->assertSame(LegalPage::PRIVACY, $page->slug);
        $this->get('/aviso-de-privacidad')->assertSee('Nuevo texto');
    }
}
