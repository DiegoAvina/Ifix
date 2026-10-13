<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentOrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentOrderService $paymentOrderService,
    ) {}

    /**
     * Inicia el pago de una Order con Mercado Pago (Checkout Pro) y
     * redirige al usuario al checkout hospedado.
     *
     * Ningún dato del pago (monto, moneda, customer) se acepta desde
     * la request: todo se recalcula desde la Order real.
     */
    public function payWithMercadoPago(int $orderId): RedirectResponse
    {
        $order = $this->paymentOrderService->findOwned($orderId, Auth::id());

        try {

            $preference = $this->paymentService->startMercadoPagoCheckout($order);

        } catch (InvalidArgumentException $e) {

            return redirect()
                ->route('checkout.confirmation', $order)
                ->with('error', $e->getMessage());

        } catch (Throwable $e) {

            // Falla de comunicación con Mercado Pago (credenciales,
            // red, 5xx de su lado). No es un problema de la Order en
            // sí, así que no se expone el detalle técnico al usuario.
            Log::error('mercadopago.checkout.create_preference_failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.confirmation', $order)
                ->with('error', 'No pudimos conectar con Mercado Pago en este momento. Intenta de nuevo en unos minutos.');

        }

        return redirect()->away($preference->init_point);
    }
}
