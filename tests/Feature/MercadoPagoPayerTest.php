<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentData;
use App\Services\Payment\MercadoPagoService;
use Tests\TestCase;

class MercadoPagoPayerTest extends TestCase
{
    private function payload(): array
    {
        return app(MercadoPagoService::class)->preferencePayload(new PaymentData(
            orderId: 3,
            externalReference: 'order-3',
            description: 'Pedido 00000003',
            amount: 448.0,
            currency: 'MXN',
            payerEmail: 'cliente-real@gmail.com',
            expiresAt: now()->addHours(48),
        ));
    }

    public function test_test_credentials_never_send_the_real_customer_as_payer(): void
    {
        config(['services.mercadopago.test_mode' => true]);

        $payload = $this->payload();

        $this->assertNull($payload['payer']);
        $this->assertStringNotContainsString('cliente-real@gmail.com', json_encode($payload));
        $this->assertSame('order-3', $payload['external_reference']);
    }

    public function test_production_sends_the_customer_email_as_payer(): void
    {
        config(['services.mercadopago.test_mode' => false]);

        $this->assertSame(['email' => 'cliente-real@gmail.com'], $this->payload()['payer']);
    }

    public function test_test_mode_is_on_outside_production_by_default(): void
    {
        $this->assertTrue(config('services.mercadopago.test_mode'));
    }
}
