@extends('layouts.app')

@section('title', 'Carrito de compras')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div
    x-data="cartWidget"
    data-cart-url="{{ route('cart.data') }}"
    data-free-shipping-from="{{ (int) ($freeShippingFrom ?? 0) }}"
    data-standard-shipping-cost="{{ $cart->estimatedShipping ?? 0 }}"
    x-init="init($el.dataset.cartUrl, Number($el.dataset.freeShippingFrom), Number($el.dataset.standardShippingCost))"
    class="min-h-screen bg-[#f5f5f3]">

    {{-- ========================================================= --}}
    {{-- PASOS DEL PROCESO --}}
    {{-- ========================================================= --}}

    @php
        $checkoutSteps = ['Carrito de compras', 'Elegir dirección', 'Envío y pago', 'Confirmar pedido'];
    @endphp

    <div class="border-b border-neutral-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:py-8">

            {{-- Móvil y tablet: los 4 pasos no caben en una línea; se muestra el actual y una barra de avance. --}}
            <div class="lg:hidden">
                <div class="flex items-center justify-between gap-3 text-xs">
                    <p class="font-bold text-neutral-950">
                        <span class="text-[#bd7710]">Paso 1 de {{ count($checkoutSteps) }}</span>
                        <span aria-hidden="true">·</span>
                        {{ $checkoutSteps[0] }}
                    </p>
                    <p class="hidden text-neutral-500 sm:block">Sigue: {{ $checkoutSteps[1] }}</p>
                </div>
                <div class="mt-2.5 grid gap-1.5" style="grid-template-columns: repeat({{ count($checkoutSteps) }}, minmax(0, 1fr));" aria-hidden="true">
                    @foreach ($checkoutSteps as $step)
                        <span @class(['h-1.5 rounded-full', 'bg-[#f5a623]' => $loop->first, 'bg-neutral-100' => ! $loop->first])></span>
                    @endforeach
                </div>
            </div>

            {{-- Escritorio: los 4 pasos completos. --}}
            <ol class="hidden items-center justify-center lg:flex" aria-label="Pasos de la compra">
                @foreach ($checkoutSteps as $step)
                    <li class="flex items-center" @if ($loop->first) aria-current="step" @endif>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'flex h-9 w-9 items-center justify-center rounded-full text-sm',
                                'bg-[#f5a623] font-extrabold text-neutral-950' => $loop->first,
                                'bg-neutral-100 font-semibold text-neutral-500' => ! $loop->first,
                            ])>{{ $loop->iteration }}</span>
                            <span @class([
                                'whitespace-nowrap text-sm',
                                'font-bold text-neutral-950' => $loop->first,
                                'font-medium text-neutral-400' => ! $loop->first,
                            ])>{{ $step }}</span>
                        </div>
                        @unless ($loop->last)
                            <div class="mx-4 h-px w-12 bg-neutral-200 xl:mx-6 xl:w-20" aria-hidden="true"></div>
                        @endunless
                    </li>
                @endforeach
            </ol>

        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- CONTENIDO --}}
    {{-- ========================================================= --}}

    {{-- pb extra en móvil/tablet: deja espacio a la barra fija de "Continuar". --}}
    <div class="mx-auto max-w-[1340px] px-4 pb-28 pt-6 sm:px-6 sm:pt-8 lg:px-8 lg:pb-8">

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
                                    @if ($freeShippingFrom)
                                        Envío gratis en compras de ${{ number_format($freeShippingFrom, 0) }} MXN o más.
                                    @endif
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

                            {{-- Foto a la izquierda en todos los tamaños: en móvil, una foto a todo el ancho hacía cada producto muy alto. --}}
                            <div class="flex gap-3 p-3 sm:gap-5 sm:p-5">

                                {{-- Imagen --}}
                                <div
                                    class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#f5f5f3] p-1 sm:h-32 sm:w-32">

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
                                        class="line-clamp-2 text-sm font-bold leading-snug text-neutral-950 sm:text-base"
                                        x-text="item.name">
                                    </h2>


                                    <p
                                        x-show="item.sku"
                                        class="mt-1 text-xs text-neutral-400"
                                        x-text="`SKU: ${item.sku}`">
                                    </p>


                                    {{-- Precio --}}
                                    <div class="mt-2 sm:mt-5">

                                        <p
                                            class="text-base font-extrabold tabular-nums text-neutral-950 sm:text-lg"
                                            x-text="item.total">
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-neutral-500"
                                            x-text="`${item.unitPrice} c/u`">
                                        </p>

                                    </div>


                                    {{-- Acciones --}}
                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 sm:mt-5 sm:gap-5">

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
                                                aria-label="Quitar uno"
                                                class="flex h-9 w-9 items-center justify-center text-lg font-semibold text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40 sm:h-10 sm:w-10">

                                                −

                                            </button>


                                            <span
                                                class="flex h-9 min-w-11 items-center justify-center border-x border-neutral-200 px-3 text-sm font-semibold tabular-nums text-neutral-900 sm:h-10 sm:min-w-12"
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
                                                aria-label="Agregar uno"
                                                class="flex h-9 w-9 items-center justify-center text-lg font-semibold text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40 sm:h-10 sm:w-10">

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


                </div>


                {{-- ================================================= --}}
                {{-- SEGURIDAD / BENEFICIOS --}}
                {{-- ================================================= --}}
                {{-- En móvil va al final, después del resumen; en escritorio, bajo los productos. --}}

                <div class="order-last lg:order-none lg:col-span-2">

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

                {{-- En escritorio ocupa la columna derecha junto a productos y beneficios, y se queda fijo bajo el navbar (143 px). --}}
                <div class="lg:col-start-3 lg:row-span-2 lg:row-start-1">

                    <div id="resumen-compra" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm lg:sticky lg:top-[164px]">

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


                {{-- ================================================= --}}
                {{-- BARRA FIJA (MÓVIL Y TABLET) --}}
                {{-- ================================================= --}}
                {{-- El total y el botón siempre a la mano, sin bajar hasta el resumen. --}}

                <div
                    class="fixed inset-x-0 bottom-0 z-40 border-t border-neutral-200 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_-18px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-[#17191b]/95 sm:px-6 lg:hidden"
                    style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
                    <div class="mx-auto flex max-w-[1340px] items-center gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] text-neutral-500">Total (sin envío)</p>
                            <p class="truncate text-lg font-black tabular-nums text-neutral-950" x-text="total"></p>
                        </div>
                        <a
                            href="{{ auth()->check() ? route('checkout.index') : route('login') }}"
                            class="ml-auto inline-flex min-w-0 max-w-xs flex-1 items-center justify-center gap-2 rounded-full bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400 active:scale-[0.98]">
                            <span class="truncate">{{ auth()->check() ? 'Continuar compra' : 'Iniciar sesión' }}</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>
                </div>

            </div>

        </template>

    </div>

</div>

@endsection