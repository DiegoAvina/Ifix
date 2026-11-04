<?php

namespace App\Mail\Orders;

class OrderPaymentOffline extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return "Estamos confirmando el pago de tu pedido {$reference}";
    }

    protected function viewName(): string
    {
        return 'emails.orders.payment-offline';
    }
}
