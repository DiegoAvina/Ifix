<?php

namespace App\Services\Payment;

use App\Mail\Orders\OrderPaymentReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;
use Throwable;

/**
 * Avisa al cliente que su pago se confirmó. No depende del proveedor:
 * cualquier pasarela debe llamarlo una sola vez, después de confirmar la
 * transacción que marcó la Order como pagada.
 *
 * Nunca lanza excepciones: el pago ya quedó registrado y un problema con
 * el correo no debe hacer que el proveedor reintente la notificación.
 */
class PaymentConfirmationMailer
{
    public function send(Order $order): void
    {
        try {
            $recipient = $order->billingAddress?->contact_email
                ?? $order->shippingAddress?->contact_email
                ?? $order->user?->email;

            if (! $recipient) {
                Log::warning('payment.confirmation_email.no_recipient', ['order_id' => $order->id]);

                return;
            }

            Mail::to($recipient)->queue(new OrderPaymentReceived($order));
        } catch (Throwable $exception) {
            Log::error('payment.confirmation_email.failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
