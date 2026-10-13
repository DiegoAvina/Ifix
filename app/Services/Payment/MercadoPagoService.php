<?php

namespace App\Services\Payment;

use App\Data\Payment\PaymentData;
use App\Data\Payment\PaymentResultData;
use InvalidArgumentException;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Preference;
use MercadoPago\Webhook\WebhookSignatureValidator;

/**
 * Encapsula el SDK oficial de Mercado Pago (mercadopago/dx-php v3).
 *
 * Todas las llamadas a clases del SDK viven aquí — el resto de la
 * aplicación nunca referencia directamente `MercadoPago\...`.
 */
class MercadoPagoService
{
    /**
     * Tolerancia de reloj para la validación de timestamp del webhook
     * (protección contra replay attacks con notificaciones viejas).
     *
     * Público porque PaymentWebhookService reutiliza este mismo valor
     * como ventana de su límite de consultas por evento (ver
     * MAX_LOOKUPS_PER_EVENT ahí): no tiene sentido que ese límite dure
     * más que el tiempo en que una firma capturada sigue siendo
     * válida — pasada esta ventana, un replay ya es rechazado por la
     * propia validación de firma, sin necesidad de rate limiting.
     */
    public const SIGNATURE_TOLERANCE_SECONDS = 300;

    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(
            (string) config('services.mercadopago.access_token')
        );
    }

    /**
     * Crea una Preference de Checkout Pro para la Order ya validada.
     */
    public function createPreference(PaymentData $data): Preference
    {
        $client = new PreferenceClient();

        return $client->create([
            'items' => [[
                'title' => $data->description,
                'quantity' => 1,
                'unit_price' => $data->amount,
                'currency_id' => $data->currency,
            ]],

            'payer' => $data->payerEmail ? [
                'email' => $data->payerEmail,
            ] : null,

            // Referencia inequívoca de nuestra Order — es lo que
            // usaremos para localizarla de vuelta desde el webhook.
            'external_reference' => $data->externalReference,

            'back_urls' => [
                'success' => route('payments.mercadopago.success'),
                'failure' => route('payments.mercadopago.failure'),
                'pending' => route('payments.mercadopago.pending'),
            ],

            // Redirige automáticamente de vuelta cuando el pago
            // queda aprobado, en vez de dejar al usuario en la
            // pantalla de resultado de Mercado Pago.
            'auto_return' => 'approved',

            'notification_url' => route('payments.mercadopago.webhook'),

            // No forzamos payment_methods/installments: dejamos que
            // Mercado Pago ofrezca los métodos y meses sin intereses
            // ya habilitados para la cuenta del vendedor.
        ]);
    }

    /**
     * Consulta el estado REAL de un pago directamente en la API de
     * Mercado Pago. Nunca se debe confiar en el payload del webhook
     * por sí solo — esta es la fuente de verdad.
     */
    public function findPayment(int $paymentId): PaymentResultData
    {
        $client = new PaymentClient();

        $payment = $client->get($paymentId);

        if (! $payment->status) {
            throw new InvalidArgumentException(
                "No se pudo obtener el estado del pago {$paymentId} en Mercado Pago."
            );
        }

        return new PaymentResultData(
            providerPaymentId: (string) $payment->id,
            status: $payment->status,
            statusDetail: $payment->status_detail,
            amount: (float) $payment->transaction_amount,
            currency: (string) $payment->currency_id,
            externalReference: $payment->external_reference,
        );
    }

    /**
     * Verifica la autenticidad de una notificación de webhook.
     *
     * @throws InvalidWebhookSignatureException Si la firma no es válida.
     * @throws InvalidArgumentException Si no hay webhook secret configurado.
     */
    public function verifyWebhookSignature(
        ?string $xSignature,
        ?string $xRequestId,
        ?string $dataId
    ): void {
        $secret = (string) config('services.mercadopago.webhook_secret');

        WebhookSignatureValidator::validate(
            xSignature: $xSignature,
            xRequestId: $xRequestId,
            dataId: $dataId,
            secret: $secret,
            toleranceSeconds: self::SIGNATURE_TOLERANCE_SECONDS,
        );
    }
}
