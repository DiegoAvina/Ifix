<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SignedUrlBehindProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_link_works_when_https_is_terminated_by_a_local_proxy(): void
    {
        config(['app.url' => 'https://tienda.example.com']);
        URL::forceRootUrl('https://tienda.example.com');
        URL::forceScheme('https');

        $user = User::factory()->unverified()->create();
        $link = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);

        // El proxy (cloudflared) llega desde 127.0.0.1 por HTTP y avisa que el original era HTTPS.
        $path = substr($link, strlen('https://tienda.example.com'));
        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'tienda.example.com'])
            ->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'tienda.example.com', 'X-Forwarded-Port' => '443'])
            ->get('http://tienda.example.com'.$path);

        $this->assertNotSame(403, $response->getStatusCode());
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
