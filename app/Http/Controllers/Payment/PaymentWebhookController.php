<?php

namespace App\Http\Controllers\Payment;

use App\Exceptions\Payment\TooManyWebhookAttemptsException;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use Throwable;

/**
 * POST /payments/mercadopago/webhook — llamado por los servidores de
 * Mercado Pago, nunca por el navegador del usuario. Excluido de CSRF
 * (ver bootstrap/app.php); su autenticidad se garantiza validando
 * x-signature, no con un token de sesión.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentWebhookService $webhookService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        // PHP convierte automáticamente los puntos de los nombres de
        // query params en guiones bajos ("data.id" -> "data_id"), por
        // eso se lee así y no como $request->query('data.id').
        $dataId = $request->query('data_id') ?? $request->query('id');

        $type = $request->query('type') ?? $request->input('type');

        try {

            $result = $this->webhookService->handle(
                type: $type,
                dataId: $dataId ? (string) $dataId : null,
                xSignature: $request->header('x-signature'),
                xRequestId: $request->header('x-request-id'),
                payload: $request->all(),
            );

            return response()->json($result, 200);

        } catch (InvalidWebhookSignatureException $e) {

            Log::warning('mercadopago.webhook.invalid_signature', [
                'reason' => $e->getReason(),
                'request_id' => $e->getRequestId(),
            ]);

            // 401: la notificación no se pudo autenticar. Mercado
            // Pago no reintenta una y otra vez ante un 401 de la
            // misma forma que ante un 5xx, y no queremos darle a un
            // atacante ninguna pista sobre por qué falló.
            return response()->json(['error' => 'invalid signature'], 401);

        } catch (TooManyWebhookAttemptsException $e) {

            // Firma válida, pero este mismo pago (data_id) ya se
            // consultó demasiadas veces en la ventana en que esa
            // firma sigue siendo válida. No es un dato corrupto ni
            // una firma inválida: es replay/abuso. 429 para que
            // quede claro que es un límite de tasa, no un rechazo
            // definitivo — Mercado Pago puede reintentar más tarde
            // sin problema, la idempotencia sigue intacta.
            Log::warning('mercadopago.webhook.rate_limited', [
                'data_id' => $dataId,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'too many requests'], 429);

        } catch (InvalidArgumentException $e) {

            // Notificación auténtica pero con datos que no cuadran
            // contra ninguna Order válida (referencia inexistente,
            // monto/moneda distintos). Respondemos 200 para que
            // Mercado Pago no reintente indefinidamente algo que
            // nunca va a cuadrar, mientras queda registrado en logs
            // para revisión manual.
            Log::warning('mercadopago.webhook.rejected', [
                'message' => $e->getMessage(),
                'data_id' => $dataId,
            ]);

            return response()->json(['error' => $e->getMessage()], 200);

        } catch (Throwable $e) {

            // Falla de comunicación con Mercado Pago (red, credenciales,
            // 5xx del lado de ellos) — esto sí es transitorio, así que
            // respondemos algo distinto de 200/401 para que Mercado
            // Pago reintente la notificación más tarde.
            Log::error('mercadopago.webhook.provider_error', [
                'message' => $e->getMessage(),
                'data_id' => $dataId,
            ]);

            return response()->json(['error' => 'temporary error'], 502);

        }
    }
}
