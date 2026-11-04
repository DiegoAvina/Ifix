<?php

namespace App\Console\Commands;

use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Lunar\Models\Order;
use Throwable;

/**
 * Red de seguridad del webhook: si un aviso de Mercado Pago no llega o se
 * rechaza (falta el webhook secret, túnel caído, servidor apagado), el
 * pedido no se queda sin pagar en Lunar. Busca en Mercado Pago los pagos
 * de cada pedido pendiente y los aplica con el mismo servicio que el
 * webhook, así que conserva sus validaciones e idempotencia.
 */
class ReconcileMercadoPagoPayments extends Command
{
    protected $signature = 'payments:mercadopago:reconcile';

    protected $description = 'Aplica a sus pedidos los pagos de Mercado Pago cuyo webhook no se procesó';

    // Además del plazo de pago: un OXXO/SPEI puede acreditarse un poco después.
    private const LOOKBACK_GRACE_HOURS = 24;

    public function handle(MercadoPagoService $mercadoPago, PaymentWebhookService $webhookService): int
    {
        if ((string) config('services.mercadopago.webhook_secret') === '') {
            $this->warn('MERCADOPAGO_WEBHOOK_SECRET está vacío: los webhooks se rechazan y solo esta conciliación registra los pagos.');
        }

        $since = now()->subHours((int) config('services.mercadopago.payment_hours') + self::LOOKBACK_GRACE_HOURS);
        $applied = 0;

        Order::query()
            ->whereIn('status', ['awaiting-payment', 'cancelled'])
            ->whereNull('placed_at')
            ->where('created_at', '>=', $since)
            ->chunkById(50, function ($orders) use ($mercadoPago, $webhookService, &$applied): void {
                foreach ($orders as $order) {
                    $applied += $this->reconcile($order, $mercadoPago, $webhookService);
                }
            });

        $this->info("Pagos aplicados: {$applied}");

        return self::SUCCESS;
    }

    /**
     * Un fallo en un pedido no detiene a los demás.
     */
    protected function reconcile(Order $order, MercadoPagoService $mercadoPago, PaymentWebhookService $webhookService): int
    {
        $applied = 0;

        try {
            $paymentIds = $mercadoPago->paymentIdsFor(PaymentWebhookService::buildExternalReference($order->id));
        } catch (Throwable $e) {
            Log::error('mercadopago.reconcile.search_failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return 0;
        }

        foreach ($paymentIds as $paymentId) {
            try {
                $result = $webhookService->processPayment($paymentId, [
                    'source' => 'reconcile',
                    'payment_id' => $paymentId,
                ]);
            } catch (InvalidArgumentException $e) {
                Log::warning('mercadopago.reconcile.rejected', ['order_id' => $order->id, 'payment_id' => $paymentId, 'message' => $e->getMessage()]);

                continue;
            } catch (Throwable $e) {
                Log::error('mercadopago.reconcile.failed', ['order_id' => $order->id, 'payment_id' => $paymentId, 'message' => $e->getMessage()]);

                continue;
            }

            if ($result['status'] === 'processed') {
                $applied++;
                $this->line("Pedido {$order->reference}: pago {$paymentId} — {$result['message']}");
            }
        }

        return $applied;
    }
}
