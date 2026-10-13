<?php

namespace App\Services\Payment;

use InvalidArgumentException;
use Lunar\Models\Order;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;

/**
 * Resuelve y valida la Order antes de dejarla entrar al flujo de pago.
 *
 * Ninguna de estas validaciones confía en nada enviado por el
 * navegador: todas comparan contra la Order y sus relaciones ya
 * guardadas en base de datos.
 */
class PaymentOrderService
{
    /**
     * Busca la Order verificando que pertenezca al usuario autenticado.
     *
     * Nunca se acepta un customer_id/user_id del frontend: la
     * pertenencia se valida contra el usuario real de la sesión.
     */
    public function findOwned(int $orderId, int $userId): Order
    {
        $order = Order::query()->find($orderId);

        abort_if(! $order, 404, 'El pedido no existe.');

        abort_unless($order->user_id === $userId, 403, 'Este pedido no te pertenece.');

        return $order;
    }

    /**
     * Valida que la Order esté en condiciones reales de recibir un pago.
     *
     * @throws InvalidArgumentException Con un mensaje en español listo
     *                                   para mostrar al usuario.
     */
    public function validateForPayment(Order $order): void
    {
        if ($order->isPlaced()) {
            throw new InvalidArgumentException(
                'Este pedido ya fue pagado.'
            );
        }

        if ($this->hasSuccessfulCapture($order)) {
            throw new InvalidArgumentException(
                'Este pedido ya tiene un pago exitoso registrado.'
            );
        }

        // OJO: no se puede hacer loadMissing(['lines.purchasable']) sobre
        // TODAS las líneas: la línea de envío tiene purchasable_type
        // = Lunar\DataTypes\ShippingOption, que no es un Eloquent Model,
        // así que el eager load del morph revienta con un
        // ArgumentCountError. Cargamos solo "lines" y accedemos a
        // ->purchasable de forma perezosa, línea por línea, únicamente
        // sobre las líneas de producto (nunca la de envío).
        $order->loadMissing(['lines']);

        $productLines = $order->lines->where('type', '!=', 'shipping');

        if ($productLines->isEmpty()) {
            throw new InvalidArgumentException(
                'El pedido no tiene productos.'
            );
        }

        if ((int) $order->total->value <= 0) {
            throw new InvalidArgumentException(
                'El pedido no tiene un total válido.'
            );
        }

        if (blank($order->currency_code)) {
            throw new InvalidArgumentException(
                'El pedido no tiene una moneda válida.'
            );
        }

        // Revalidar stock real: el tiempo entre "crear la Order" y
        // "pulsar pagar" puede ser largo (el usuario pudo dejar la
        // pestaña abierta), así que el stock pudo cambiar desde
        // entonces.
        foreach ($productLines as $line) {

            $variant = $line->purchasable;

            if (! $variant instanceof ProductVariant) {
                continue;
            }

            if (! $variant->canBeFulfilledAtQuantity($line->quantity)) {

                $name = $variant->getDescription();

                throw new InvalidArgumentException(
                    $variant->stock > 0
                        ? "Solo hay {$variant->stock} piezas disponibles de \"{$name}\"."
                        : "\"{$name}\" ya no tiene existencias."
                );
            }
        }
    }

    /**
     * ¿Ya existe una captura exitosa registrada para esta Order?
     *
     * Esta es la comprobación central para evitar doble captura:
     * se usa tanto antes de crear una preferencia nueva como dentro
     * del propio webhook, justo antes de aplicar el pago.
     */
    public function hasSuccessfulCapture(Order $order): bool
    {
        return Transaction::query()
            ->where('order_id', $order->id)
            ->where('type', 'capture')
            ->where('success', true)
            ->exists();
    }
}
