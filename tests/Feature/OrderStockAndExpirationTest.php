<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentData;
use App\Data\Payment\PaymentResultData;
use App\Mail\Orders\OrderPaymentReceived;
use App\Models\User;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Mockery\MockInterface;
use MercadoPago\Resources\Preference;
use Tests\TestCase;

class OrderStockAndExpirationTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $this->variant = ProductVariant::query()->firstOrFail();
        $this->variant->update(['stock' => 5, 'backorder' => 0, 'purchasable' => 'in_stock']);
    }

    private function order(int $quantity = 2, array $attributes = []): Order
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 39800,
            ...$attributes,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $this->variant->id,
            'type' => 'physical',
            'quantity' => $quantity,
        ]);

        return $order;
    }

    private function approve(Order $order, string $paymentId = '5001'): array
    {
        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order, $paymentId): void {
            $mock->shouldReceive('verifyWebhookSignature');
            $mock->shouldReceive('findPayment')->andReturn(new PaymentResultData(
                providerPaymentId: $paymentId,
                status: 'approved',
                statusDetail: null,
                amount: 398.00,
                currency: 'MXN',
                externalReference: PaymentWebhookService::buildExternalReference($order->id),
            ));
        });

        return app(PaymentWebhookService::class)->handle('payment', $paymentId, 'firma', 'req', []);
    }

    public function test_approved_payment_takes_the_sold_units_out_of_stock(): void
    {
        Mail::fake();

        $order = $this->order(quantity: 2);

        $this->approve($order);

        $this->assertSame(3, $this->variant->fresh()->stock);
        $this->assertSame('payment-received', $order->fresh()->status);
        Mail::assertQueued(OrderPaymentReceived::class);
    }

    public function test_stock_is_only_taken_once_even_if_the_payment_is_notified_again(): void
    {
        Mail::fake();

        $order = $this->order(quantity: 2);

        $this->approve($order, '5001');
        $this->approve($order, '5001');
        $this->approve($order, '5002');

        $this->assertSame(3, $this->variant->fresh()->stock);
    }

    public function test_paid_order_without_stock_goes_to_review_without_touching_stock(): void
    {
        Mail::fake();

        $this->variant->update(['stock' => 1]);
        $order = $this->order(quantity: 2);

        $this->assertSame('processed', $this->approve($order)['status']);

        $order->refresh();
        $this->assertSame('payment-review', $order->status);
        $this->assertNotNull($order->placed_at);
        $this->assertSame(1, $this->variant->fresh()->stock);
        Mail::assertNothingQueued();
    }

    public function test_backorder_covers_what_stock_cannot(): void
    {
        Mail::fake();

        $this->variant->update(['stock' => 1, 'backorder' => 5, 'purchasable' => 'in_stock_or_on_backorder']);

        $this->approve($this->order(quantity: 3));

        $variant = $this->variant->fresh();
        $this->assertSame(0, $variant->stock);
        $this->assertSame(3, $variant->backorder);
    }

    public function test_unpaid_orders_are_cancelled_after_the_deadline(): void
    {
        config(['services.mercadopago.payment_hours' => 48]);

        $expired = $this->order(attributes: ['created_at' => now()->subHours(50)]);
        $recent = $this->order(attributes: ['created_at' => now()->subHours(47)]);
        $paid = $this->order(attributes: ['created_at' => now()->subHours(60), 'status' => 'payment-received', 'placed_at' => now()]);

        $this->artisan('orders:expire-unpaid')->expectsOutput('Pedidos cancelados: 1')->assertSuccessful();

        $this->assertSame('cancelled', $expired->fresh()->status);
        $this->assertSame('awaiting-payment', $recent->fresh()->status);
        $this->assertSame('payment-received', $paid->fresh()->status);
        $this->assertSame(5, $this->variant->fresh()->stock);
    }

    public function test_a_late_payment_on_a_cancelled_order_is_still_recorded(): void
    {
        Mail::fake();

        $order = $this->order(quantity: 2, attributes: ['status' => 'cancelled', 'created_at' => now()->subDays(3)]);

        $this->approve($order);

        $this->assertSame('payment-received', $order->fresh()->status);
        $this->assertSame(3, $this->variant->fresh()->stock);
    }

    public function test_expired_or_cancelled_orders_cannot_start_a_new_payment(): void
    {
        config(['services.mercadopago.payment_hours' => 48]);

        foreach ([
            $this->order(attributes: ['created_at' => now()->subHours(49)]),
            $this->order(attributes: ['status' => 'cancelled']),
        ] as $order) {
            try {
                app(PaymentService::class)->startMercadoPagoCheckout($order);
                $this->fail('Un pedido vencido no debe poder pagarse.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('venció', $exception->getMessage());
            }
        }
    }

    public function test_payment_link_expires_at_the_order_deadline(): void
    {
        config(['services.mercadopago.payment_hours' => 48]);

        $order = $this->order(attributes: ['created_at' => now()->subHours(10)->startOfSecond()]);

        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('createPreference')
                ->once()
                ->withArgs(fn (PaymentData $data) => $data->expiresAt->equalTo($order->created_at->copy()->addHours(48)))
                ->andReturn(new Preference);
        });

        app(PaymentService::class)->startMercadoPagoCheckout($order);
    }

    public function test_customer_sees_why_a_cancelled_order_cannot_be_paid(): void
    {
        $order = $this->order(attributes: ['status' => 'cancelled', 'reference' => 'IFX-VENCIDO']);

        $this->actingAs($order->user)
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee('Cancelado')
            ->assertSee('venció')
            ->assertDontSee('Pagar con Mercado Pago');
    }

    public function test_the_expiration_command_runs_every_hour(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('orders:expire-unpaid');
    }
}
