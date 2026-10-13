<?php

namespace App\Services\Cart;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\ProductVariant;

class CartService
{
    /**
     * Obtiene el carrito actual sin crear uno nuevo.
     */
    public function current(): ?Cart
    {
        return CartSession::current();
    }

    /**
     * Obtiene el carrito actual o crea uno nuevo.
     */
    public function getOrCreate(): Cart
    {
        return CartSession::manager();
    }

    /**
     * Agrega una variante al carrito.
     *
     * La validación de stock ocurre en servidor.
     */
    public function addProduct(
        ProductVariant $variant,
        int $quantity = 1,
        array $meta = []
    ): Cart {

        $this->validateQuantity($quantity);


        return DB::transaction(function () use (
            $variant,
            $quantity,
            $meta
        ) {

            $cart = $this->getOrCreate();


            /*
        |--------------------------------------------------------------------------
        | Validar disponibilidad real en servidor
        |--------------------------------------------------------------------------
        |
        | ProductVariant::canBeFulfilledAtQuantity() ya contempla las
        | tres reglas de "purchasable" de Lunar ('always',
        | 'in_stock', 'in_stock_or_on_backorder'), por lo que no
        | necesitamos reimplementarlas aquí.
        */

            $existingQuantity = (int) $cart->lines()
                ->where('purchasable_type', 'product_variant')
                ->where('purchasable_id', $variant->id)
                ->sum('quantity');


            $requestedQuantity =
                $existingQuantity + $quantity;


            if (! $variant->canBeFulfilledAtQuantity($requestedQuantity)) {

                $stock = (int) $variant->stock;

                throw new InvalidArgumentException(

                    $stock > 0

                        ? "Solo hay {$stock} piezas disponibles."

                        : "Este producto está agotado."

                );
            }


            /*
        |--------------------------------------------------------------------------
        | Agregar al carrito
        |--------------------------------------------------------------------------
        */

            return $cart->add(

                purchasable: $variant,

                quantity: $quantity,

                meta: $meta,

            );
        });
    }

    /**
     * Actualiza la cantidad de una línea.
     */
    public function updateQuantity(
    int $lineId,
    int $quantity
): Cart {

    $this->validateQuantity($quantity);

    return DB::transaction(function () use (
        $lineId,
        $quantity
    ) {

        $cart = $this->getOrCreate();

        /*
        |--------------------------------------------------------------------------
        | Buscar la línea dentro del carrito actual
        |--------------------------------------------------------------------------
        |
        | Nunca confiamos solamente en el ID enviado por el navegador.
        |
        */

        $line = $cart->lines()
            ->whereKey($lineId)
            ->with('purchasable')
            ->lockForUpdate()
            ->first();


        if (! $line) {

            throw new InvalidArgumentException(
                'La línea del carrito no es válida.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Obtener variante
        |--------------------------------------------------------------------------
        */

        $variant = $line->purchasable;


        if (! $variant instanceof ProductVariant) {

            throw new InvalidArgumentException(
                'El producto del carrito no es válido.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Bloquear variante mientras validamos inventario
        |--------------------------------------------------------------------------
        */

        $variant = ProductVariant::query()
            ->whereKey($variant->id)
            ->lockForUpdate()
            ->first();


        if (! $variant) {

            throw new InvalidArgumentException(
                'La variante del producto ya no existe.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Verificar disponibilidad real (incluye "purchasable" y stock)
        |--------------------------------------------------------------------------
        */

        $this->validateStock(
            variant: $variant,
            quantity: $quantity
        );


        /*
        |--------------------------------------------------------------------------
        | Actualizar línea
        |--------------------------------------------------------------------------
        */

        return $cart->updateLine(
            cartLineId: $line->id,
            quantity: $quantity,
        );

    });
}
    /**
     * Elimina una línea.
     */
    public function remove(int $lineId): Cart
    {
        $cart = $this->getOrCreate();


        $line = $cart->lines()
            ->whereKey($lineId)
            ->first();


        if (! $line) {

            throw new InvalidArgumentException(
                'La línea del carrito no es válida.'
            );
        }


        return $cart->remove($line->id);
    }

    /**
     * Vacía el carrito.
     */
    public function clear(): Cart
    {
        $cart = $this->getOrCreate();

        return $cart->clear();
    }

    /**
     * Cantidad total de unidades.
     */
    public function quantity(): int
    {
        $cart = $this->current();

        if (! $cart) {
            return 0;
        }

        return (int) $cart->lines->sum('quantity');
    }

    /**
     * Cantidad de líneas diferentes.
     */
    public function lineCount(): int
    {
        $cart = $this->current();

        if (! $cart) {
            return 0;
        }

        return $cart->lines->count();
    }

    /**
     * Comprueba si el carrito tiene productos.
     */
    public function hasItems(): bool
    {
        return $this->quantity() > 0;
    }

    /**
     * Obtiene el total del carrito.
     */
    public function total(): ?\Lunar\DataTypes\Price
    {
        return $this->current()?->total;
    }

    /**
     * Obtiene el subtotal.
     */
    public function subtotal(): ?\Lunar\DataTypes\Price
    {
        return $this->current()?->subTotal;
    }

    /**
     * Valida que la cantidad sea válida.
     */
    protected function validateQuantity(
        int $quantity
    ): void {

        if ($quantity < 1) {

            throw new InvalidArgumentException(
                'La cantidad debe ser mayor o igual a 1.'
            );
        }
    }

    /**
     * Valida el inventario disponible.
     *
     * Usa ProductVariant::canBeFulfilledAtQuantity(), que ya
     * contempla las tres reglas de "purchasable" de Lunar
     * ('always', 'in_stock', 'in_stock_or_on_backorder').
     */
    protected function validateStock(
        ProductVariant $variant,
        int $quantity
    ): void {

        if ($variant->canBeFulfilledAtQuantity($quantity)) {
            return;
        }


        $stock = (int) $variant->stock;


        throw new InvalidArgumentException(

            $stock > 0

                ? "Solo hay {$stock} piezas disponibles."

                : "Este producto está agotado."

        );
    }
}
