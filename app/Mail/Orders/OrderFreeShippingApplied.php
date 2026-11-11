<?php

namespace App\Mail\Orders;

/**
 * Aviso al cliente: la tienda le aplicó envío gratis a su pedido pendiente
 * de pago. Incluye sus productos, el total nuevo y el botón para pagar.
 */
class OrderFreeShippingApplied extends OrderStatusMail
{
    protected function subjectLine(string $reference): string
    {
        return "¡Tu pedido {$reference} ahora tiene envío gratis!";
    }

    protected function viewName(): string
    {
        return 'emails.orders.free-shipping-applied';
    }
}
