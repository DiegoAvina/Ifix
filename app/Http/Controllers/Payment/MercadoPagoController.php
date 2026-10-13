<?php

namespace App\Http\Controllers\Payment;

use App\Services\Order\OrderViewService;
use App\Services\Payment\PaymentOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

/**
 * Return URLs del navegador tras Checkout Pro.
 *
 * IMPORTANTE: estas rutas nunca marcan una Order como pagada. Solo
 * muestran el estado ACTUAL de la Order en nuestra base de datos (que
 * solo el webhook puede haber actualizado). El navegador del usuario
 * no es una fuente confiable — puede cerrarse, fallar, o el query
 * string puede manipularse — así que "success"/"failure"/"pending" en
 * la URL de Mercado Pago solo se usa para elegir el tono del mensaje,
 * nunca para decidir si el pago se acredita.
 */
class MercadoPagoController extends Controller
{
    public function __construct(
        protected PaymentOrderService $paymentOrderService,
        protected OrderViewService $orderView,
    ) {}

    public function success(Request $request): View
    {
        return $this->show($request, 'success');
    }

    public function failure(Request $request): View
    {
        return $this->show($request, 'failure');
    }

    public function pending(Request $request): View
    {
        return $this->show($request, 'pending');
    }

    protected function show(Request $request, string $tone): View
    {
        $orderId = $this->extractOrderId($request->query('external_reference'));

        $order = $orderId
            ? $this->paymentOrderService->findOwned($orderId, Auth::id())
            : null;

        return view('payment.status', [
            'tone' => $tone,
            'order' => $order ? $this->orderView->map($order) : null,
        ]);
    }

    protected function extractOrderId(?string $externalReference): ?int
    {
        if (! $externalReference || ! preg_match('/^order-(\d+)$/', $externalReference, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
