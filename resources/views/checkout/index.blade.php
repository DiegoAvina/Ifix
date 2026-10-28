@extends('layouts.app')

@section('title', 'Checkout | Ifix')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div class="checkout-page min-h-screen bg-surface-container-low">

    <div class="mx-auto max-w-7xl px-6 py-10">

        {{-- ========================================================= --}}
        {{-- MENSAJES --}}
        {{-- ========================================================= --}}

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-error/20 bg-error-container px-5 py-4 text-sm font-medium text-on-error-container">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-error/20 bg-error-container px-5 py-4 text-sm text-on-error-container">
                <p class="font-semibold">Revisa los siguientes datos:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- PASOS --}}
        {{-- ========================================================= --}}

        <div class="mb-10 overflow-hidden rounded-2xl border border-outline-variant/30 bg-white">

            <div class="grid grid-cols-4">

                <div class="border-b-4 border-outline-variant px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Paso 1</p>
                    <p class="mt-1 font-semibold text-secondary">Carrito</p>
                </div>

                <div class="border-b-4 border-primary px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-primary">Paso 2</p>
                    <p class="mt-1 font-semibold text-on-surface">Dirección y envío</p>
                </div>

                <div class="border-b-4 border-outline-variant px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Paso 3</p>
                    <p class="mt-1 font-semibold text-secondary">Confirmar pedido</p>
                </div>

                <div class="border-b-4 border-outline-variant px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-secondary">Paso 4</p>
                    <p class="mt-1 font-semibold text-secondary">Pago</p>
                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

            {{-- ===================================================== --}}
            {{-- IZQUIERDA --}}
            {{-- ===================================================== --}}

            <div class="space-y-6 lg:col-span-2">

                {{-- ================================================= --}}
                {{-- DIRECCIÓN DE ENVÍO --}}
                {{-- ================================================= --}}

                <div class="rounded-2xl border border-outline-variant/30 bg-white p-8" x-data="{ showNewAddress: {{ count($addresses) ? 'false' : 'true' }} }">

                    <div class="mb-6 flex items-center justify-between">

                        <div>
                            <p class="font-label-md text-label-md text-primary">Paso 2</p>
                            <h2 class="mt-1 text-2xl font-bold text-on-surface">Dirección de envío</h2>
                        </div>

                        @if (count($addresses))
                            <button
                                type="button"
                                @click="showNewAddress = !showNewAddress"
                                class="text-sm font-semibold text-primary hover:underline">
                                <span x-text="showNewAddress ? 'Ver mis direcciones' : '+ Agregar nueva'"></span>
                            </button>
                        @endif

                    </div>


                    {{-- Direcciones guardadas --}}

                    <div x-show="!showNewAddress" x-cloak class="space-y-4">

                        @forelse ($addresses as $address)

                            <div class="flex flex-col gap-4 rounded-xl border border-outline-variant/40 p-5 sm:flex-row sm:items-center sm:justify-between">

                                <div>
                                    <p class="font-semibold text-on-surface">
                                        {{ $address->firstName }} {{ $address->lastName }}
                                    </p>
                                    <p class="mt-1 text-sm text-secondary">
                                        {{ $address->lineOne }}@if($address->lineTwo), {{ $address->lineTwo }}@endif,
                                        {{ $address->city }}, {{ $address->state }}, {{ $address->postcode }}
                                    </p>
                                    <p class="text-sm text-secondary">{{ $address->countryName }}</p>
                                </div>

                                <div class="flex shrink-0 gap-2">

                                    <form method="POST" action="{{ route('checkout.address.shipping', $address->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary transition hover:brightness-110">
                                            Usar para envío
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('checkout.address.billing', $address->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-outline-variant px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-primary hover:text-primary">
                                            Usar para facturación
                                        </button>
                                    </form>

                                </div>

                            </div>

                        @empty

                            <p class="text-sm text-secondary">Todavía no tienes direcciones guardadas.</p>

                        @endforelse

                    </div>


                    {{-- Nueva dirección --}}

                    <form
                        x-show="showNewAddress"
                        x-cloak
                        method="POST"
                        action="{{ route('checkout.address.store') }}"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        @csrf

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-on-surface">Nombre</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-on-surface">Apellido</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-on-surface">Calle y número</label>
                            <input type="text" name="line_one" value="{{ old('line_one') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-on-surface">Colonia / referencia (opcional)</label>
                            <input type="text" name="line_two" value="{{ old('line_two') }}"
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">Ciudad</label>
                            <input type="text" name="city" value="{{ old('city') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">Estado</label>
                            <input type="text" name="state" value="{{ old('state') }}"
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">Código postal</label>
                            <input type="text" name="postcode" value="{{ old('postcode') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">País</label>
                            <select name="country_id" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected(old('country_id', $defaultCountryId) == $country->id)>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">Correo de contacto</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-on-surface">Teléfono de contacto</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required
                                class="w-full rounded-xl border border-outline-variant px-4 py-3 text-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full rounded-xl bg-primary px-6 py-3.5 font-semibold text-on-primary transition hover:brightness-110 sm:w-auto">
                                Guardar y usar esta dirección
                            </button>
                        </div>

                    </form>

                </div>


                {{-- ================================================= --}}
                {{-- ESTADO ACTUAL DE DIRECCIONES --}}
                {{-- ================================================= --}}

                <div class="rounded-2xl border border-outline-variant/30 bg-white p-8">

                    <h2 class="text-lg font-bold text-on-surface">Direcciones de este pedido</h2>

                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        <div>
                            <p class="font-label-md text-label-md text-secondary">Envío</p>

                            @if ($hasShippingAddress)
                                <p class="mt-2 text-sm text-on-surface">
                                    {{ $shippingAddressSummary->first_name }} {{ $shippingAddressSummary->last_name }}<br>
                                    {{ $shippingAddressSummary->line_one }}, {{ $shippingAddressSummary->city }}
                                </p>
                            @else
                                <p class="mt-2 text-sm text-secondary">Aún no has seleccionado una dirección de envío.</p>
                            @endif
                        </div>

                        <div>
                            <p class="font-label-md text-label-md text-secondary">Facturación</p>

                            @if ($hasBillingAddress)
                                <p class="mt-2 text-sm text-on-surface">
                                    {{ $billingAddressSummary->first_name }} {{ $billingAddressSummary->last_name }}<br>
                                    {{ $billingAddressSummary->line_one }}, {{ $billingAddressSummary->city }}
                                </p>
                            @else
                                <p class="mt-2 text-sm text-secondary">Aún no has seleccionado una dirección de facturación.</p>
                            @endif
                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- ENVÍO --}}
                {{-- ================================================= --}}

                <div class="rounded-2xl border border-outline-variant/30 bg-white p-8">

                    <h2 class="text-lg font-bold text-on-surface">Envío</h2>

                    <form method="POST" action="{{ route('shipping.postal-code') }}" class="mt-4 flex gap-3">
                        @csrf
                        <input
                            type="text"
                            name="postal_code"
                            maxlength="5"
                            placeholder="Código postal"
                            value="{{ $postalCode }}"
                            class="w-40 rounded-xl border border-outline-variant px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        <button type="submit" class="rounded-xl border border-outline-variant px-4 py-2.5 text-sm font-semibold text-on-surface hover:border-primary hover:text-primary">
                            Calcular
                        </button>
                    </form>

                    <div class="mt-5 flex items-start gap-4 rounded-xl bg-surface-container-low p-5">

                        <span class="material-symbols-outlined text-primary">local_shipping</span>

                        <div>
                            <p class="font-semibold text-on-surface">
                                Envío estándar —
                                {{ $shippingEstimate->freeShipping ? 'Gratis' : '$' . number_format($shippingEstimate->shippingCost, 2) . ' MXN' }}
                            </p>
                            <p class="mt-1 text-sm text-secondary">
                                Llega entre el {{ $shippingEstimate->from->translatedFormat('d \d\e F') }}
                                y el {{ $shippingEstimate->to->translatedFormat('d \d\e F') }}.
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- RESUMEN --}}
            {{-- ===================================================== --}}

            <div class="lg:col-span-1">

                <div class="sticky top-28 overflow-hidden rounded-2xl border border-outline-variant/30 bg-white">

                    <div class="p-6">

                        <h2 class="text-xl font-bold text-on-surface">Resumen del pedido</h2>

                        <div class="mt-6 max-h-72 space-y-4 overflow-y-auto pr-1">

                            @foreach ($cart->items as $item)

                                <div class="flex gap-4">

                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-outline-variant/40 bg-surface-container-low">
                                        @if ($item->image)
                                            <img src="{{ $item->image }}" alt="{{ $item->name }}" class="h-full w-full object-contain">
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-on-surface">{{ $item->name }}</p>
                                        <p class="mt-1 text-xs text-secondary">Cantidad: {{ $item->quantity }}</p>
                                        <p class="mt-1 text-sm font-semibold text-on-surface">{{ $item->total }}</p>
                                    </div>

                                </div>

                            @endforeach

                        </div>


                        <div class="mt-6 border-t border-outline-variant/30 pt-6">

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary">Subtotal</span>
                                <span class="font-medium text-on-surface">{{ $cart->subtotal }}</span>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm text-secondary">IVA</span>
                                <span class="text-sm text-on-surface">{{ $cart->tax }}</span>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm text-secondary">Envío</span>
                                <span class="text-sm text-on-surface">
                                    {{ $shippingEstimate->freeShipping ? 'Gratis' : '$' . number_format($shippingEstimate->shippingCost, 2) . ' MXN' }}
                                </span>
                            </div>

                            <div class="my-5 border-t border-dashed border-outline-variant/40"></div>

                            <div class="flex items-center justify-between">
                                <span class="font-bold text-on-surface">Total estimado</span>
                                <span class="text-xl font-black text-primary">{{ $cart->total }}</span>
                            </div>

                            <p class="mt-2 text-xs text-secondary">
                                El total final (incluyendo envío) se confirma al crear tu pedido.
                            </p>

                        </div>

                    </div>


                    <div class="border-t border-outline-variant/30 bg-surface-container-low p-6">

                        <form method="POST" action="{{ route('checkout.place') }}">
                            @csrf

                            <button
                                type="submit"
                                @if (! $hasShippingAddress || ! $hasBillingAddress) disabled @endif
                                class="flex w-full items-center justify-center rounded-xl bg-primary px-5 py-4 font-bold text-on-primary transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-40">
                                Confirmar pedido
                            </button>

                        </form>

                        @if (! $hasShippingAddress || ! $hasBillingAddress)
                            <p class="mt-3 text-center text-xs text-secondary">
                                Selecciona una dirección de envío y de facturación para continuar.
                            </p>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
