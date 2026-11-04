<?php

namespace App\Console\Commands;

use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Lunar\Models\Transaction;
use Throwable;

/**
 * Recupera un pago cuyo webhook no se pudo procesar (p. ej. faltaba el
 * webhook secret). No crea cobros ni confía en datos escritos a mano:
 * consulta GET /v1/payments/{id} y lo aplica con el mismo servicio que
 * el webhook, con sus validaciones e idempotencia.
 */
class SyncMercadoPagoPayment extends Command
{
    protected $signature = 'payments:mercadopago:sync {paymentId : ID del pago en Mercado Pago}';

    protected $description = 'Aplica a su pedido un pago de Mercado Pago consultándolo en la API (sin webhook)';

    public function handle(MercadoPagoService $mercadoPago, PaymentWebhookService $webhookService): int
    {
        $paymentId = (string) $this->argument('paymentId');

        if (! ctype_digit($paymentId)) {
            $this->error('El ID del pago debe ser numérico.');

            return self::INVALID;
        }

        try {
            $payment = $mercadoPago->findPayment((int) $paymentId);
        } catch (Throwable $e) {
            $this->error("No se pudo consultar el pago en Mercado Pago: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->table(['Pago', 'Estado', 'Detalle', 'Importe', 'Referencia'], [[
            $payment->providerPaymentId,
            $payment->status,
            $payment->statusDetail ?? '-',
            number_format($payment->amount, 2).' '.$payment->currency,
            $payment->externalReference ?? '-',
        ]]);

        try {
            $result = $webhookService->processPayment($paymentId, [
                'source' => 'manual-sync',
                'payment_id' => $paymentId,
            ]);
        } catch (InvalidArgumentException $e) {
            // Referencia desconocida, importe o moneda distintos al pedido.
            $this->error("Pago rechazado: {$e->getMessage()}");

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error("No se pudo procesar el pago: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("{$result['status']}: {$result['message']}");

        $order = Transaction::query()
            ->where('driver', 'mercadopago')
            ->where('reference', $payment->providerPaymentId)
            ->latest('id')
            ->first()
            ?->order;

        if ($order) {
            $this->line("Pedido {$order->reference}: estado {$order->status}, placed_at ".($order->placed_at ?? '-'));
        }

        return self::SUCCESS;
    }
}
