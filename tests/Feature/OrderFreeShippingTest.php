<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentResultData;
use App\Mail\Orders\OrderFreeShippingApplied;
use App\Models\User;
use App\Services\Order\FreeShippingService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Livewire\Livewire;
use Mockery\MockInterface;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class OrderFreeShippingTest extends TestCase
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

    /**
     * Pedido sin pagar: producto $300 + envío $149 = $449.
     */
    private function order(array $attributes = []): Order
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'sub_total' => 30000,
            'shipping_total' => 14900,
            'tax_total' => 0,
            'total' => 44900,
            ...$attributes,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $this->variant->id,
            'type' => 'physical',
            'quantity' => 1,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => \Lunar\DataTypes\ShippingOption::class,
            'purchasable_id' => 1,
            'type' => 'shipping',
            'description' => 'Envío estándar',
            'identifier' => 'standard',
            'unit_price' => 14900,
            'sub_total' => 14900,
            'tax_total' => 0,
            'total' => 14900,
            'quantity' => 1,
        ]);

        return $order->fresh();
    }

    private function staff(): Staff
    {
        return Staff::query()->create([
            'first_name' => 'Admin', 'last_name' => 'IFIX', 'email' => 'admin@example.com',
            'password' => bcrypt('password'), 'admin' => true,
        ]);
    }

    private function pay(Order $order, float $amount, string $paymentId): void
    {
        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order, $amount, $paymentId): void {
            $mock->shouldReceive('verifyWebhookSignature');
            $mock->shouldReceive('findPayment')->andReturn(new PaymentResultData(
                providerPaymentId: $paymentId,
                status: 'approved',
                statusDetail: null,
                amount: $amount,
                currency: 'MXN',
                externalReference: PaymentWebhookService::buildExternalReference($order->id),
            ));
        });

        app(PaymentWebhookService::class)->handle('payment', $paymentId, 'firma', 'req', []);
    }

    public function test_free_shipping_removes_the_shipping_and_emails_the_customer(): void
    {
        Mail::fake();
        $order = $this->order();

        $removed = app(FreeShippingService::class)->apply($order, $this->staff());

        $order->refresh();
        $this->assertSame(14900, $removed);
        $this->assertSame(0, $order->shipping_total->value);
        $this->assertSame(30000, $order->total->value);
        $this->assertSame(0, $order->shippingLines->first()->total->value);
        $this->assertTrue(Activity::query()->where('subject_id', $order->id)->where('event', 'comment')->exists());

        Mail::assertQueued(OrderFreeShippingApplied::class, fn ($mail) => $mail->order->is($order));

        // No se aplica dos veces.
        $this->assertFalse(app(FreeShippingService::class)->canApply($order));
    }

    public function test_free_shipping_can_be_removed_and_reapplied(): void
    {
        Mail::fake();
        $order = $this->order();
        $service = app(FreeShippingService::class);

        $service->apply($order);
        $restored = $service->remove($order->fresh());

        $order->refresh();
        $this->assertSame(14900, $restored);
        $this->assertSame(14900, $order->shipping_total->value);
        $this->assertSame(44900, $order->total->value);
        $this->assertSame(14900, $order->shippingLines->first()->total->value);
        $this->assertFalse($service->canRemove($order));
        $this->assertTrue($service->canApply($order));

        $service->apply($order);
        $this->assertSame(30000, $order->fresh()->total->value);
    }

    public function test_paid_orders_cannot_change_shipping(): void
    {
        $order = $this->order(['status' => 'payment-received', 'placed_at' => now()]);

        $this->assertFalse(app(FreeShippingService::class)->canApply($order));
        $this->expectException(InvalidArgumentException::class);

        app(FreeShippingService::class)->apply($order);
    }

    public function test_staff_applies_and_removes_it_from_the_order_page(): void
    {
        Mail::fake();
        $order = $this->order();
        $this->actingAs($this->staff(), 'staff');

        Livewire::test(ManageOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionVisible('free_shipping')
            ->assertActionHidden('remove_free_shipping')
            ->callAction('free_shipping');

        $this->assertSame(30000, $order->fresh()->total->value);

        Livewire::test(ManageOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionHidden('free_shipping')
            ->assertActionVisible('remove_free_shipping')
            ->callAction('remove_free_shipping');

        $this->assertSame(44900, $order->fresh()->total->value);
    }

    public function test_paying_with_an_old_page_that_included_shipping_restores_it_without_refund(): void
    {
        Mail::fake();
        $order = $this->order();
        app(FreeShippingService::class)->apply($order);

        // Pagó $449 con la página abierta antes del envío gratis.
        $this->pay($order, 449.00, '7001');

        $order->refresh();
        $this->assertSame('payment-received', $order->status);
        $this->assertSame(44900, $order->total->value);
        $this->assertSame(14900, $order->shipping_total->value);
        $this->assertTrue(Transaction::query()->where('order_id', $order->id)->where('success', true)->exists());
    }

    public function test_paying_without_shipping_after_it_was_removed_reapplies_free_shipping(): void
    {
        Mail::fake();
        $order = $this->order();
        $service = app(FreeShippingService::class);
        $service->apply($order);
        $service->remove($order->fresh());

        // Pagó $300 con la página abierta mientras tenía envío gratis.
        $this->pay($order, 300.00, '7002');

        $order->refresh();
        $this->assertSame('payment-received', $order->status);
        $this->assertSame(30000, $order->total->value);
        $this->assertSame(0, $order->shipping_total->value);
    }

    public function test_free_shipping_email_shows_products_and_new_total(): void
    {
        $order = $this->order();
        app(FreeShippingService::class)->apply($order, notifyCustomer: false);

        $html = (new OrderFreeShippingApplied($order->fresh()))->render();

        $this->assertStringContainsString('envío gratis', $html);
        $this->assertStringContainsString('Pagar mi pedido', $html);
        $this->assertStringContainsString('300.00', $html);
    }
}
