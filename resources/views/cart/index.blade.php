@extends('layouts.app')

@section('title', 'Carrito de compras')

@section('content')

<div
    x-data="cartWidget"
    data-cart-url="{{ route('cart.data') }}"
    x-init="init($el.dataset.cartUrl)"
    class="min-h-screen bg-gray-50">

    {{-- ========================================================= --}}
    {{-- PASOS DEL PROCESO --}}
    {{-- ========================================================= --}}

    <div class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-8">

            <div class="flex items-center justify-center">

                {{-- Paso 1 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white">
                            1
                        </span>

                        <span class="font-semibold text-blue-700">
                            Carrito de compras
                        </span>

                    </div>

                    <div class="mx-6 h-px w-20 bg-gray-200"></div>

                </div>


                {{-- Paso 2 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">
                            2
                        </span>

                        <span class="font-medium text-gray-400">
                            Elegir dirección
                        </span>

                    </div>

                    <div class="mx-6 h-px w-20 bg-gray-200"></div>

                </div>


                {{-- Paso 3 --}}
                <div class="flex items-center">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">
                            3
                        </span>

                        <span class="font-medium text-gray-400">
                            Envío y pago
                        </span>

                    </div>

                    <div class="mx-6 h-px w-20 bg-gray-200"></div>

                </div>


                {{-- Paso 4 --}}
                <div class="flex items-center gap-3">

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">
                        4
                    </span>

                    <span class="font-medium text-gray-400">
                        Confirmar pedido
                    </span>

                </div>

            </div>

        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- CONTENIDO --}}
    {{-- ========================================================= --}}

    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Carrito vacío --}}
        <template x-if="items.length === 0">

            <div class="rounded-2xl border border-gray-200 bg-white px-6 py-20 text-center shadow-sm">

                <span
                    class="material-symbols-outlined text-7xl text-gray-300">
                    shopping_cart
                </span>

                <h1 class="mt-6 text-2xl font-bold text-gray-900">
                    Tu carrito está vacío
                </h1>

                <p class="mt-2 text-gray-500">
                    Agrega productos para comenzar tu compra.
                </p>

                <a
                    href="{{ url('/') }}"
                    class="mt-8 inline-flex items-center justify-center rounded-xl bg-blue-700 px-8 py-3 font-semibold text-white transition hover:bg-blue-800">

                    Seguir comprando

                </a>

            </div>

        </template>


        {{-- Carrito con productos --}}
        <template x-if="items.length > 0">

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- ================================================= --}}
                {{-- PRODUCTOS --}}
                {{-- ================================================= --}}

                <div class="space-y-4 lg:col-span-2">

                    <div class="mb-2">

                        <h1 class="text-2xl font-bold text-gray-900">
                            Carrito de compras
                        </h1>

                        <p
                            class="mt-1 text-sm text-gray-500"
                            x-text="
                                quantity === 1
                                    ? '1 producto'
                                    : `${quantity} productos`
                            ">
                        </p>

                    </div>


                    {{-- Items --}}
                    <template
                        x-for="item in items"
                        :key="item.lineId">

                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                            <div class="flex flex-col gap-6 p-6 sm:flex-row">

                                {{-- Imagen --}}
                                <div
                                    class="flex h-32 w-full shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 sm:w-32">

                                    <template x-if="item.image">

                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="h-full w-full object-contain">

                                    </template>

                                    <template x-if="!item.image">

                                        <span
                                            class="material-symbols-outlined text-5xl text-gray-300">
                                            image
                                        </span>

                                    </template>

                                </div>


                                {{-- Información --}}
                                <div class="min-w-0 flex-1">

                                    <h2
                                        class="text-lg font-semibold text-gray-900"
                                        x-text="item.name">
                                    </h2>


                                    <p
                                        x-show="item.sku"
                                        class="mt-1 text-sm text-gray-400"
                                        x-text="`SKU: ${item.sku}`">
                                    </p>


                                    {{-- Precio --}}
                                    <div class="mt-5">

                                        <p
                                            class="text-xl font-bold text-gray-900"
                                            x-text="item.total">
                                        </p>

                                        <p
                                            class="mt-1 text-sm text-gray-500"
                                            x-text="`${item.unitPrice} c/u`">
                                        </p>

                                    </div>


                                    {{-- Acciones --}}
                                    <div class="mt-5 flex flex-wrap items-center gap-5">

                                        {{-- Cantidad --}}
                                        <div
                                            class="flex items-center overflow-hidden rounded-xl border border-gray-300">

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
                                                class="flex h-10 w-10 items-center justify-center text-lg font-semibold text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                −

                                            </button>


                                            <span
                                                class="flex h-10 min-w-12 items-center justify-center border-x border-gray-300 px-3 text-sm font-semibold text-gray-900"
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
                                                class="flex h-10 w-10 items-center justify-center text-lg font-semibold text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                +

                                            </button>

                                        </div>


                                        {{-- Eliminar --}}
                                        <button
                                            type="button"
                                            @click="removeItem(item.lineId)"
                                            :disabled="loading"
                                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-red-600 disabled:opacity-40">

                                            <span class="material-symbols-outlined text-lg">
                                                delete
                                            </span>

                                            Eliminar

                                        </button>


                                        {{-- Guardar --}}
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-blue-700">

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
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40">

                            <span class="material-symbols-outlined text-lg">
                                delete_sweep
                            </span>

                            Vaciar carrito

                        </button>

                    </div>


                    {{-- ================================================= --}}
                    {{-- SEGURIDAD / BENEFICIOS --}}
                    {{-- ================================================= --}}

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                        <h3 class="text-lg font-bold text-gray-900">
                            Compra segura
                        </h3>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-green-600">
                                    verified_user
                                </span>

                                <div>
                                    <p class="font-medium text-gray-900">
                                        Pago seguro
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Tus datos se procesan de forma segura.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-green-600">
                                    local_shipping
                                </span>

                                <div>
                                    <p class="font-medium text-gray-900">
                                        Envío asegurado
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Enviamos mediante paqueterías confiables.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-green-600">
                                    receipt_long
                                </span>

                                <div>
                                    <p class="font-medium text-gray-900">
                                        Facturación
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Podrás solicitar factura durante el proceso.
                                    </p>
                                </div>

                            </div>


                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-green-600">
                                    support_agent
                                </span>

                                <div>
                                    <p class="font-medium text-gray-900">
                                        Atención personalizada
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
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

                    <div class="sticky top-24 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                        <div class="p-6">

                            <h2 class="text-xl font-bold text-gray-900">
                                Resumen de compra
                            </h2>


                            {{-- Subtotal --}}
                            <div class="mt-6 flex items-center justify-between">

                                <span class="text-sm text-gray-500">
                                    Total de productos
                                </span>

                                <span
                                    class="font-medium text-gray-900"
                                    x-text="subtotal">
                                </span>

                            </div>


                            {{-- Envío --}}
                            <div class="mt-3 flex items-center justify-between">

                                <span class="text-sm text-gray-500">
                                    Costo de envío
                                </span>

                                <span
                                    class="font-medium text-gray-900"
                                    x-text="shipping">
                                </span>

                            </div>


                            {{-- IVA --}}
                            <div class="mt-3 flex items-center justify-between">

                                <span class="text-sm text-gray-500">
                                    IVA
                                </span>

                                <span
                                    class="font-medium text-gray-900"
                                    x-text="tax">
                                </span>

                            </div>


                            {{-- Total --}}
                            <div class="my-6 border-t border-gray-200"></div>

                            <div class="flex items-center justify-between">

                                <span class="font-bold text-gray-900">
                                    Total
                                </span>

                                <span
                                    class="text-2xl font-black text-blue-700"
                                    x-text="total">
                                </span>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- LOGIN / CHECKOUT --}}
                        {{-- ================================================= --}}

                        <div class="border-t border-gray-200 bg-gray-50 p-6">

                            @auth

                            <div class="mb-4 flex items-start gap-3">

                                <span class="material-symbols-outlined text-green-600">
                                    verified
                                </span>

                                <div>

                                    <p class="font-semibold text-gray-900">
                                        Sesión iniciada
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Puedes continuar con tu compra.
                                    </p>

                                </div>

                            </div>


                            <a
                                href="{{ route('checkout.index') }}"
                                class="flex w-full items-center justify-center rounded-xl bg-blue-700 px-5 py-4 font-bold text-white transition hover:bg-blue-800">
                                Continuar con la compra
                            </a>

                            @else

                            <div class="mb-5">

                                <p class="font-semibold text-gray-900">
                                    Inicia sesión para continuar
                                </p>

                                <p class="mt-2 text-sm leading-6 text-gray-500">
                                    Necesitamos que inicies sesión para seleccionar tu dirección,
                                    calcular el envío y realizar el pago de forma segura.
                                </p>

                            </div>


                            <a
                                href="{{ route('login') }}"
                                class="flex w-full items-center justify-center rounded-xl bg-orange-500 px-5 py-4 font-bold text-white transition hover:bg-orange-600">

                                Iniciar sesión para continuar

                            </a>


                            @if(Route::has('register'))

                            <p class="mt-4 text-center text-sm text-gray-500">

                                ¿No tienes cuenta?

                                <a
                                    href="{{ route('register') }}"
                                    class="font-semibold text-blue-700 hover:text-blue-800">

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