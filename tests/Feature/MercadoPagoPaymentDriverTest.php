<?php

namespace Tests\Feature;

use App\PaymentTypes\MercadoPagoPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Admin\Models\Staff;
use Lunar\Facades\Payments;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Tests\TestCase;

class MercadoPagoPaymentDriverTest extends TestCase
{
    use RefreshDatabase;

    private function paidOrder(): array
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'reference' => '00000004',
            'status' => 'payment-received',
            'placed_at' => now(),
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 54900,
        ]);

        // Igual que la que registra PaymentWebhookService::recordTransaction().
        $transaction = Transaction::create([
            'order_id' => $order->id,
            'success' => true,
            'type' => 'capture',
            'driver' => 'mercadopago',
            'amount' => 54900,
            'reference' => '186610566017',
            'status' => 'approved',
            'card_type' => '',
            'meta' => ['status_detail' => 'accredited'],
        ]);

        return [$order, $transaction];
    }

    public function test_mercadopago_driver_is_registered_with_its_lunar_payment_type(): void
    {
        $this->assertInstanceOf(MercadoPagoPayment::class, Payments::driver('mercadopago'));
        $this->assertSame('mercadopago', config('lunar.payments.types.mercadopago.driver'));
        $this->assertSame('payment-received', config('lunar.payments.types.mercadopago.authorized'));
    }

    public function test_staff_can_open_an_order_paid_with_mercado_pago(): void
    {
        [$order] = $this->paidOrder();

        $staff = Staff::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'IFIX',
            'email' => 'admin@example.com',
            'password' => 'password',
            'admin' => true,
        ]);

        $this->actingAs($staff, 'staff')
            ->get("/lunar/orders/{$order->id}")
            ->assertOk()
            ->assertSee('186610566017')
            ->assertSee('approved accredited');
    }

    public function test_payment_checks_describe_the_verified_payment(): void
    {
        [, $transaction] = $this->paidOrder();

        $checks = $transaction->paymentChecks()->getChecks();

        $this->assertTrue($checks[0]->successful);
        $this->assertSame('approved accredited', $checks[0]->message);
        $this->assertSame('186610566017', $checks[1]->message);
    }

    public function test_panel_refund_and_capture_do_nothing_instead_of_faking_a_result(): void
    {
        [$order, $transaction] = $this->paidOrder();

        $this->assertFalse($transaction->refund(1000)->success);
        $this->assertFalse($transaction->capture(1000)->success);

        $this->assertSame(1, Transaction::where('order_id', $order->id)->count());
        $this->assertSame('payment-received', $order->fresh()->status);
    }

    public function test_authorize_only_reports_whether_mercado_pago_confirmed_the_payment(): void
    {
        [$order] = $this->paidOrder();
        $unpaid = Order::factory()->create(['status' => 'awaiting-payment', 'placed_at' => null]);

        $this->assertTrue(Payments::driver('mercadopago')->order($order)->authorize()->success);

        $response = Payments::driver('mercadopago')->order($unpaid)->authorize();
        $this->assertFalse($response->success);
        $this->assertSame('awaiting-payment', $unpaid->fresh()->status);
        $this->assertNull($unpaid->fresh()->placed_at);
    }
}
