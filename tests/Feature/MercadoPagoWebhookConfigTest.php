<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MercadoPagoWebhookConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_webhook_secret_asks_mercado_pago_to_retry_instead_of_dropping_the_payment(): void
    {
        config(['services.mercadopago.webhook_secret' => '']);

        $this->post('/payments/mercadopago/webhook?type=payment&data.id=123')
            ->assertStatus(502);
    }

    public function test_non_payment_notifications_are_acknowledged_so_mercado_pago_stops_retrying(): void
    {
        config(['services.mercadopago.webhook_secret' => '']);

        $this->post('/payments/mercadopago/webhook?topic=merchant_order&id=45803232989')
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->post('/payments/mercadopago/webhook?type=merchant_order&data.id=45803232989')
            ->assertOk()
            ->assertJson(['status' => 'ignored']);
    }

    public function test_unsigned_notification_is_rejected_when_the_secret_is_configured(): void
    {
        config(['services.mercadopago.webhook_secret' => 'secreto-de-prueba']);

        $this->post('/payments/mercadopago/webhook?type=payment&data.id=123')
            ->assertStatus(401);
    }
}
