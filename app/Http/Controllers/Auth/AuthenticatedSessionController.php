<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Models\Customer;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Asociar carrito con usuario y customer de Lunar
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if ($user) {

            $customer = Customer::whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->first();

            $cart = CartSession::current();

            if ($cart && $customer) {

                $cart->associate(
                    user: $user,
                    policy: 'merge',
                    refresh: true
                );

                $cart->setCustomer($customer);

                $cart->save();
            }
        }

        return redirect()->intended(
            route('dashboard', absolute: false)
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}