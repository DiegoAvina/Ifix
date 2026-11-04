<?php

namespace App\PaymentTypes;

use App\Services\Payment\PaymentOrderService;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentCheck;
use Lunar\Base\DataTransferObjects\PaymentChecks;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\PaymentTypes\AbstractPayment;

/**
 * Driver "mercadopago" de Lunar (Payments::driver('mercadopago')), el que
 * llevan las Transactions que registra PaymentWebhookService. Lunar lo pide
 * al mostrar un pedido en el panel (paymentChecks) y en sus acciones de
 * captura y reembolso.
 *
 * Con Checkout Pro (Preferences API) el cobro ocurre en Mercado Pago y se
 * confirma solo por webhook o conciliación, que consultan el pago en la API.
 * Este driver no cobra ni marca pedidos como pagados: solo informa.
 */
class MercadoPagoPayment extends AbstractPayment
{
    public const DRIVER = 'mercadopago';

    public function __construct(
        protected PaymentOrderService $paymentOrders,
    ) {}

    /**
     * El cliente autoriza en Mercado Pago, no aquí. Responde si el pedido
     * ya tiene un cobro confirmado, sin cambiar nada.
     */
    public function authorize(): ?PaymentAuthorize
    {
        $order = $this->order ?? $this->cart?->completedOrder()->first() ?? $this->cart?->draftOrder()->first();

        if (! $order) {
            return new PaymentAuthorize(success: false, message: 'El carrito no tiene un pedido.', paymentType: self::DRIVER);
        }

        $confirmed = $this->paymentOrders->hasSuccessfulCapture($order);

        return new PaymentAuthorize(
            success: $confirmed,
            message: $confirmed ? null : 'El pago aún no ha sido confirmado por Mercado Pago.',
            orderId: $order->id,
            paymentType: self::DRIVER,
        );
    }

    /**
     * Checkout Pro captura el cobro automáticamente al aprobarse.
     */
    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(
            success: false,
            message: 'Mercado Pago captura los pagos automáticamente; no hay nada que capturar desde la tienda.',
        );
    }

    /**
     * Sin integración de reembolsos todavía: se hacen en el panel de
     * Mercado Pago. Responder false evita que Lunar registre un reembolso
     * que no ocurrió.
     */
    public function refund(TransactionContract $transaction, int $amount, $notes = null): PaymentRefund
    {
        return new PaymentRefund(
            success: false,
            message: 'Los reembolsos de Mercado Pago se hacen desde su panel (Actividad → el pago → Devolver dinero).',
        );
    }

    public function getPaymentChecks(TransactionContract $transaction): PaymentChecks
    {
        $checks = new PaymentChecks;

        $checks->addCheck(new PaymentCheck(
            successful: (bool) $transaction->success,
            label: 'Mercado Pago',
            message: trim($transaction->status.' '.($transaction->meta['status_detail'] ?? '')),
        ));

        $checks->addCheck(new PaymentCheck(
            successful: filled($transaction->reference),
            label: 'ID de pago',
            message: (string) ($transaction->reference ?: '—'),
        ));

        return $checks;
    }
}
