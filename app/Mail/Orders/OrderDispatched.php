<?php

namespace App\Mail\Orders;

class OrderDispatched extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return "Tu pedido {$reference} va en camino";
    }

    protected function viewName(): string
    {
        return 'emails.orders.dispatched';
    }
}
