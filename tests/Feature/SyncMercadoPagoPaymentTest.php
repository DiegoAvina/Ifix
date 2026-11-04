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
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncMercadoPagoPaymentTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $this->variant = ProductVariant::query()->firstOrFail();
        $this->variant->update(['stock' => 10, 'backorder' => 0, 'purchasable' => 'in_stock']);

        $this->order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 44800,
        ]);

        OrderLine::factory()->create([
            'order_id' => $this->order->id,
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $this->variant->id,
            'type' => 'physical',
            'quantity' => 1,
        ]);
    }

    /**
     * Mercado Pago simulado: el comando no debe pedir ni verificar firma,
     * solo consultar el pago.
     */
    private function mercadoPagoReturns(float $amount = 448.00, string $status = 'approved'): void
    {
        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($amount, $status): void {
            $mock->shouldNotReceive('verifyWebhookSignature');
            $mock->shouldReceive('findPayment')->with(187666198262)->andReturn(new PaymentResultData(
                providerPaymentId: '187666198262',
                status: $status,
                statusDetail: 'accredited',
                amount: $amount,
                currency: 'MXN',
                externalReference: PaymentWebhookService::buildExternalReference($this->order->id),
            ));
        });
    }

    public function test_sync_applies_an_approved_payment_through_the_webhook_service(): void
    {
        Mail::fake();
        $this->mercadoPagoReturns();

        $this->artisan('payments:mercadopago:sync', ['paymentId' => '187666198262'])->assertSuccessful();

        $this->assertSame('payment-received', $this->order->fresh()->status);
        $this->assertNotNull($this->order->fresh()->placed_at);
        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertSame(1, Transaction::where('order_id', $this->order->id)->where('reference', '187666198262')->where('type', 'capture')->where('success', true)->count());
        Mail::assertQueued(OrderPaymentReceived::class, fn ($mail) => $mail->order->is($this->order));
    }

    public function test_running_the_sync_twice_does_not_duplicate_anything(): void
    {
        Mail::fake();
        $this->mercadoPagoReturns();

        $this->artisan('payments:mercadopago:sync', ['paymentId' => '187666198262'])->assertSuccessful();
        $this->artisan('payments:mercadopago:sync', ['paymentId' => '187666198262'])->assertSuccessful();

        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertSame(1, Transaction::where('order_id', $this->order->id)->count());
        Mail::assertQueuedCount(1);
    }

    public function test_amount_mismatch_is_rejected_without_touching_the_order(): void
    {
        Mail::fake();
        $this->mercadoPagoReturns(amount: 1.00);

        $this->artisan('payments:mercadopago:sync', ['paymentId' => '187666198262'])->assertFailed();

        $this->assertSame('awaiting-payment', $this->order->fresh()->status);
        $this->assertSame(10, $this->variant->fresh()->stock);
        $this->assertSame(0, Transaction::where('order_id', $this->order->id)->count());
        Mail::assertNothingQueued();
    }

    public function test_non_numeric_payment_id_is_refused(): void
    {
        $this->artisan('payments:mercadopago:sync', ['paymentId' => 'abc'])->assertExitCode(2);
    }
}
