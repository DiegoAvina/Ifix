<?php

namespace App\Services\Payment;

use App\Data\Payment\PaymentResultData;
use App\Exceptions\Payment\TooManyWebhookAttemptsException;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;

/**
 * Procesa una notificación de webhook de Mercado Pago de punta a punta:
 * verificación de firma, idempotencia, consulta del estado real,
 * validación contra la Order, y registro de Transaction + actualización
 * de la Order cuando corresponde.
 *
 * Esta clase es la ÚNICA que puede marcar placed_at en una Order a
 * partir de un pago. Nada accesible desde el navegador hace esto.
 */
class PaymentWebhookService
{
    protected const PROVIDER = 'mercadopago';

    /**
     * Prefijo usado en external_reference para poder recuperar el id
     * de la Order de forma inequívoca (ver MercadoPagoService).
     */
    protected const REFERENCE_PREFIX = 'order-';

    /**
     * Cuántas veces, como máximo, se vuelve a consultar el MISMO pago
     * (mismo data_id) en la API de Mercado Pago dentro de la ventana
     * en que su firma sigue siendo válida (ver
     * MercadoPagoService::SIGNATURE_TOLERANCE_SECONDS).
     *
     * Esto es lo que cierra el hallazgo real: solo se llega aquí
     * DESPUÉS de que la firma ya fue verificada (paso 1 de handle()),
     * así que un atacante sin el secreto real nunca lo alcanza — esto
     * no reemplaza la validación de firma, la complementa. Lo que sí
     * detiene es el escenario en que alguien capturó UNA notificación
     * real (con firma válida) y la reenvía muchas veces dentro de los
     * 300s en que esa firma sigue siendo válida: sin este límite, cada
     * reenvío dispara una llamada real a findPayment() contra la API
     * de Mercado Pago, aunque el resultado final siempre sea
     * descartado por la idempotencia de los pasos 4 y 10-11.
     *
     * 10 intentos en esa misma ventana de 300s es deliberadamente
     * generoso: dan margen de sobra para que Mercado Pago reintente
     * legítimamente la misma notificación si nuestro servidor no
     * respondió a tiempo, mientras acotan un reenvío malicioso a un
     * puñado de llamadas en vez de dejarlo ilimitado.
     */
    protected const MAX_LOOKUPS_PER_EVENT = 10;

    public function __construct(
        protected MercadoPagoService $mercadoPago,
        protected PaymentOrderService $paymentOrderService,
    ) {}

    public static function buildExternalReference(int $orderId): string
    {
        return self::REFERENCE_PREFIX.$orderId;
    }

    /**
     * @param  array<string,mixed>  $payload  Cuerpo crudo de la notificación (para auditoría).
     * @return array{status: string, message: string} Resultado para logging/depuración.
     *
     * @throws InvalidWebhookSignatureException Firma inválida — el controller debe responder 401.
     * @throws InvalidArgumentException Notificación autenticada pero con datos que no cuadran
     *                                   (Order inexistente, monto/moneda distintos, etc.) — el
     *                                   controller debe responder 422 y NO reintentar indefinidamente
     *                                   generará reintentos de Mercado Pago, lo cual es deseable
     *                                   solo para errores transitorios; en la práctica respondemos
     *                                   200 igual para evitar reintentos infinitos por datos que
     *                                   nunca van a cuadrar, y dejamos el registro para revisión manual.
     */
    public function handle(
        ?string $type,
        ?string $dataId,
        ?string $xSignature,
        ?string $xRequestId,
        array $payload
    ): array {
        // 1. Autenticidad. Si esto falla, lanza InvalidWebhookSignatureException
        // y el controller responde 401 sin procesar nada más.
        $this->mercadoPago->verifyWebhookSignature($xSignature, $xRequestId, $dataId);

        // 2. Solo procesamos notificaciones de pagos. MP también manda
        // otros "type" (ej. merchant_order) que no necesitamos aquí.
        if ($type !== 'payment' || ! $dataId) {
            return ['status' => 'ignored', 'message' => "Tipo de notificación no manejado: {$type}"];
        }

        // 2.5. Límite por evento (rate limiting real de este flujo,
        // ver MAX_LOOKUPS_PER_EVENT arriba). Se evalúa DESPUÉS de la
        // firma para no poder ser explotado por alguien sin el
        // secreto real, y ANTES de findPayment() para evitar la
        // llamada innecesaria a la API de Mercado Pago cuando se
        // dispara.
        $eventKey = 'mercadopago-webhook-event:'.$dataId;

        if (RateLimiter::tooManyAttempts($eventKey, self::MAX_LOOKUPS_PER_EVENT)) {
            throw new TooManyWebhookAttemptsException(
                "Demasiadas consultas para el pago {$dataId} en poco tiempo."
            );
        }

        RateLimiter::hit($eventKey, MercadoPagoService::SIGNATURE_TOLERANCE_SECONDS);

        // 3. Fuente de verdad: consultamos el pago real en Mercado
        // Pago. Nunca confiamos en el monto/estado que pudiera venir
        // en el payload del webhook.
        $result = $this->mercadoPago->findPayment((int) $dataId);

        // 4. Idempotencia de primer nivel: ¿ya procesamos EXACTAMENTE
        // este payment_id con este mismo status? Si sí, es un webhook
        // duplicado (reintento de MP) — no hacemos nada más.
        $alreadyProcessed = PaymentWebhookEvent::query()
            ->where('provider', self::PROVIDER)
            ->where('payment_id', $result->providerPaymentId)
            ->where('status', $result->status)
            ->exists();

        if ($alreadyProcessed) {
            return ['status' => 'duplicate', 'message' => "Webhook duplicado para el pago {$result->providerPaymentId} (status={$result->status})."];
        }

        // 5. Localizar nuestra Order mediante la referencia que
        // nosotros mismos generamos al crear la preferencia.
        $order = $this->resolveOrder($result->externalReference);

        // 6. Validar que el pago corresponde de verdad a esta Order.
        $this->validateAgainstOrder($order, $result);

        // 7-9. Todo lo que muta datos va dentro de una transacción con
        // bloqueo de fila, para que dos webhooks simultáneos para la
        // misma Order (ej. Mercado Pago reintentando en paralelo) no
        // puedan crear dos capturas ni pisarse entre sí.
        return DB::transaction(function () use ($order, $result, $payload) {

            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotencia de segundo nivel, ya con el lock tomado:
            // si entre el paso 4 y aquí otro webhook ya registró una
            // captura exitosa para esta Order, no volvemos a capturar
            // ni a tocar placed_at — solo dejamos constancia de que
            // vimos esta notificación.
            $alreadyCaptured = $this->paymentOrderService->hasSuccessfulCapture($lockedOrder);

            if ($result->isApproved() && $alreadyCaptured) {

                $this->recordWebhookEvent($result, $lockedOrder, $payload);

                return ['status' => 'already_captured', 'message' => "La Order #{$lockedOrder->id} ya tenía una captura exitosa."];
            }

            $this->recordTransaction($lockedOrder, $result);

            if ($result->isApproved()) {

                $lockedOrder->update([
                    'status' => 'payment-received',
                    'placed_at' => now(),
                ]);

            }
            // Pendiente o rechazado: la Order se queda tal cual
            // (awaiting-payment, placed_at = null). Ya quedó
            // registrada la Transaction con el estado real para
            // trazabilidad.

            $this->recordWebhookEvent($result, $lockedOrder, $payload);

            Log::info('mercadopago.webhook.processed', [
                'order_id' => $lockedOrder->id,
                'payment_id' => $result->providerPaymentId,
                'status' => $result->status,
            ]);

            return ['status' => 'processed', 'message' => "Order #{$lockedOrder->id}: {$result->status}"];
        });
    }

