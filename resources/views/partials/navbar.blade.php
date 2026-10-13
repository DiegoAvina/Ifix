{{--
    Partial: Navbar / Top Navigation Bar

    Incluir con: @include('partials.navbar')
--}}

@php

$navLinks = $navLinks ?? [

[
'label' => 'Inicio',
'url' => route('home'),
'active' => request()->routeIs('home')
],

[
'label' => 'Productos',
'url' => route('catalog.index'),
'active' => request()->routeIs('catalog.*')
],

[
'label' => 'Carrito',
'url' => route('cart.index'),
'active' => request()->routeIs('cart.index')
],

];

@endphp


<header
    x-data="{ mobileMenuOpen: false, scrolled: false }"
    x-init="scrolled = window.scrollY > 8"
    @scroll.window="scrolled = window.scrollY > 8"
    :class="scrolled ? 'shadow-[0_8px_30px_-15px_rgba(0,0,0,0.15)] border-gray-200/80' : 'shadow-none border-gray-100'"
    class="bg-white/80 backdrop-blur-xl w-full border-b sticky top-0 z-50 transition-all duration-300">

    <div
        class="max-w-screen-2xl mx-auto px-5 sm:px-8 h-20 flex justify-between items-center gap-4 font-inter tracking-tight antialiased">

        {{-- Logo + botón menú móvil --}}

        <div class="flex items-center gap-3 shrink-0">

            {{-- Botón menú móvil --}}

            <button
                type="button"
                @click="mobileMenuOpen = true"
                class="lg:hidden flex h-10 w-10 items-center justify-center rounded-full text-secondary transition-colors duration-300 hover:bg-gray-100 hover:text-primary active:opacity-70"
                aria-label="Abrir menú"
                :aria-expanded="mobileMenuOpen.toString()">

                <span class="material-symbols-outlined" aria-hidden="true">
                    menu
                </span>

            </button>


            {{-- Logo --}}

            <a
                href="{{ url('/') }}"
                class="group flex items-center gap-2.5">

                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary via-blue-700 to-blue-600 text-white text-sm font-black shadow-md shadow-blue-700/25 transition-transform duration-300 group-hover:scale-105 group-hover:rotate-3">
                    O
                </span>

                <span
                    class="text-xl font-black tracking-tighter text-primary uppercase">
                    Ifix
                </span>

            </a>

        </div>


        {{-- Navegación principal --}}

        <nav
            class="hidden lg:flex items-center gap-1 rounded-full border border-gray-100 bg-gray-50/70 p-1.5"
            aria-label="Navegación principal">

            @foreach ($navLinks as $link)

            <a
                href="{{ $link['url'] }}"
                class="{{ $link['active']

                        ? 'bg-white text-primary shadow-sm shadow-gray-200/70 font-semibold'

                        : 'text-secondary font-medium hover:text-primary hover:bg-white/60'

                    }} rounded-full px-4 py-2 text-sm transition-all duration-300"
                @if ($link['active'])
                aria-current="page"
                @endif>

                {{ $link['label'] }}

            </a>

            @endforeach

        </nav>


        {{-- Acciones: búsqueda + carrito --}}

        <div class="flex items-center gap-2 sm:gap-3">

            {{-- Search integrado --}}

            <div class="hidden sm:block">
                <x-search />
            </div>


            {{-- ========================================================= --}}
            {{-- CARRITO --}}
            {{-- ========================================================= --}}

            <div
                x-data="cartWidget"
                data-cart-url="{{ route('cart.data') }}"
                x-init="init($el.dataset.cartUrl)"
                class="relative">

                {{-- Botón del carrito --}}

                <button
                    type="button"
                    @click="open = !open"
                    class="relative flex h-11 w-11 items-center justify-center rounded-full text-secondary transition-all duration-300 hover:bg-gray-100 hover:text-primary hover:scale-105 active:scale-95"
                    aria-label="Ver carrito"
                    :aria-expanded="open.toString()">

                    <span
                        class="material-symbols-outlined text-[22px]"
                        aria-hidden="true">
                        shopping_cart
                    </span>


                    {{-- Contador --}}

                    <span
                        x-show="quantity > 0"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-text="quantity"
                        class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-blue-700 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white"></span>

                </button>


                {{-- ===================================================== --}}
                {{-- MINI CARRITO --}}
                {{-- ===================================================== --}}

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                    class="absolute right-0 top-full z-[100] mt-4 w-[380px] origin-top-right overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-2xl shadow-gray-900/10">

                    {{-- Header --}}

                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-5 py-4">

                        <div>

                            <h3
                                class="font-bold text-gray-900">
                                Tu carrito
                            </h3>

                            <p
                                class="mt-1 text-xs text-gray-500"
                                x-text="
                                    quantity === 1
                                        ? '1 producto'
                                        : `${quantity} productos`
                                "></p>

                        </div>


                        <button
                            type="button"
                            @click="open = false"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition duration-300 hover:rotate-90 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Cerrar carrito">

                            <span class="material-symbols-outlined text-lg">
                                close
                            </span>

                        </button>

                    </div>


                    {{-- ================================================= --}}
                    {{-- PRODUCTOS --}}
                    {{-- ================================================= --}}

                    <div class="max-h-[360px] overflow-y-auto px-3">

                        {{-- Carrito vacío --}}

                        <template x-if="items.length === 0">

                            <div class="px-2 py-12 text-center">

                                <div
                                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-50">

                                    <span
                                        class="material-symbols-outlined text-4xl text-gray-300">
                                        shopping_cart
                                    </span>

                                </div>

                                <p
                                    class="mt-4 font-semibold text-gray-800">
                                    Tu carrito está vacío
                                </p>

                                <p
                                    class="mt-1 text-sm text-gray-400">
                                    Agrega productos para comenzar.
                                </p>

                                <a
                                    href="{{ url('/') }}"
                                    class="mt-5 inline-flex items-center justify-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                                    Explorar productos
                                </a>

                            </div>

                        </template>


                        {{-- Items --}}

                        <div
                            x-show="items.length > 0"
                            class="divide-y divide-gray-100">

                            <template
                                x-for="item in items"
                                :key="item.lineId">

                                <div
                                    class="group flex gap-4 rounded-2xl px-2 py-4 transition-colors duration-200 hover:bg-gray-50">

                                    {{-- Imagen --}}

                                    <div
                                        class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 transition-colors duration-200 group-hover:border-gray-200">

                                        <template x-if="item.image">

                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                class="h-full w-full object-contain">

                                        </template>


                                        <template x-if="!item.image">

                                            <div
                                                class="flex h-full w-full items-center justify-center">

                                                <span
                                                    class="material-symbols-outlined text-gray-300">
                                                    image
                                                </span>

                                            </div>

                                        </template>

                                    </div>


                                    {{-- Información --}}

                                    <div class="min-w-0 flex-1">

                                        <p
                                            class="truncate font-semibold text-gray-900"
                                            x-text="item.name"></p>


                                        <p
                                            x-show="item.sku"
                                            class="mt-0.5 truncate text-[11px] uppercase tracking-wide text-gray-400"
                                            x-text="`SKU: ${item.sku}`"></p>


                                        <div
                                            class="mt-2.5 flex items-center justify-between">

                                            <span
                                                class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600"
                                                x-text="`Cant. ${item.quantity}`"></span>


                                            <span
                                                class="font-bold text-gray-900 tabular-nums"
                                                x-text="item.total"></span>

                                        </div>

                                    </div>

                                </div>

                            </template>

                        </div>

                    </div>


                    {{-- ================================================= --}}
                    {{-- RESUMEN --}}
                    {{-- ================================================= --}}

                    <div
                        class="border-t border-gray-100 bg-gray-50/70 px-5 py-5">

                        <div class="flex items-center justify-between">

                            <span
                                class="text-sm text-gray-500">
                                Subtotal
                            </span>

                            <span
                                class="font-semibold text-gray-900 tabular-nums"
                                x-text="subtotal"></span>

                        </div>


                        <div
                            class="mt-2 flex items-center justify-between">

                            <span
                                class="text-sm text-gray-500">
                                IVA
                            </span>

                            <span
                                class="text-sm text-gray-600 tabular-nums"
                                x-text="tax"></span>

                        </div>


                        <div
                            class="mt-4 flex items-center justify-between border-t border-dashed border-gray-200 pt-4">

                            <span
                                class="font-bold text-gray-900">
                                Total
                            </span>

                            <span
                                class="text-xl font-black text-gray-900 tabular-nums"
                                x-text="total"></span>

                        </div>

                        <button
                            type="button"
                            @click="clearCart()"
                            :disabled="loading || items.length === 0"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition duration-300 hover:border-red-200 hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40">

                            <span class="material-symbols-outlined text-lg">
                                delete_sweep
                            </span>

                            Vaciar carrito

                        </button>


                        {{-- Ver carrito --}}

                        <a
                            href="{{ route('cart.index') }}"
                            class="mt-3 flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-primary to-blue-700 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-700/25 transition duration-300 hover:shadow-blue-700/40 hover:brightness-110">
                            Ver carrito
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================= --}}
    {{-- MENÚ MÓVIL (drawer) --}}
    {{-- ================================================================= --}}

    <div
        x-show="mobileMenuOpen"
        x-cloak
        class="fixed inset-0 z-[110] lg:hidden">

        {{-- Overlay --}}

        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
            class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>


        {{-- Panel --}}

        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @keydown.escape.window="mobileMenuOpen = false"
            class="absolute inset-y-0 left-0 flex w-[85%] max-w-xs flex-col bg-white px-5 py-6 shadow-2xl">

            <div class="flex items-center justify-between">

                <span
                    class="text-lg font-black tracking-tighter text-primary uppercase">
                    Ifix
                </span>

                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Cerrar menú">

                    <span class="material-symbols-outlined text-lg">
                        close
                    </span>

                </button>

            </div>


            <div class="mt-4 sm:hidden">
                <x-search />
            </div>


            <nav class="mt-6 flex flex-col gap-1" aria-label="Navegación móvil">

                @foreach ($navLinks as $link)

                <a
                    href="{{ $link['url'] }}"
                    class="{{ $link['active']

                            ? 'bg-primary/5 text-primary font-semibold'

                            : 'text-secondary font-medium hover:bg-gray-50 hover:text-primary'

                        }} rounded-xl px-4 py-3 text-sm transition-colors duration-300"
                    @if ($link['active'])
                    aria-current="page"
                    @endif>

                    {{ $link['label'] }}

                </a>

                @endforeach

            </nav>

        </div>

    </div>

</header>