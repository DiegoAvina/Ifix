<?php

namespace App\Http\Controllers;

use App\Services\Order\OrderViewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Lunar\Models\Order;

class OrderController extends Controller
{
    public function __construct(
        protected OrderViewService $orderView,
    ) {}

    /**
     * Historial de pedidos del usuario autenticado.
     */
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('reference')
            ->orderByDesc('created_at')
            ->get();

        return view('checkout.orders', [
            'orders' => $orders->map(fn (Order $order) => $this->orderView->map($order)),
        ]);
    }

    /**
     * Confirmación de un pedido puntual.
     *
     * Nunca confiamos en el {order} de la URL por sí solo: se
     * verifica que pertenezca al usuario autenticado antes de
     * mostrar nada.
     */
    public function show(Order $order): View
    {
        $this->orderView->ensureOwnership($order, Auth::id());

        return view('checkout.confirmation', [
            'order' => $this->orderView->map($order),
        ]);
    }
}
