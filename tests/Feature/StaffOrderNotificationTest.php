<?php

namespace Tests\Feature;

use App\Data\Payment\PaymentResultData;
use App\Mail\Orders\OrderPaymentReceived;
use App\Mail\Orders\StaffNewOrder;
use App\Models\User;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StaffOrderNotificationTest extends TestCase
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
    }

    private function staff(string $email, bool $admin = false, bool $canManageOrders = false): Staff
    {
        $staff = Staff::query()->create([
            'first_name' => 'Equipo',
            'last_name' => 'IFIX',
            'email' => $email,
            'password' => 'password',
            'admin' => $admin,
        ]);

        if ($canManageOrders) {
            $staff->givePermissionTo(Permission::findOrCreate('sales:manage-orders', 'staff'));
        }

        return $staff;
    }

    private function order(int $quantity = 1): Order
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create(['email' => 'cliente@ejemplo.test'])->id,
            'reference' => '00000006',
            'status' => 'awaiting-payment',
            'placed_at' => null,
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 39800,
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

    private function approve(Order $order): array
    {
        $this->mock(MercadoPagoService::class, function (MockInterface $mock) use ($order): void {
            $mock->shouldReceive('verifyWebhookSignature');
            $mock->shouldReceive('findPayment')->andReturn(new PaymentResultData(
                providerPaymentId: '186611500273',
                status: 'approved',
                statusDetail: 'accredited',
                amount: 398.00,
                currency: 'MXN',
                externalReference: PaymentWebhookService::buildExternalReference($order->id),
            ));
        });

        return app(PaymentWebhookService::class)->handle('payment', '186611500273', 'firma', 'req', []);
    }

    public function test_paid_order_notifies_staff_who_can_manage_orders_and_the_customer(): void
    {
        Mail::fake();
        $this->staff('admin@ifix.test', admin: true);
        $this->staff('ventas@ifix.test', canManageOrders: true);
        $this->staff('catalogo@ifix.test');
        $order = $this->order();

        $this->approve($order);

        Mail::assertQueued(StaffNewOrder::class, fn ($mail) => $mail->hasTo('admin@ifix.test') && $mail->order->is($order));
        Mail::assertQueued(StaffNewOrder::class, fn ($mail) => $mail->hasTo('ventas@ifix.test'));
        Mail::assertNotQueued(StaffNewOrder::class, fn ($mail) => $mail->hasTo('catalogo@ifix.test'));
        Mail::assertQueued(StaffNewOrder::class, 2);
        Mail::assertQueued(OrderPaymentReceived::class, 1);
    }

    public function test_repeated_notifications_do_not_email_staff_twice(): void
    {
        Mail::fake();
        $this->staff('admin@ifix.test', admin: true);
        $order = $this->order();

        $this->approve($order);
        $this->approve($order);

        Mail::assertQueued(StaffNewOrder::class, 1);
    }

    public function test_paid_order_without_stock_still_alerts_staff_for_review(): void
    {
        Mail::fake();
        $this->staff('admin@ifix.test', admin: true);
        $order = $this->order(quantity: 50);

        $this->approve($order);

        $this->assertSame('payment-review', $order->fresh()->status);
        Mail::assertQueued(StaffNewOrder::class, fn ($mail) => $mail->envelope()->subject === 'Pedido pagado 00000006 sin stock suficiente: revisar');
        Mail::assertNotQueued(OrderPaymentReceived::class);
    }

    public function test_staff_email_links_to_the_order_in_lunar_and_shows_the_customer(): void
    {
        $order = $this->order();
        $order->update(['status' => 'payment-received']);

        $html = (new StaffNewOrder($order->fresh()))->render();

        $this->assertStringContainsString("/lunar/orders/{$order->id}", $html);
        $this->assertStringContainsString('cliente@ejemplo.test', $html);
        $this->assertStringContainsString('Nuevo pedido pagado', $html);
    }
}
