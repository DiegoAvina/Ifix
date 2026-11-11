<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_account_shows_a_red_card_with_a_verify_button(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('Verifica tu correo')
            ->assertSee('border-red-300', false)
            ->assertSee('action="'.route('verification.send').'"', false)
            ->assertSee('Verificar mi correo')
            ->assertDontSee('Cuenta verificada');
    }

    public function test_verify_button_sends_the_email_and_confirms_it_on_the_page(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post(route('verification.send'))
            ->assertRedirect('/profile');

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)
            ->withSession(['status' => 'verification-link-sent'])
            ->get('/profile')
            ->assertSee('Te enviamos un nuevo enlace')
            ->assertSee('Reenviar correo de verificación');
    }

    public function test_verified_account_shows_the_green_card_without_button(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('Cuenta verificada')
            ->assertDontSee('Verificar mi correo');
    }
}
