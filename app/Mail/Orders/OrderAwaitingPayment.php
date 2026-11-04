<?php

namespace App\Mail\Orders;

class OrderAwaitingPayment extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return "Tu pedido {$reference} está pendiente de pago";
    }

    protected function viewName(): string
    {
        return 'emails.orders.awaiting-payment';
    }
}
