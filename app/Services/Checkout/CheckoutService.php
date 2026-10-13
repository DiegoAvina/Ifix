<?php

namespace App\Services\Checkout;

use App\Services\Shipping\StandardShippingModifier;
use InvalidArgumentException;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Order;

/**
 * Orquesta el paso de "carrito listo" a "Order creada".
 *
 * Reutiliza la API real de Lunar (Cart::setShippingAddress(),
 * Cart::setBillingAddress(), Cart::setShippingOption(),
 * Cart::validateStock(), Cart::createOrder()) en vez de
 * reimplementar sus reglas.
 */
class CheckoutService
{
    /**
     * Aplica una dirección guardada del cliente como dirección de
     * envío del carrito, y de paso activa nuestra única opción de
     * envío (no hay integración de paquetería, es una tarifa fija).
     */
    public function useShippingAddress(Cart $cart, Address $address): void
    {
        $cart->setShippingAddress($address);

        $option = ShippingManifest::getOption(
            $cart,
            StandardShippingModifier::IDENTIFIER
        );

        if ($option) {
            $cart->setShippingOption($option);
        }
    }

    /**
     * Aplica una dirección guardada del cliente como dirección de
     * facturación del carrito.
     */
    public function useBillingAddress(Cart $cart, Address $address): void
    {
        $cart->setBillingAddress($address);
    }

    /**
     * Valida el carrito contra su estado ACTUAL (no el que tenía
     * cuando se cargó la página de checkout) y crea la Order.
     *
     * @throws InvalidArgumentException Carrito vacío o stock insuficiente
     *                                   (mensaje siempre en español).
     * @throws CartException Validación nativa de Lunar para todo lo
     *                        demás (dirección de envío/facturación,
     *                        opción de envío faltante, etc. — ya
     *                        viene en español vía lunar::exceptions).
     */
    public function placeOrder(Cart $cart): Order
    {
        $cart = $cart->fresh(['lines.purchasable']);

        if (! $cart || $cart->lines->isEmpty()) {
            throw new InvalidArgumentException(
                'Tu carrito está vacío.'
            );
        }

        // Si el contenido cambió (otra pestaña, stock agotado, etc.)
        // entre cargar el checkout y confirmar, esto lo detecta antes
        // de crear la Order.
        //
        // No usamos Cart::validateStock() aquí: internamente usa el
        // validador CartLineStock de Lunar, cuyo mensaje de error está
        // hardcodeado en inglés ("Item is not available at this
        // quantity.") y no pasa por lunar::exceptions. Repetimos la
        // misma comprobación (ProductVariant::canBeFulfilledAtQuantity,
        // la misma API que usa ese validador) pero con un mensaje en
        // español, consistente con el resto del carrito.
        foreach ($cart->lines as $line) {

            $variant = $line->purchasable;

            if ($variant && ! $variant->canBeFulfilledAtQuantity($line->quantity)) {

                $name = $variant->getDescription();

                throw new InvalidArgumentException(
                    $variant->stock > 0
                        ? "Solo hay {$variant->stock} piezas disponibles de \"{$name}\"."
                        : "\"{$name}\" ya no tiene existencias."
                );
            }
        }

        $order = $cart->createOrder();

        $this->startFreshCart($cart);

        return $order;
    }

    /**
     * Deja lista una sesión de carrito nueva y vacía para futuras
     * compras, y retira el carrito que ya quedó ligado a la Order
     * para que Lunar no lo vuelva a ofrecer como carrito activo.
     *
     * Dos problemas distintos a resolver aquí:
     *
     * 1. CartSession::forget() por sí solo no basta: solo olvida el
     *    cart_id de la sesión actual. Creamos un carrito nuevo
     *    explícito y lo apuntamos en la sesión (CartSession::use())
     *    para que la siguiente petición lo use de inmediato.
     *
     * 2. Eso no alcanza para sobrevivir un logout/login posterior:
     *    Cart::scopeActive() solo excluye carritos con una Order ya
     *    "placed" (con pago), no carritos en borrador como los que
     *    genera esta fase (sin pasarela de pago todavía). Si el
     *    usuario cierra sesión, CartSessionAuthListener::login() cae
     *    en su fallback $user->carts()->active()->first() al volver
     *    a entrar — y sin este soft-delete, ese fallback podía
     *    encontrar el carrito YA USADO (con menor id) en vez del
     *    nuevo, resucitando el pedido anterior. Al marcarlo como
     *    eliminado (Cart usa SoftDeletes de forma nativa) deja de
     *    aparecer en cualquier consulta normal, incluida esa. La
     *    Order ya creada no se ve afectada: Order::cart() solo se usa
     *    para trazabilidad interna, no para mostrar la confirmación.
     */
    protected function startFreshCart(Cart $spentCart): void
    {
        $freshCart = Cart::create([
            'currency_id' => $spentCart->currency_id,
            'channel_id' => $spentCart->channel_id,
            'user_id' => $spentCart->user_id,
            'customer_id' => $spentCart->customer_id,
        ]);

        CartSession::use($freshCart);

        $spentCart->delete();
    }
}
