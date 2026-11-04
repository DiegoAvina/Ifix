<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentResultData;
use App\Mail\Orders\OrderPaymentReceived;
use App\Models\User;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class PaymentConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    private function order(?string $contactEmail = 'cliente@ejemplo.test', ?int $userId = null): Order
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $order = Order::factory()->create([
            'user_id' => $userId,
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 39800,
        ]);

        if ($contactEmail) {
            OrderAddress::factory()->create([
                'order_id' => $order->id,
                'type' => 'billing',
                'contact_email' => $contactEmail,
            ]);
        }

        return $order;
    }

    /**
     * Procesa una notificación como lo hace el controlador, con la API
     * de Mercado Pago simulada.
     */
    private function notify(Order $order, string $status, string $paymentId = '1001'): array
    {
        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order, $status, $paymentId): void {
            $mock->shouldReceive('verifyWebhookSignature');
            $mock->shouldReceive('findPayment')->andReturn(new PaymentResultData(
                providerPaymentId: $paymentId,
                status: $status,
                statusDetail: null,
                amount: 398.00,
                currency: 'MXN',
                externalReference: PaymentWebhookService::buildExternalReference($order->id),
            ));
        });

        return app(PaymentWebhookService::class)->handle('payment', $paymentId, 'firma', 'req', []);
    }

    public function test_approved_payment_queues_the_email_to_the_order_contact(): void
    {
        Mail::fake();

        $order = $this->order();

        $this->assertSame('processed', $this->notify($order, 'approved')['status']);

        Mail::assertQueued(OrderPaymentReceived::class, fn ($mail) => $mail->hasTo('cliente@ejemplo.test') && $mail->order->is($order));
        Mail::assertQueuedCount(1);
        $this->assertSame('payment-received', $order->fresh()->status);
    }

    public function test_repeated_notifications_do_not_send_the_email_twice(): void
    {
        Mail::fake();

        $order = $this->order();

        $this->notify($order, 'approved');

        $this->assertSame('duplicate', $this->notify($order, 'approved')['status']);
        $this->assertSame('already_captured', $this->notify($order, 'approved', '1002')['status']);

        Mail::assertQueuedCount(1);
    }

    public function test_unpaid_statuses_do_not_send_the_email(): void
    {
        Mail::fake();

        $order = $this->order();

        $this->notify($order, 'pending', '1001');
        $this->notify($order, 'rejected', '1002');

        Mail::assertNothingQueued();
        $this->assertNull($order->fresh()->placed_at);
    }

    public function test_email_falls_back_to_the_account_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'cuenta@ejemplo.test']);
        $order = $this->order(contactEmail: null, userId: $user->id);

        $this->notify($order, 'approved');

        Mail::assertQueued(OrderPaymentReceived::class, fn ($mail) => $mail->hasTo('cuenta@ejemplo.test'));
    }

    public function test_order_without_any_email_is_still_marked_as_paid(): void
    {
        Mail::fake();

        $order = $this->order(contactEmail: null);

        $this->assertSame('processed', $this->notify($order, 'approved')['status']);

        Mail::assertNothingQueued();
        $this->assertSame('payment-received', $order->fresh()->status);
    }

    public function test_a_mail_failure_does_not_break_the_payment(): void
    {
        $order = $this->order();

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Correo caído'));

        $this->assertSame('processed', $this->notify($order, 'approved')['status']);
        $this->assertSame('payment-received', $order->fresh()->status);
    }
}
