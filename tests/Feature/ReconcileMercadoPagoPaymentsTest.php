<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentResultData;
use App\Mail\Orders\OrderPaymentReceived;
use App\Models\User;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class ReconcileMercadoPagoPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $this->variant = ProductVariant::query()->firstOrFail();
        $this->variant->update(['stock' => 10, 'backorder' => 0, 'purchasable' => 'in_stock']);

        // Escenario real: el webhook no se puede validar.
        config(['services.mercadopago.webhook_secret' => '']);
    }

    private function order(array $attributes = []): Order
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 54900,
            ...$attributes,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $this->variant->id,
            'type' => 'physical',
            'quantity' => 1,
        ]);

        return $order;
    }

    private function payment(Order $order, string $id, float $amount = 549.00, string $status = 'approved'): PaymentResultData
    {
        return new PaymentResultData(
            providerPaymentId: $id,
            status: $status,
            statusDetail: 'accredited',
            amount: $amount,
            currency: 'MXN',
            externalReference: PaymentWebhookService::buildExternalReference($order->id),
        );
    }

    public function test_paid_order_is_registered_even_if_the_webhook_never_got_through(): void
    {
        Mail::fake();
        $order = $this->order();

        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldNotReceive('verifyWebhookSignature');
            $mock->shouldReceive('paymentIdsFor')->with("order-{$order->id}")->andReturn(['186610566017']);
            $mock->shouldReceive('findPayment')->with(186610566017)->andReturn($this->payment($order, '186610566017'));
        });

        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();

        $this->assertSame('payment-received', $order->fresh()->status);
        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertSame(1, Transaction::where('order_id', $order->id)->where('type', 'capture')->where('success', true)->count());
        Mail::assertQueued(OrderPaymentReceived::class, fn ($mail) => $mail->order->is($order));
    }

    public function test_reconciling_again_or_a_late_webhook_does_not_duplicate_anything(): void
    {
        Mail::fake();
        $order = $this->order();

        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('verifyWebhookSignature');
            $mock->shouldReceive('paymentIdsFor')->andReturn(['186610566017']);
            $mock->shouldReceive('findPayment')->andReturn($this->payment($order, '186610566017'));
        });

        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();
        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();
        app(PaymentWebhookService::class)->handle('payment', '186610566017', 'firma', 'req', []);

        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertSame(1, Transaction::where('order_id', $order->id)->count());
        Mail::assertQueuedCount(1);
    }

    public function test_payment_that_does_not_match_the_order_is_rejected(): void
    {
        Mail::fake();
        $order = $this->order();

        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('paymentIdsFor')->andReturn(['999']);
            $mock->shouldReceive('findPayment')->andReturn($this->payment($order, '999', amount: 1.00));
        });

        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();

        $this->assertSame('awaiting-payment', $order->fresh()->status);
        $this->assertSame(10, $this->variant->fresh()->stock);
        $this->assertSame(0, Transaction::where('order_id', $order->id)->count());
        Mail::assertNothingQueued();
    }

    public function test_a_failing_order_does_not_stop_the_others(): void
    {
        Mail::fake();
        $broken = $this->order();
        $order = $this->order();

        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($broken, $order): void {
            $mock->shouldReceive('paymentIdsFor')->with("order-{$broken->id}")->andThrow(new RuntimeException('timeout'));
            $mock->shouldReceive('paymentIdsFor')->with("order-{$order->id}")->andReturn(['5001']);
            $mock->shouldReceive('findPayment')->andReturn($this->payment($order, '5001'));
        });

        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();

        $this->assertSame('awaiting-payment', $broken->fresh()->status);
        $this->assertSame('payment-received', $order->fresh()->status);
    }

    public function test_paid_and_old_orders_are_not_queried(): void
    {
        $this->order(['status' => 'payment-received', 'placed_at' => now()]);
        $this->order(['created_at' => now()->subDays(30)]);

        $this->mock(MercadoPagoService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('paymentIdsFor');
        });

        $this->artisan('payments:mercadopago:reconcile')->assertSuccessful();
    }

    public function test_reconciliation_runs_every_five_minutes(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'payments:mercadopago:reconcile'));

        $this->assertNotNull($event);
        $this->assertSame('*/5 * * * *', $event->expression);
    }
}
