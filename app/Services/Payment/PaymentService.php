<?php

namespace App\Services\Payment;

use App\Data\Payment\PaymentData;
use Lunar\Models\Order;
use MercadoPago\Resources\Preference;

/**
 * Punto de entrada de alto nivel para iniciar un pago. El controller
 * solo llama a este servicio — toda la orquestación real vive en
 * PaymentOrderService (validación) y MercadoPagoService (SDK).
 */
class PaymentService
{
    public function __construct(
        protected PaymentOrderService $orders,
        protected MercadoPagoService $mercadoPago,
    ) {}

    /**
     * Valida la Order y crea la Preference de Checkout Pro para
     * pagarla. Devuelve la Preference (init_point incluido).
     *
     * @throws \InvalidArgumentException Si la Order no está en
     *                                    condiciones de pagarse (ver
     *                                    PaymentOrderService::validateForPayment).
     */
    public function startMercadoPagoCheckout(Order $order): Preference
    {
        $this->orders->validateForPayment($order);

        $data = new PaymentData(
            orderId: $order->id,
            externalReference: PaymentWebhookService::buildExternalReference($order->id),
            description: "Pedido {$order->reference}",
            amount: $order->total->decimal(),
            currency: $order->currency_code,
            payerEmail: $order->billingAddress?->contact_email ?? $order->user?->email,
        );

        return $this->mercadoPago->createPreference($data);
    }
}
