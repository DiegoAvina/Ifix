<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RequireVerifiedEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_sends_the_verification_email_and_asks_to_confirm(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Ana',
            'email' => 'ana@ejemplo.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'ana@ejemplo.test')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertFalse($user->hasVerifiedEmail());

        $this->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_verification_screen_is_in_spanish(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Confirma tu correo')
            ->assertSee('Reenviar correo de verificación')
            ->assertDontSee('Thanks for signing up');
    }

    public function test_unverified_customers_cannot_buy_or_pay(): void
    {
        $this->actingAs(User::factory()->unverified()->create());

        $this->get(route('checkout.index'))->assertRedirect(route('verification.notice'));
        $this->post(route('checkout.place'))->assertRedirect(route('verification.notice'));
        $this->post(route('payments.mercadopago.pay', 1))->assertRedirect(route('verification.notice'));
    }

    public function test_unverified_customers_keep_access_to_their_account_and_orders(): void
    {
        $this->actingAs(User::factory()->unverified()->create());

        $this->get(route('orders.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_verifying_returns_the_customer_to_checkout(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('checkout.index'))->assertRedirect(route('verification.notice'));

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertRedirect(route('checkout.index'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_customers_are_not_sent_to_the_verification_screen(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }
}
