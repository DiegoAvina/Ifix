<?php

namespace Tests\Feature;

use App\Mail\Orders\OrderAwaitingPayment;
use App\Mail\Orders\OrderDispatched;
use App\Mail\Orders\OrderPaymentOffline;
use App\Mail\Orders\OrderPaymentReceived;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class TransactionalEmailsTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DemoProductsSeeder::class);

        $order = Order::factory()->create([
            'reference' => 'IFX-1001',
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'sub_total' => 29900,
            'tax_total' => 0,
            'shipping_total' => 9900,
            'total' => 39800,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_id' => ProductVariant::query()->firstOrFail()->id,
            'description' => 'Pulimento <b>Pro</b> | 500 ml',
            'quantity' => 2,
            'total' => 29900,
        ]);

        OrderAddress::factory()->create([
            'order_id' => $order->id,
            'type' => 'shipping',
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'line_one' => 'Av. Juárez 100',
            'line_two' => null,
        ]);

        return $order;
    }

    public static function statusMails(): array
    {
        return [
            'awaiting-payment' => ['awaiting-payment', OrderAwaitingPayment::class, 'pendiente de pago'],
            'payment-offline' => ['payment-offline', OrderPaymentOffline::class, 'confirmando el pago'],
            'payment-received' => ['payment-received', OrderPaymentReceived::class, 'Recibimos el pago'],
            'dispatched' => ['dispatched', OrderDispatched::class, 'va en camino'],
        ];
    }

    /**
     * @dataProvider statusMails
     */
    public function test_each_order_status_offers_its_email_with_the_order_details(string $status, string $mailClass, string $subject): void
    {
        $this->assertSame([$mailClass], config("lunar.orders.statuses.{$status}.mailers"));

        $order = $this->order();

        // Así lo instancia el panel de Lunar (UpdatesOrderStatus).
        $mail = new $mailClass($order, null);

        $this->assertStringContainsString($subject, $mail->envelope()->subject);
        $mail->assertSeeInHtml('IFX-1001');
        $mail->assertSeeInHtml('Av. Juárez 100');
        $mail->assertSeeInHtml('$398.00 MXN');
        $mail->assertSeeInHtml(route('checkout.confirmation', $order->id), false);
        $mail->assertSeeInText('IFX-1001');
    }

    public function test_order_email_escapes_product_names_and_shows_admin_note(): void
    {
        $mail = new OrderDispatched($this->order(), 'Guía de rastreo: 12345');

        $html = $mail->render();

        $this->assertStringNotContainsString('<b>Pro</b>', $html);
        $this->assertStringContainsString('Guía de rastreo: 12345', $html);
    }

    public function test_order_email_survives_the_queue_like_the_panel_sends_it(): void
    {
        $order = $this->order();

        $mail = (new OrderDispatched($order, 'Guía 12345'))
            ->with('order', $order)
            ->with('content', 'Guía 12345');

        $html = unserialize(serialize($mail))->render();

        $this->assertStringContainsString('IFX-1001', $html);
        $this->assertStringContainsString('Guía 12345', $html);
    }

    public function test_order_email_without_note_has_no_note_panel(): void
    {
        $this->assertStringNotContainsString('class="panel"', (new OrderDispatched($this->order()))->render());
    }

    public function test_password_reset_email_is_in_spanish_and_has_the_reset_link(): void
    {
        $user = User::factory()->create();

        $message = (new ResetPassword('token-123'))->toMail($user);
        $html = (string) $message->render();

        $this->assertSame('Restablece tu contraseña de IFIX', $message->subject);
        $this->assertStringContainsString('Cambiar contraseña', $html);
        $this->assertStringContainsString('token-123', $html);
        $this->assertStringContainsString('60 minutos', $html);
        $this->assertStringNotContainsString('All rights reserved', $html);
    }

    public function test_verify_email_is_in_spanish_and_has_the_signed_link(): void
    {
        $user = User::factory()->create();

        $message = (new VerifyEmail)->toMail($user);
        $html = (string) $message->render();

        $this->assertSame('Confirma tu correo en IFIX', $message->subject);
        $this->assertStringContainsString('Confirmar correo', $html);
        $this->assertStringContainsString('signature=', $html);
    }
}
