<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    private function scriptPolicy(string $uri): string
    {
        $csp = $this->get($uri)->headers->get('Content-Security-Policy');

        preg_match('/script-src [^;]+/', (string) $csp, $matches);

        return $matches[0] ?? '';
    }

    public function test_storefront_only_allows_inline_scripts_with_a_nonce(): void
    {
        $policy = $this->scriptPolicy('/robots.txt');

        $this->assertStringContainsString("'nonce-", $policy);
        $this->assertStringNotContainsString("'unsafe-inline'", $policy);
    }

    public function test_pay_button_may_redirect_to_mercado_pago_but_not_elsewhere(): void
    {
        $csp = (string) $this->get('/robots.txt')->headers->get('Content-Security-Policy');

        preg_match('/form-action [^;]+/', $csp, $matches);

        $this->assertSame("form-action 'self' https://*.mercadopago.com.mx https://*.mercadopago.com", $matches[0] ?? '');
    }

    public function test_only_panel_routes_allow_blob_previews(): void
    {
        $this->assertStringNotContainsString('blob:', (string) $this->get('/robots.txt')->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('blob:', (string) $this->get('/lunar/login')->headers->get('Content-Security-Policy'));
    }

    public function test_panel_routes_allow_the_inline_scripts_filament_needs(): void
    {
        $policy = $this->scriptPolicy('/lunar/login');

        $this->assertStringContainsString("'unsafe-inline'", $policy);
        $this->assertStringNotContainsString("'nonce-", $policy);
    }

    public function test_paths_that_only_start_like_a_panel_keep_the_strict_policy(): void
    {
        $this->assertStringNotContainsString("'unsafe-inline'", $this->scriptPolicy('/lunar-ofertas'));
    }
}
