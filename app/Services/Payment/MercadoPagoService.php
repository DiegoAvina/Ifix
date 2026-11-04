<?php

namespace App\Services\Payment;

use App\Data\Payment\PaymentData;
use App\Data\Payment\PaymentResultData;
use InvalidArgumentException;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\Resources\Preference;
use MercadoPago\Webhook\WebhookSignatureValidator;
use RuntimeException;

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

    // Formato de fechas que pide la API (ISO 8601 con milisegundos y zona).
    protected const DATE_FORMAT = 'Y-m-d\TH:i:s.vP';

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
        return (new PreferenceClient())->create($this->preferencePayload($data));
    }

    /**
     * Cuerpo que se envía a POST /checkout/preferences.
     *
     * @return array<string, mixed>
     */
    public function preferencePayload(PaymentData $data): array
    {
        $successUrl = route('payments.mercadopago.success');

        // Mercado Pago rechaza la preferencia completa si pedimos
        // auto_return o damos una notification_url con una URL local
        // (http://127.0.0.1...). En local se prueba con un túnel HTTPS.
        $isPublicUrl = str_starts_with($successUrl, 'https://');

        // Con credenciales de prueba el pagador debe ser la cuenta de prueba
        // con la que se inicia sesión en Checkout Pro; si mandamos el correo
        // real del cliente, Mercado Pago deshabilita el botón Pagar. Ahí se
        // omite y el comprador de prueba introduce sus propios datos.
        $sendPayer = $data->payerEmail && ! config('services.mercadopago.test_mode');

        return [
            'items' => [[
                'title' => $data->description,
                'quantity' => 1,
                'unit_price' => $data->amount,
                'currency_id' => $data->currency,
            ]],

            'payer' => $sendPayer ? [
                'email' => $data->payerEmail,
            ] : null,

            // Referencia inequívoca de nuestra Order — es lo que
            // usaremos para localizarla de vuelta desde el webhook.
            'external_reference' => $data->externalReference,

            'back_urls' => [
                'success' => $successUrl,
                'failure' => route('payments.mercadopago.failure'),
                'pending' => route('payments.mercadopago.pending'),
            ],

            // Redirige automáticamente de vuelta cuando el pago
            // queda aprobado, en vez de dejar al usuario en la
            // pantalla de resultado de Mercado Pago. En local el
            // cliente vuelve con el botón de Mercado Pago.
            'auto_return' => $isPublicUrl ? 'approved' : null,

            'notification_url' => $isPublicUrl ? route('payments.mercadopago.webhook') : null,

            // Mismo plazo que la Order: el link de pago deja de aceptar
            // pagos y las fichas de OXXO/SPEI vencen a la misma hora.
            'expires' => true,
            'expiration_date_from' => now()->format(self::DATE_FORMAT),
            'expiration_date_to' => $data->expiresAt->format(self::DATE_FORMAT),
            'date_of_expiration' => $data->expiresAt->format(self::DATE_FORMAT),

            // No forzamos payment_methods/installments: dejamos que
            // Mercado Pago ofrezca los métodos y meses sin intereses
            // ya habilitados para la cuenta del vendedor.
        ];
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
     * IDs de los pagos que Mercado Pago tiene para una external_reference
     * (GET /v1/payments/search). Solo IDs: el estado se vuelve a leer con
     * findPayment() al procesar cada uno.
     *
     * @return string[]
     */
    public function paymentIdsFor(string $externalReference): array
    {
        $search = (new PaymentClient())->search(new MPSearchRequest(50, 0, [
            'external_reference' => $externalReference,
            'sort' => 'date_created',
            'criteria' => 'asc',
        ]));

        return collect($search->results ?? [])
            ->map(fn ($payment) => (string) (is_array($payment) ? $payment['id'] : $payment->id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Verifica la autenticidad de una notificación de webhook.
     *
     * @throws InvalidWebhookSignatureException Si la firma no es válida.
     * @throws RuntimeException Si no hay webhook secret configurado.
     */
    public function verifyWebhookSignature(
        ?string $xSignature,
        ?string $xRequestId,
        ?string $dataId
    ): void {
        $secret = (string) config('services.mercadopago.webhook_secret');

        // Es un error de configuración nuestro, no un dato inválido: no
        // debe acabar en el 200 de "rechazado" (InvalidArgumentException),
        // porque Mercado Pago ya no reintentaría y el pago se perdería.
        if ($secret === '') {
            throw new RuntimeException('Falta MERCADOPAGO_WEBHOOK_SECRET: no se pueden verificar los avisos de pago.');
        }

        WebhookSignatureValidator::validate(
            xSignature: $xSignature,
            xRequestId: $xRequestId,
            dataId: $dataId,
            secret: $secret,
            toleranceSeconds: self::SIGNATURE_TOLERANCE_SECONDS,
        );
    }
}
