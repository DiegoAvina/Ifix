<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use App\Services\Cart\CartViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\CartLine;
use Lunar\Models\ProductVariant;
use InvalidArgumentException;


class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CartViewService $cartViewService,
    ) {}

    /**
     * Mostrar la página completa del carrito.
     */
    public function index(): View
    {
        return view('cart.index', [
            'cart' => $this->cartViewService->get(),
        ]);
    }

    /**
     * Obtener el carrito actual en formato JSON.
     *
     * Lo utiliza Alpine.js para actualizar
     * el contador y el mini carrito.
     */
    public function data(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'cart' => $this->cartViewService->get(),
        ]);
    }

    /**
     * Agregar una variante al carrito.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'variant_id' => [
                'required',
                'integer',
                'exists:lunar_product_variants,id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'meta' => [
                'nullable',
                'array',
            ],

        ]);

        return $this->handleCartAction(function () use ($validated) {

            $variant = ProductVariant::query()
                ->findOrFail(
                    $validated['variant_id']
                );

            $this->cartService->addProduct(

                variant: $variant,

                quantity: $validated['quantity'],

                meta: $validated['meta'] ?? [],

            );

            return 'Producto agregado al carrito.';
        });
    }

    /**
     * Actualizar la cantidad de una línea.
     */
    public function update(
        Request $request,
        CartLine $line
    ): JsonResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        return $this->handleCartAction(function () use ($validated, $line) {

            $this->ensureLineBelongsToCurrentCart($line);

            $this->cartService->updateQuantity(
                lineId: $line->id,
                quantity: $validated['quantity'],
            );

            return 'Cantidad actualizada.';
        });
    }

    /**
     * Eliminar una línea del carrito.
     */
    public function destroy(
        CartLine $line
    ): JsonResponse {
        return $this->handleCartAction(function () use ($line) {

            $this->ensureLineBelongsToCurrentCart($line);

            $this->cartService->remove($line->id);

            return 'Producto eliminado del carrito.';
        });
    }

    /**
     * Vaciar completamente el carrito.
     */
    public function clear(): JsonResponse
    {
        return $this->handleCartAction(function () {

            $this->cartService->clear();

            return 'Carrito vaciado.';
        });
    }

    /**
     * Ejecuta una acción del carrito y homogeneiza la respuesta.
     *
     * Cualquier validación esperable del carrito (stock, cantidad
     * mínima, incrementos de cantidad, etc.) debe traducirse siempre
     * en un JSON 422 con un mensaje en español — nunca en un error
     * 500. Esto cubre tanto nuestras propias validaciones
     * (InvalidArgumentException, lanzadas desde CartService) como
     * las validaciones nativas de Lunar (CartException, lanzada
     * internamente por Cart::add()/updateLine() a través de los
     * validadores configurados en config/lunar/cart.php).
     *
     * Cualquier otra excepción no se captura aquí: significa un
     * error real e inesperado, y debe seguir propagándose como 500.
     */
    protected function handleCartAction(callable $action): JsonResponse
    {
        try {

            $message = $action();

            return response()->json([

                'success' => true,

                'message' => $message,

                'cart' => $this->cartViewService->get(),

            ]);
        } catch (InvalidArgumentException|CartException $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 422);
        }
    }

    /**
     * Verifica que la línea pertenezca
     * al carrito actual.
     *
     * Evita que alguien modifique
     * una línea perteneciente a otro carrito.
     */
    protected function ensureLineBelongsToCurrentCart(
        CartLine $line
    ): void {
        $cart = $this->cartService->current();

        abort_unless(
            $cart &&
                $line->cart_id === $cart->id,
            404
        );
    }
}
