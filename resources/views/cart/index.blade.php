@extends('layouts.app')

@section('title', 'Carrito de compras')

@section('content')

<div
    x-data="cartWidget"
    data-cart-url="{{ route('cart.data') }}"
    data-free-shipping-from="{{ config('shipping.free_shipping_from') }}"
    data-standard-shipping-cost="{{ config('shipping.standard_cost') }}"
    x-init="init($el.dataset.cartUrl, Number($el.dataset.freeShippingFrom), Number($el.dataset.standardShippingCost))"
    class="min-h-screen bg-[#f5f5f3]">

    {{-- ========================================================= --}}
    {{-- PASOS DEL PROCESO --}}
    {{-- ========================================================= --}}

    <div class="border-b border-neutral-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-8">

            <div class="flex items-center justify-center overflow-x-auto">

                {{-- Paso 1 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-[#f5a623] text-sm font-extrabold text-neutral-950">
                            1
                        </span>

                        <span class="whitespace-nowrap text-xs font-bold text-neutral-950 sm:text-sm">
                            Carrito de compras
                        </span>

                    </div>

                    <div class="mx-3 h-px w-8 bg-neutral-200 sm:mx-6 sm:w-20"></div>

                </div>


                {{-- Paso 2 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-sm font-semibold text-neutral-500">
                            2
                        </span>

                        <span class="hidden whitespace-nowrap text-xs font-medium text-neutral-400 sm:inline sm:text-sm">
                            Elegir dirección
                        </span>

                    </div>

                    <div class="mx-3 h-px w-8 bg-neutral-200 sm:mx-6 sm:w-20"></div>

                </div>


                {{-- Paso 3 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-sm font-semibold text-neutral-500">
                            3
                        </span>

                        <span class="hidden whitespace-nowrap text-xs font-medium text-neutral-400 sm:inline sm:text-sm">
                            Envío y pago
                        </span>

                    </div>

                    <div class="mx-3 h-px w-8 bg-neutral-200 sm:mx-6 sm:w-20"></div>

                </div>


                {{-- Paso 4 --}}
                <div class="flex items-center gap-3">

                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-sm font-semibold text-neutral-500">
                        4
                    </span>

                    <span class="hidden whitespace-nowrap text-xs font-medium text-neutral-400 sm:inline sm:text-sm">
                        Confirmar pedido
                    </span>

                </div>

            </div>

        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- CONTENIDO --}}
    {{-- ========================================================= --}}

    <div class="mx-auto max-w-[1340px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        {{-- Carrito vacío --}}
        <template x-if="items.length === 0">

            <div class="rounded-2xl border border-neutral-200 bg-white px-6 py-20 text-center shadow-sm">

                <span
                    class="material-symbols-outlined text-7xl text-neutral-300">
                    shopping_cart
                </span>

                <h1 class="mt-6 font-heading text-2xl font-extrabold text-neutral-950">
                    Tu carrito está vacío
                </h1>

                <p class="mt-2 text-sm text-neutral-500">
                    Agrega productos para comenzar tu compra.
                </p>

                <a
                    href="{{ url('/') }}"
                    class="mt-8 inline-flex items-center justify-center rounded-full bg-[#f5a623] px-8 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">

                    Seguir comprando

                </a>

            </div>

        </template>


        {{-- Carrito con productos --}}
        <template x-if="items.length > 0">

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6">

                {{-- ================================================= --}}
                {{-- PRODUCTOS --}}
                {{-- ================================================= --}}

                <div class="space-y-4 lg:col-span-2">

                    <div class="mb-2">

                        <h1 class="font-heading text-2xl font-extrabold text-neutral-950">
                            Carrito de compras
                        </h1>

                        <p
                            class="mt-1 text-xs text-neutral-500"
                            x-text="
                                quantity === 1
                                    ? '1 producto'
                                    : `${quantity} productos`
                            ">
                        </p>

                    </div>

                    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-5" aria-live="polite">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined mt-0.5 text-xl text-[#bd7710]" aria-hidden="true">local_shipping</span>
                            <div class="min-w-0 flex-1">
                                <p x-show="!freeShipping" class="text-sm font-bold text-neutral-900"
                                    x-text="`Agrega $${amountUntilFreeShipping.toFixed(2)} MXN para obtener envío gratis`"></p>
                                <p x-show="freeShipping" class="text-sm font-bold text-emerald-700">
                                    ¡Tu pedido tiene envío gratis!
                                </p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-neutral-100" role="progressbar"
                                    aria-label="Progreso para obtener envío gratis"
                                    aria-valuemin="0" aria-valuemax="100"
                                    :aria-valuenow="Math.round(freeShippingProgress)">
                                    <div class="h-full rounded-full bg-[#f5a623] transition-all duration-300"
                                        :style="`width: ${freeShippingProgress}%`"></div>
                                </div>
                                <p class="mt-2 text-[11px] text-neutral-500">
                                    Envío gratis en compras de ${{ number_format(config('shipping.free_shipping_from'), 0) }} MXN o más.
                                </p>
                            </div>
                        </div>
                    </section>


                    {{-- Items --}}
                    <template
                        x-for="item in items"
                        :key="item.lineId">

                        <div
                            class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">

                            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:gap-5 sm:p-5">

                                {{-- Imagen --}}
                                <div
                                    class="flex h-32 w-full shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#f5f5f3] sm:w-32">

                                    <template x-if="item.image">

                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="h-full w-full object-contain">

                                    </template>

                                    <template x-if="!item.image">

                                        <span
                                            class="material-symbols-outlined text-5xl text-neutral-300">
                                            image
                                        </span>

                                    </template>

                                </div>


                                {{-- Información --}}
                                <div class="min-w-0 flex-1">

                                    <h2
                                        class="text-sm font-bold text-neutral-950 sm:text-base"
                                        x-text="item.name">
                                    </h2>


                                    <p
                                        x-show="item.sku"
                                        class="mt-1 text-xs text-neutral-400"
                                        x-text="`SKU: ${item.sku}`">
                                    </p>


                                    {{-- Precio --}}
                                    <div class="mt-5">

                                        <p
                                            class="text-lg font-extrabold text-neutral-950"
                                            x-text="item.total">
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-neutral-500"
                                            x-text="`${item.unitPrice} c/u`">
                                        </p>

                                    </div>


                                    {{-- Acciones --}}
                                    <div class="mt-5 flex flex-wrap items-center gap-5">

                                        {{-- Cantidad --}}
                                        <div
                                            class="flex items-center overflow-hidden rounded-xl border border-neutral-200">

                                            <button
                                                type="button"
                                                @click="
                                                    updateItem(
                                                        item.lineId,
                                                        quantityRules.prev(
                                                            item.quantity,
                                                            item.minQuantity,
                                                            item.quantityIncrement
                                                        )
                                                    )
                                                "
                                                :disabled="
                                                    loading ||
                                                    item.quantity <= quantityRules.floor(item.minQuantity, item.quantityIncrement)
                                                "
                                                class="flex h-10 w-10 items-center justify-center text-lg font-semibold text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                −

                                            </button>


                                            <span
                                                class="flex h-10 min-w-12 items-center justify-center border-x border-neutral-200 px-3 text-sm font-semibold text-neutral-900"
                                                x-text="item.quantity">
                                            </span>


                                            <button
                                                type="button"
                                                @click="
                                                    updateItem(
                                                        item.lineId,
                                                        quantityRules.next(
                                                            item.quantity,
                                                            item.minQuantity,
                                                            item.quantityIncrement,
                                                            item.maxQuantity
                                                        )
                                                    )
                                                "
                                                :disabled="loading || item.quantity >= item.maxQuantity"
                                                class="flex h-10 w-10 items-center justify-center text-lg font-semibold text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                +

                                            </button>

                                        </div>


                                        {{-- Eliminar --}}
                                        <button
                                            type="button"
                                            @click="removeItem(item.lineId)"
                                            :disabled="loading"
                                            class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-500 transition hover:text-red-600 disabled:opacity-40">

                                            <span class="material-symbols-outlined text-lg">
                                                delete
                                            </span>

                                            Eliminar

                                        </button>


                                        {{-- Guardar --}}
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-500 transition hover:text-[#a76500]">

                                            <span class="material-symbols-outlined text-lg">
                                                schedule
                                            </span>

                                            Guardar para más tarde

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </template>


                    {{-- Vaciar --}}
                    <div class="flex justify-end pt-2">

                        <button
                            type="button"
                            @click="clearCart()"
                            :disabled="loading || items.length === 0"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-500 transition hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40">

                            <span class="material-symbols-outlined text-lg">
                                delete_sweep
                            </span>

                            Vaciar carrito

                        </button>

                    </div>


                    {{-- ================================================= --}}
                    {{-- SEGURIDAD / BENEFICIOS --}}
                    {{-- ================================================= --}}

                    <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-6">

                        <h3 class="font-heading text-lg font-extrabold text-neutral-950">
                            Compra segura
                        </h3>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[#bd7710]">
                                    verified_user
                                </span>

                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">
                                        Pago seguro
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Tus datos se procesan de forma segura.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[#bd7710]">
                                    local_shipping
                                </span>

                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">
                                        Envío asegurado
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Enviamos mediante paqueterías confiables.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[#bd7710]">
                                    receipt_long
                                </span>

                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">
                                        Facturación
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Podrás solicitar factura durante el proceso.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[#bd7710]">
                                    support_agent
                                </span>

                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">
                                        Atención personalizada
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Estamos disponibles para ayudarte.
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- RESUMEN --}}
                {{-- ================================================= --}}

                <div class="lg:col-span-1">

                    <div class="sticky top-24 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">

                        <div class="p-6">

                            <h2 class="font-heading text-lg font-extrabold text-neutral-950">
                                Resumen de compra
                            </h2>


                            {{-- Subtotal --}}
                            <div class="mt-6 flex items-center justify-between">

                                <span class="text-xs text-neutral-500">
                                    Total de productos
                                </span>

                                <span
                                    class="text-sm font-semibold text-neutral-900"
                                    x-text="subtotal">
                                </span>

                            </div>


                            {{-- Envío --}}
                            <div class="mt-3 flex items-center justify-between">

                                <span class="text-xs text-neutral-500">
                                    Costo de envío
                                </span>

                                <span
                                    class="text-sm font-semibold text-neutral-900"
                                    x-text="estimatedShippingLabel">
                                </span>

                            </div>


                            {{-- IVA --}}
                            <div class="mt-3 flex items-center justify-between">

                                <span class="text-xs text-neutral-500">
                                    IVA
                                </span>

                                <span
                                    class="text-sm font-semibold text-neutral-900"
                                    x-text="tax">
                                </span>

                            </div>


                            {{-- Total --}}
                            <div class="my-5 border-t border-neutral-200"></div>

                            <div class="flex items-center justify-between">
                                <span class="font-bold text-neutral-900">
                                    Total (sin envío)
                                </span>

                                <span
                                    class="text-xl font-black text-neutral-950 sm:text-2xl"
                                    x-text="total">
                                </span>
                            </div>
                            <p class="mt-2 text-[11px] leading-5 text-neutral-500">
                                El envío se estima aquí y se confirma al finalizar tu compra.
                            </p>

                        </div>


                        {{-- ================================================= --}}
                        {{-- LOGIN / CHECKOUT --}}
                        {{-- ================================================= --}}

                        <div class="border-t border-neutral-200 bg-[#f5f5f3] p-5 sm:p-6">

                            @auth

                            <div class="mb-4 flex items-start gap-3">

                                <span class="material-symbols-outlined text-emerald-600">
                                    verified
                                </span>

                                <div>

                                    <p class="text-sm font-bold text-neutral-900">
                                        Sesión iniciada
                                    </p>

                                    <p class="mt-1 text-xs text-neutral-500">
                                        Puedes continuar con tu compra.
                                    </p>

                                </div>

                            </div>


                            <a
                                href="{{ route('checkout.index') }}"
                                class="flex w-full items-center justify-center rounded-full bg-[#f5a623] px-5 py-3.5 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">
                                Continuar con la compra
                            </a>

                            @else

                            <div class="mb-5">

                                <p class="text-sm font-bold text-neutral-900">
                                    Inicia sesión para continuar
                                </p>

                                <p class="mt-2 text-xs leading-5 text-neutral-500">
                                    Necesitamos que inicies sesión para seleccionar tu dirección,
                                    calcular el envío y realizar el pago de forma segura.
                                </p>

                            </div>


                            <a
                                href="{{ route('login') }}"
                                class="flex w-full items-center justify-center rounded-full bg-[#f5a623] px-5 py-3.5 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">

                                Iniciar sesión para continuar

                            </a>


                            @if(Route::has('register'))

                            <p class="mt-4 text-center text-xs text-neutral-500">

                                ¿No tienes cuenta?

                                <a
                                    href="{{ route('register') }}"
                                    class="font-bold text-[#a76500] hover:text-neutral-950">

                                    Crear cuenta

                                </a>

                            </p>

                            @endif

                            @endauth

                        </div>

                    </div>

                </div>

            </div>

        </template>

    </div>

</div>

@endsection