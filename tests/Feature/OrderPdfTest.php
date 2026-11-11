<?php

namespace Tests\Feature;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Tests\TestCase;

class OrderPdfTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): Order
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        return Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'reference' => '00000042',
            'status' => 'payment-received',
            'placed_at' => now(),
            'currency_code' => 'MXN',
            'compare_currency_code' => 'MXN',
            'total' => 29900,
            ...$attributes,
        ]);
    }

    public function test_panel_pdf_uses_the_ifix_receipt_in_spanish(): void
    {
        $order = $this->order();
        Transaction::create([
            'order_id' => $order->id,
            'success' => true,
            'type' => 'capture',
            'driver' => 'mercadopago',
            'amount' => 29900,
            'reference' => '186613196759',
            'status' => 'approved',
            'card_type' => '',
            'meta' => [],
        ]);

        // Es la vista que pide el botón "Descargar PDF" del panel de Lunar.
        $html = view('lunarpanel::pdf.order', ['record' => $order->fresh()])->render();

        $this->assertStringContainsString('Comprobante de pedido', $html);
        $this->assertStringContainsString('#00000042', $html);
        $this->assertStringContainsString('Pago recibido', $html);
        $this->assertStringContainsString('Operación: 186613196759', $html);
        $this->assertStringContainsString('no sustituye a un comprobante fiscal (CFDI)', $html);
        $this->assertStringNotContainsString('Invoice', $html);

        $this->assertStringStartsWith('%PDF', Pdf::loadView('lunarpanel::pdf.order', ['record' => $order->fresh()])->output());
    }

    public function test_unpaid_order_without_addresses_still_renders(): void
    {
        $order = $this->order(['status' => 'awaiting-payment', 'placed_at' => null]);

        $html = view('lunarpanel::pdf.order', ['record' => $order->fresh()])->render();

        $this->assertStringContainsString('Esperando pago', $html);
        $this->assertStringContainsString('Este pedido aún no registra un pago.', $html);
    }
}
