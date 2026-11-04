<?php

namespace App\Mail\Orders;

use App\Services\Order\OrderViewService;
use Illuminate\Mail\Mailables\Content;
use Lunar\Admin\Filament\Resources\OrderResource;

/**
 * Aviso interno al personal de Lunar: entró un pedido pagado. Si el pago
 * llegó sin stock suficiente (payment-review), lo dice en el asunto para
 * que se decida si se surte o se reembolsa.
 */
class StaffNewOrder extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return $this->order->status === 'payment-review'
            ? "Pedido pagado {$reference} sin stock suficiente: revisar"
            : "Nuevo pedido pagado {$reference}";
    }

    protected function viewName(): string
    {
        return 'emails.orders.staff-new-order';
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->viewName(),
            with: [
                'summary' => app(OrderViewService::class)->map($this->order),
                'orderUrl' => OrderResource::getUrl('order', ['record' => $this->order], panel: 'lunar'),
                'needsReview' => $this->order->status === 'payment-review',
                'customerEmail' => $this->order->billingAddress?->contact_email
                    ?? $this->order->shippingAddress?->contact_email
                    ?? $this->order->user?->email,
                'customerPhone' => $this->order->shippingAddress?->contact_phone
                    ?? $this->order->billingAddress?->contact_phone,
            ],
        );
    }
}
