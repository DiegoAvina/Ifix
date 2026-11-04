<?php

namespace App\Services\Order;

use App\Mail\Orders\StaffNewOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Order;
use Throwable;

/**
 * Avisa al personal de Lunar que puede atender pedidos (administradores o
 * con el permiso sales:manage-orders) que entró un pedido pagado.
 *
 * Igual que PaymentConfirmationMailer, nunca lanza: el pago ya quedó
 * registrado y un fallo de correo no debe hacer reintentar al proveedor.
 */
class StaffOrderNotifier
{
    public function send(Order $order): void
    {
        try {
            $recipients = Staff::query()->whereNotNull('email')->get()
                ->filter(fn (Staff $staff) => $staff->admin || $staff->checkPermissionTo('sales:manage-orders', 'staff'))
                ->pluck('email')
                ->unique();

            if ($recipients->isEmpty()) {
                Log::warning('order.staff_notification.no_recipients', ['order_id' => $order->id]);

                return;
            }

            // Un correo por persona: nadie ve las direcciones de los demás.
            foreach ($recipients as $email) {
                Mail::to($email)->queue(new StaffNewOrder($order));
            }
        } catch (Throwable $exception) {
            Log::error('order.staff_notification.failed', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
