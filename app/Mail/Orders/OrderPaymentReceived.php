<?php

namespace App\Mail\Orders;

class OrderPaymentReceived extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return "Recibimos el pago de tu pedido {$reference}";
    }

    protected function viewName(): string
    {
        return 'emails.orders.payment-received';
    }
}