    protected function resolveOrder(?string $externalReference): Order
    {
        if (! $externalReference || ! str_starts_with($externalReference, self::REFERENCE_PREFIX)) {
            throw new InvalidArgumentException(
                "external_reference ausente o con formato inesperado: ".($externalReference ?? 'null')
            );
        }

        $orderId = (int) substr($externalReference, strlen(self::REFERENCE_PREFIX));

        $order = Order::query()->find($orderId);

        if (! $order) {
            throw new InvalidArgumentException(
                "No existe ninguna Order para la referencia \"{$externalReference}\"."
            );
        }

        return $order;
    }

    protected function validateAgainstOrder(Order $order, PaymentResultData $result): void
    {
        if (strtoupper($order->currency_code) !== strtoupper($result->currency)) {
            throw new InvalidArgumentException(
                "Moneda del pago ({$result->currency}) no coincide con la Order #{$order->id} ({$order->currency_code})."
            );
        }

        $orderTotal = $order->total->decimal();

        if (abs($orderTotal - $result->amount) > 0.01) {
            throw new InvalidArgumentException(
                "Monto del pago ({$result->amount}) no coincide con el total de la Order #{$order->id} ({$orderTotal})."
            );
        }
    }

    protected function recordTransaction(Order $order, PaymentResultData $result): void
    {
        $decimalPlaces = $order->currency?->decimal_places ?? 2;

        Transaction::create([
            'order_id' => $order->id,
            'success' => $result->isApproved(),
            'type' => $result->isPending() ? 'intent' : 'capture',
            'driver' => self::PROVIDER,
            'amount' => (int) round($result->amount * (10 ** $decimalPlaces)),
            'reference' => $result->providerPaymentId,
            'status' => $result->status,
            // Checkout Pro no siempre implica una tarjeta (OXXO/SPEI no
            // la tienen); lunar_transactions.card_type es NOT NULL sin
            // default, así que usamos '' cuando no aplica.
            'card_type' => '',
            'meta' => [
                'status_detail' => $result->statusDetail,
            ],
        ]);
    }

    protected function recordWebhookEvent(PaymentResultData $result, Order $order, array $payload): void
    {
        // firstOrCreate (no create): dos webhooks para la misma
        // combinación provider+payment_id+status corriendo en
        // paralelo se serializan por el lock de la Order de arriba,
        // pero por si acaso, esto evita romper con una violación del
        // índice único en vez de simplemente no duplicar el registro.
        PaymentWebhookEvent::firstOrCreate(
            [
                'provider' => self::PROVIDER,
                'payment_id' => $result->providerPaymentId,
                'status' => $result->status,
            ],
            [
                'order_id' => $order->id,
                'payload' => $payload,
                'processed_at' => now(),
            ]
        );
    }
}
