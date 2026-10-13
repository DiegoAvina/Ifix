<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Lunar\Models\Customer;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Crear usuario Laravel
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password']
            ),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Crear Customer de Lunar
        |--------------------------------------------------------------------------
        |
        | Lunar utiliza Customer para la información comercial
        | del cliente y User para la autenticación.
        |
        */

        $nameParts = preg_split(
            '/\s+/',
            trim($validated['name']),
            2
        );


        $firstName = $nameParts[0] ?? $validated['name'];

        $lastName = $nameParts[1] ?? '';


        $customer = Customer::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Relacionar User ↔ Customer
        |--------------------------------------------------------------------------
        */

        $customer->users()->syncWithoutDetaching([
            $user->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Evento de registro
        |--------------------------------------------------------------------------
        */

        event(new Registered($user));


        /*
        |--------------------------------------------------------------------------
        | Iniciar sesión
        |--------------------------------------------------------------------------
        */

        Auth::login($user);


        /*
        |--------------------------------------------------------------------------
        | Regenerar sesión
        |--------------------------------------------------------------------------
        |
        | Ayuda a prevenir session fixation después del login.
        |
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Continuar donde estaba el usuario
        |--------------------------------------------------------------------------
        */

        return redirect()->intended(
            route('dashboard', absolute: false)
        );
    }
}