<?php

namespace App\Mail\Orders;

use App\Services\Order\OrderViewService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Lunar\Models\Order;

/**
 * Correo de un estado de pedido. Se registran en `mailers` de
 * config/lunar/orders.php; el panel de Lunar los instancia con
 * (Order, contenido adicional) y solo los envía si el admin los marca
 * al cambiar el estado.
 */
abstract class OrderStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    // Reintentos en cola si el proveedor de correo falla por un momento.
    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Order $order,
        public ?string $additionalContent = null,
    ) {
    }

    abstract protected function subjectLine(string $reference): string;

    abstract protected function viewName(): string;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine($this->order->reference ?: '#'.$this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->viewName(),
            with: [
                'summary' => app(OrderViewService::class)->map($this->order),
                'orderUrl' => route('checkout.confirmation', $this->order->id),
            ],
        );
    }
}
