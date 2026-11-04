<?php

namespace App\Services\Order;

use Lunar\Models\Order;
use Lunar\Models\ProductVariant;

/**
 * Descuenta del inventario lo que se vendió en una Order pagada. Lunar no
 * lo hace por sí solo, y el carrito/checkout solo validan sin reservar.
 *
 * Debe llamarse dentro de la misma transacción que marca la Order como
 * pagada: bloquea las variantes para que dos pagos simultáneos no vendan
 * la misma última pieza.
 */
class OrderStockService
{
    /**
     * @return bool false si alguna línea ya no se puede surtir; en ese caso
     *              no se descuenta nada, para que el pedido se revise a mano.
     */
    public function commit(Order $order): bool
    {
        $quantities = $order->lines()
            ->where('purchasable_type', ProductVariant::morphName())
            ->get()
            ->groupBy('purchasable_id')
            ->map(fn ($lines) => (int) $lines->sum('quantity'));

        if ($quantities->isEmpty()) {
            return true;
        }

        // Orden fijo por id al bloquear para no provocar deadlocks entre
        // pedidos que comparten productos.
        $variants = ProductVariant::query()
            ->whereKey($quantities->keys())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant || ! $variant->canBeFulfilledAtQuantity($quantity)) {
                return false;
            }
        }

        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            $fromStock = min($quantity, max(0, (int) $variant->stock));
            $variant->stock = (int) $variant->stock - $fromStock;

            // Lo que no salió del stock se toma de backorder (si aplica);
            // con purchasable "always" simplemente se agota el stock.
            if ($quantity > $fromStock && $variant->purchasable === 'in_stock_or_on_backorder') {
                $variant->backorder = max(0, (int) $variant->backorder - ($quantity - $fromStock));
            }

            $variant->save();
        }

        return true;
    }
}
