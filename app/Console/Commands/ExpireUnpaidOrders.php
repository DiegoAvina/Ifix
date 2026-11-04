<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentOrderService;
use Illuminate\Console\Command;
use Lunar\Models\Order;

/**
 * Cancela los pedidos que no se pagaron dentro del plazo. Como el stock
 * se descuenta hasta confirmarse el pago, cancelar no tiene que devolver
 * nada al inventario. Si después llega un pago aprobado (p. ej. un OXXO
 * pagado justo al límite), el webhook lo registra igual.
 */
class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Cancela los pedidos sin pagar cuyo plazo de pago ya venció';

    // Margen para avisos de Mercado Pago que llegan poco después del vencimiento.
    private const GRACE_HOURS = 1;

    public function handle(PaymentOrderService $payments): int
    {
        $cutoff = now()->subHours((int) config('services.mercadopago.payment_hours') + self::GRACE_HOURS);
        $cancelled = 0;

        Order::query()
            ->where('status', 'awaiting-payment')
            ->whereNull('placed_at')
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($orders) use ($payments, &$cancelled): void {
                foreach ($orders as $order) {
                    if ($payments->hasSuccessfulCapture($order)) {
                        continue;
                    }

                    $order->update(['status' => 'cancelled']);
                    $cancelled++;
                }
            });

        $this->info("Pedidos cancelados: {$cancelled}");

        return self::SUCCESS;
    }
}
