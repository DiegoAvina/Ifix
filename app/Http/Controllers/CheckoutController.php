<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use App\Services\Cart\CartViewService;
use App\Services\Checkout\AddressService;
use App\Services\Checkout\CheckoutService;
use App\Services\Shipping\ShippingViewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Address;
use Lunar\Models\Country;
use Lunar\Models\Customer;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CartViewService $cartViewService,
        protected AddressService $addressService,
        protected ShippingViewService $shippingView,
        protected CheckoutService $checkoutService,
    ) {}

    /**
     * Mostrar la página de checkout.
     */
    public function index(): RedirectResponse|View
    {
        $customer = $this->resolveCustomer();

        $cart = $this->cartService->current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $cart->load([
            'shippingAddress',
            'billingAddress',
        ]);

        $shippingEstimate = $this->shippingView->estimateFor(
            cartTotal: $cart->subTotal?->decimal() ?? 0.0,
            itemCount: (int) $cart->lines->sum('quantity'),
        );

        return view('checkout.index', [

            'cart' => $this->cartViewService->get(),

            'addresses' => $this->addressService->forCustomer($customer),

            'countries' => Country::orderBy('name')->get(['id', 'name']),

            'shippingEstimate' => $shippingEstimate,

            'hasPostalCode' => $this->shippingView->hasPostalCode(),

            'postalCode' => $this->shippingView->currentPostalCode(),

            'hasShippingAddress' => (bool) $cart->shippingAddress,

            'hasBillingAddress' => (bool) $cart->billingAddress,

            'shippingAddressSummary' => $cart->shippingAddress,

            'billingAddressSummary' => $cart->billingAddress,

        ]);
    }

    /**
     * Crear una dirección nueva para el cliente autenticado y
     * aplicarla de inmediato como dirección de envío y facturación.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = $this->resolveCustomer();

        $validated = $request->validate(
            $this->addressService->validationRules()
        );

        $address = $this->addressService->create($customer, $validated);

        $cart = $this->requireCart();

        $this->checkoutService->useShippingAddress($cart, $address);
        $this->checkoutService->useBillingAddress($cart, $address);

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Dirección guardada y aplicada a tu pedido.');
    }

    /**
     * Usar una dirección existente como dirección de envío
     * (y, por defecto, también de facturación).
     */
    public function useShippingAddress(Request $request, Address $address): RedirectResponse
    {
        $customer = $this->resolveCustomer();

        $this->addressService->findOwned($address->id, $customer);

        $cart = $this->requireCart();

        $this->checkoutService->useShippingAddress($cart, $address);

        if (! $cart->billingAddress) {
            $this->checkoutService->useBillingAddress($cart, $address);
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Dirección de envío actualizada.');
    }

    /**
     * Usar una dirección existente distinta como dirección de
     * facturación (cuando no se quiere usar la misma de envío).
     */
    public function useBillingAddress(Request $request, Address $address): RedirectResponse
    {
        $customer = $this->resolveCustomer();

        $this->addressService->findOwned($address->id, $customer);

        $cart = $this->requireCart();

        $this->checkoutService->useBillingAddress($cart, $address);

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Dirección de facturación actualizada.');
    }

    /**
     * Confirmar el pedido: revalida el carrito contra su estado
     * actual y crea la Order.
     */
    public function place(Request $request): RedirectResponse
    {
        $this->resolveCustomer();

        $cart = $this->requireCart();

        try {

            $order = $this->checkoutService->placeOrder($cart);

        } catch (InvalidArgumentException|CartException $e) {

            return redirect()
                ->route('checkout.index')
                ->with('error', $e->getMessage());

        }

        // CheckoutService::placeOrder() ya dejó lista una sesión de
        // carrito nueva y vacía para futuras compras.

        return redirect()->route('checkout.confirmation', $order);
    }

    /**
     * Obtiene el Lunar Customer del usuario autenticado.
     */
    protected function resolveCustomer(): Customer
    {
        $customer = $this->addressService->resolveCustomer(Auth::user());

        abort_if(
            ! $customer,
            500,
            'Tu cuenta no tiene un cliente asociado. Contacta a soporte.'
        );

        return $customer;
    }

    protected function requireCart(): \Lunar\Models\Cart
    {
        $cart = $this->cartService->current();

        abort_if(
            ! $cart || $cart->lines->isEmpty(),
            409,
            'Tu carrito está vacío.'
        );

        return $cart;
    }
}
