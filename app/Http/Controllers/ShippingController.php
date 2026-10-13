<?php

namespace App\Http\Controllers;

use App\Services\Shipping\PostalCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Data\Shipping\ShippingContextData;


class ShippingController extends Controller
{
    public function __construct(
        protected PostalCodeService $postalCodeService
    ) {}

    /**
     * Guarda el código postal en la sesión.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'postal_code' => [
                'required',
                'digits:5',
            ],
        ]);

        $this->postalCodeService->set(
            $validated['postal_code']
        );

        return back()->with(
            'success',
            'Código postal actualizado correctamente.'
        );
    }

    /**
     * Elimina el código postal de la sesión.
     */
    public function destroy(): RedirectResponse
    {
        $this->postalCodeService->forget();

        return back()->with(
            'success',
            'Código postal eliminado.'
        );
    }
}