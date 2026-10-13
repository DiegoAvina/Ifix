<?php

namespace App\Services\Order;

use App\Data\Order\OrderAddressData;
use App\Data\Order\OrderData;
use App\Data\Order\OrderLineData;
use App\Support\Money;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class OrderViewService
{
    /**
     * Verifica que la Order pertenezca al usuario autenticado.
     *
     * No confiamos en el order_id de la URL por sí solo: siempre
     * se compara contra el usuario real de la sesión.
     */
    public function ensureOwnership(Order $order, int $userId): void
    {
        abort_unless($order->user_id === $userId, 404);
    }

    public function map(Order $order): OrderData
    {
        $order->loadMissing([
            'lines',
            'shippingAddress.country',
            'billingAddress.country',
        ]);

        return new OrderData(

            id: $order->id,

            reference: $order->reference,

            status: $order->status,

            statusLabel: $order->status_label,

            isPlaced: $order->isPlaced(),

            createdAt: $order->created_at,

            lines: $order->lines
                ->where('type', '!=', 'shipping')
                ->map(fn ($line) => $this->mapLine($line))
                ->values()
                ->toArray(),

            subtotal: Money::format($order->sub_total),

            tax: Money::format($order->tax_total),

            shipping: Money::format($order->shipping_total),

            total: Money::format($order->total),

            shippingAddress: $order->shippingAddress
                ? $this->mapAddress($order->shippingAddress)
                : null,

            billingAddress: $order->billingAddress
                ? $this->mapAddress($order->billingAddress)
                : null,

        );
    }

    protected function mapLine($line): OrderLineData
    {
        return new OrderLineData(
            id: $line->id,
            description: $line->description,
            identifier: $line->identifier,
            quantity: (int) $line->quantity,
            unitPrice: Money::format($line->unit_price),
            total: Money::format($line->total),
            type: $line->type,
        );
    }

    protected function mapAddress(OrderAddress $address): OrderAddressData
    {
        return new OrderAddressData(
            firstName: (string) $address->first_name,
            lastName: (string) $address->last_name,
            companyName: $address->company_name,
            lineOne: (string) $address->line_one,
            lineTwo: $address->line_two,
            city: (string) $address->city,
            state: $address->state,
            postcode: $address->postcode,
            countryName: $address->country?->name ?? '',
            contactEmail: $address->contact_email,
            contactPhone: $address->contact_phone,
        );
    }
}
