<?php

namespace App\Services\Checkout;

use App\Data\AddressData;
use App\Models\User;
use Illuminate\Support\Collection;
use Lunar\Models\Address;
use Lunar\Models\Customer;

class AddressService
{
    /**
     * Obtiene el Lunar Customer del usuario autenticado.
     *
     * Nunca crea un Customer nuevo aquí: el registro (RegisteredUserController)
     * ya se encarga de crear exactamente uno por usuario.
     */
    public function resolveCustomer(User $user): ?Customer
    {
        return $user->latestCustomer();
    }

    /**
     * Direcciones guardadas del cliente, como modelos de Lunar.
     *
     * Necesario para poder pasarlas directamente a
     * Cart::setShippingAddress()/setBillingAddress(), que aceptan
     * un Lunar\Base\Addressable.
     */
    public function modelsForCustomer(Customer $customer): Collection
    {
        return $customer->addresses()
            ->with('country')
            ->orderByDesc('billing_default')
            ->orderByDesc('shipping_default')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return AddressData[]
     */
    public function forCustomer(Customer $customer): array
    {
        return $this->modelsForCustomer($customer)
            ->map(fn (Address $address) => $this->map($address))
            ->toArray();
    }

    /**
     * Busca una dirección por id, garantizando que pertenezca al cliente.
     *
     * Nunca confiamos en un customer_id enviado desde el frontend:
     * la pertenencia se valida contra el Customer real del usuario
     * autenticado.
     */
    public function findOwned(int $addressId, Customer $customer): Address
    {
        $address = Address::query()
            ->where('customer_id', $customer->id)
            ->find($addressId);

        abort_unless($address, 403, 'Esta dirección no te pertenece.');

        return $address;
    }

    /**
     * Crea una dirección nueva para el cliente autenticado.
     */
    public function create(Customer $customer, array $validated): Address
    {
        $validated['customer_id'] = $customer->id;

        $validated['shipping_default'] = (bool) ($validated['shipping_default'] ?? false);

        $validated['billing_default'] = (bool) ($validated['billing_default'] ?? false);

        return Address::create($validated);
    }

    /**
     * Reglas de validación para crear/editar una dirección.
     *
     * country_id se valida contra la tabla real de países de Lunar.
     */
    public function validationRules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],

            'line_one' => ['required', 'string', 'max:255'],
            'line_two' => ['nullable', 'string', 'max:255'],
            'line_three' => ['nullable', 'string', 'max:255'],

            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'country_id' => ['required', 'integer', 'exists:lunar_countries,id'],

            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],

            'delivery_instructions' => ['nullable', 'string', 'max:1000'],

            'shipping_default' => ['nullable', 'boolean'],
            'billing_default' => ['nullable', 'boolean'],
        ];
    }

    protected function map(Address $address): AddressData
    {
        return new AddressData(
            id: $address->id,
            title: $address->title,
            firstName: $address->first_name,
            lastName: $address->last_name,
            companyName: $address->company_name,
            lineOne: $address->line_one,
            lineTwo: $address->line_two,
            city: $address->city,
            state: $address->state,
            postcode: $address->postcode,
            countryId: $address->country_id,
            countryName: $address->country?->name ?? '',
            contactEmail: $address->contact_email,
            contactPhone: $address->contact_phone,
            shippingDefault: (bool) $address->shipping_default,
            billingDefault: (bool) $address->billing_default,
        );
    }
}
