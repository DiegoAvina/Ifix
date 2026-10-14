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


<div
    x-data="{ mobileMenuOpen: false, scrolled: false }"
    x-init="scrolled = window.scrollY > 8"
    @scroll.window="scrolled = window.scrollY > 8">

<header
    :class="scrolled ? 'shadow-[0_12px_35px_-18px_rgba(0,0,0,0.9)] border-white/15' : 'shadow-none border-white/10'"
    class="ifix-navbar sticky top-0 z-50 w-full border-b border-white/10 bg-[#090a0b]/95 text-white backdrop-blur-xl transition-all duration-300">

    <div class="bg-[#171819] text-[10px] font-medium text-neutral-300 sm:text-[11px]">
        <div class="mx-auto flex min-h-7 max-w-[1440px] items-center justify-between gap-3 px-4 py-1 sm:px-6 lg:px-8">
            <p class="flex items-center gap-1.5 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm text-[#f5a623]" aria-hidden="true">local_shipping</span>
                <span>Envío gratis a todo México en compras desde ${{ number_format(config('shipping.free_shipping_from'), 0) }} MXN</span>
            </p>
            <div class="hidden items-center gap-4 sm:flex">
                <a href="https://wa.me/525541698230" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 transition hover:text-[#f5a623]">
                    <span class="material-symbols-outlined text-sm text-[#f5a623]" aria-hidden="true">chat</span>
                    WhatsApp Soporte: +52 (33) 2049 5306
                </a>
                <span class="text-white/25" aria-hidden="true">|</span>
                <span>Garantía de satisfacción 100%</span>
            </div>
        </div>
    </div>

    <div
        class="mx-auto flex min-h-[68px] max-w-[1440px] items-center gap-2 px-3 font-inter tracking-tight antialiased sm:gap-4 sm:px-6 lg:min-h-[78px] lg:gap-6 lg:px-8">

        {{-- Logo + botón menú móvil --}}

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">

            {{-- Botón menú móvil --}}

            <button
                type="button"
                @click="mobileMenuOpen = true"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-neutral-300 transition-colors duration-300 hover:bg-white/10 hover:text-[#f5a623] active:opacity-70 lg:hidden"
                aria-label="Abrir menú"
                :aria-expanded="mobileMenuOpen.toString()">

                <span class="material-symbols-outlined" aria-hidden="true">
                    menu
                </span>

            </button>


            {{-- Logo --}}

            <a
                href="{{ url('/') }}"
                class="group flex items-center transition-opacity duration-200 hover:opacity-80"
                aria-label="IFIX — Inicio">
                <img
                    src="{{ asset('images/ifix-logo.svg') }}"
                    alt="IFIX — Productos para detallado"
                    class="h-auto w-[102px] sm:w-[138px]">

            </a>

        </div>

        {{-- Búsqueda central --}}
        <div class="min-w-0 flex-1 px-1 lg:px-3">
            <x-search placeholder="Buscar pulimentos, cerámicos, microfibras..." class="ifix-search w-full rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-neutral-900 shadow-inner shadow-black/[0.03] transition focus-within:border-[#f5a623] sm:px-4" />
        </div>

        <div class="flex shrink-0 items-center gap-1 sm:gap-2 lg:gap-3">

            <button
                type="button"
                x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
                @theme-changed.window="darkMode = $event.detail.dark"
                @click="window.toggleTheme()"
                class="ifix-navbar-text flex h-9 w-9 items-center justify-center rounded-lg text-neutral-200 transition hover:bg-white/10 hover:text-[#f5a623]"
                :aria-label="darkMode ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                title="Cambiar tema">
                <span class="material-symbols-outlined text-[20px]" x-text="darkMode ? 'light_mode' : 'dark_mode'" aria-hidden="true"></span>
            </button>

            <a
                href="{{ auth()->check() ? route('profile.edit') : route('login') }}"
                class="ifix-navbar-text flex h-10 items-center gap-2 rounded-lg px-1.5 text-sm font-semibold leading-tight text-neutral-100 transition hover:text-[#f5a623] sm:px-2"
                aria-label="Mi cuenta">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_outline</span>
                <span class="hidden sm:flex sm:flex-col">
                    @auth
                        <span class="text-[9px] font-bold uppercase tracking-wider text-neutral-400">Bienvenido</span>
                        <span>Mi cuenta</span>
                    @else
                        <span>Mi cuenta</span>
                    @endauth
                </span>
            </a>

            <a
                href="{{ auth()->check() ? route('orders.index') : route('login') }}"
                class="ifix-navbar-text hidden h-10 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-neutral-100 transition hover:text-[#f5a623] xl:flex">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">receipt_long</span>
                Mis pedidos
            </a>

            <a
                href="{{ auth()->check() ? route('profile.edit') : route('login') }}"
                class="hidden h-9 w-9 items-center justify-center rounded-full bg-[#7c5800] text-amber-100 transition hover:bg-[#a87500] sm:flex"
                aria-label="Abrir mi cuenta">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">person</span>
            </a>

            {{-- ========================================================= --}}
            {{-- CARRITO --}}
            {{-- ========================================================= --}}

            <div
                x-data="cartWidget"
                data-cart-url="{{ route('cart.data') }}"
                x-init="init($el.dataset.cartUrl, null, null, @js($cart ?? null))"
                class="relative">

                {{-- Botón del carrito --}}

                <button
                    type="button"
                    @click="open = !open"
                    class="ifix-navbar-text relative flex h-10 items-center justify-center gap-2 rounded-full bg-white/10 px-3 text-neutral-100 transition-all duration-200 hover:bg-white/15 hover:text-[#f5a623] active:scale-95"
                    aria-label="Ver carrito"
                    :aria-expanded="open.toString()">

                    <span
                        class="material-symbols-outlined text-[20px] text-[#f5a623]"
                        aria-hidden="true">
                        shopping_cart
                    </span>

                    <span class="hidden text-xs font-bold tabular-nums sm:inline" x-text="total"></span>

                    {{-- Contador --}}

                    <span
                        x-show="quantity > 0"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-text="quantity"
                        class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#f5a623] px-1 text-[10px] font-extrabold leading-none text-neutral-950 ring-1 ring-[#090a0b]"></span>

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
                    class="absolute right-0 top-full z-[100] mt-3 w-[min(92vw,400px)] origin-top-right overflow-hidden rounded-2xl border border-white/10 bg-[#111315] text-white shadow-2xl shadow-black/50">

                    {{-- Header --}}

                    <div
                        class="flex items-center justify-between border-b border-white/10 bg-[#17191b] px-5 py-4">

                        <div>

                            <h3
                                class="font-bold tracking-tight text-white">
                                Tu carrito
                            </h3>

                            <p
                                class="mt-1 text-xs text-neutral-400"
                                x-text="
                                    quantity === 1
                                        ? '1 producto'
                                        : `${quantity} productos`
                                "></p>

                        </div>


                        <button
                            type="button"
                            @click="open = false"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-neutral-400 transition duration-200 hover:bg-white/10 hover:text-white"
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
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04]">

                                    <span
                                        class="material-symbols-outlined text-3xl text-neutral-500">
                                        shopping_cart
                                    </span>

                                </div>

                                <p
                                    class="mt-4 font-semibold text-white">
                                    Tu carrito está vacío
                                </p>

                                <p
                                    class="mt-1 text-sm text-neutral-400">
                                    Agrega productos para comenzar.
                                </p>

                                <a
                                    href="{{ url('/') }}"
                                    class="mt-5 inline-flex items-center justify-center rounded-lg bg-[#f5a623] px-5 py-2.5 text-sm font-bold text-neutral-950 transition hover:bg-amber-400">
                                    Explorar productos
                                </a>

                            </div>

                        </template>


                        {{-- Items --}}

                        <div
                            x-show="items.length > 0"
                            class="divide-y divide-white/10">

                            <template
                                x-for="item in items"
                                :key="item.lineId">

                                <div
                                    class="group flex gap-3 rounded-xl px-2 py-4 transition-colors duration-200 hover:bg-white/[0.04]">

                                    {{-- Imagen --}}

                                    <div
                                        class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-xl border border-white/10 bg-white p-1 transition-colors duration-200 group-hover:border-[#f5a623]/50">

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
                                                    class="material-symbols-outlined text-neutral-500">
                                                    image
                                                </span>

                                            </div>

                                        </template>

                                    </div>


                                    {{-- Información --}}

                                    <div class="min-w-0 flex-1">

                                        <p
                                            class="truncate text-sm font-semibold text-white"
                                            x-text="item.name"></p>


                                        <p
                                            x-show="item.sku"
                                            class="mt-0.5 truncate text-[10px] uppercase tracking-wide text-neutral-500"
                                            x-text="`SKU: ${item.sku}`"></p>


                                        <div
                                            class="mt-2.5 flex items-center justify-between">

                                            <span
                                                class="rounded-md bg-white/10 px-2 py-1 text-[10px] font-medium text-neutral-300"
                                                x-text="`Cant. ${item.quantity}`"></span>


                                            <span
                                                class="text-sm font-bold text-white tabular-nums"
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
                        class="border-t border-white/10 bg-[#17191b] px-5 py-4">

                        <div
                            x-show="items.length > 0"
                            class="mb-4 rounded-xl border border-white/10 bg-black/20 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3 text-[11px]">
                                <span
                                    class="font-semibold text-neutral-300"
                                    x-text="freeShipping ? '¡Ya tienes envío gratis!' : 'Envío gratis desde $499 MXN'"></span>
                                <span
                                    class="shrink-0 font-bold text-[#f5a623]"
                                    x-show="!freeShipping"
                                    x-text="`Faltan $${amountUntilFreeShipping.toFixed(2)}`"></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                                <div
                                    class="h-full rounded-full bg-[#f5a623] transition-[width] duration-500"
                                    :style="`width: ${freeShippingProgress}%`"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">

                            <span
                                class="text-sm text-neutral-400">
                                Subtotal
                            </span>

                            <span
                                class="font-semibold text-neutral-100 tabular-nums"
                                x-text="subtotal"></span>

                        </div>


                        <div
                            class="mt-2 flex items-center justify-between">

                            <span
                                class="text-sm text-neutral-400">
                                IVA
                            </span>

                            <span
                                class="text-sm text-neutral-300 tabular-nums"
                                x-text="tax"></span>

                        </div>


                        <div
                            class="mt-4 flex items-center justify-between border-t border-dashed border-white/15 pt-4">

                            <span
                                class="font-bold text-white">
                                Total
                            </span>

                            <span
                                class="text-xl font-black text-white tabular-nums"
                                x-text="total"></span>

                        </div>

                        <button
                            type="button"
                            @click="clearCart()"
                            :disabled="loading || items.length === 0"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-5 py-3 text-sm font-semibold text-neutral-300 transition duration-200 hover:border-red-400/30 hover:bg-red-500/10 hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-40">

                            <span class="material-symbols-outlined text-lg">
                                delete_sweep
                            </span>

                            Vaciar carrito

                        </button>


                        {{-- Ver carrito --}}

                        <a
                            href="{{ route('cart.index') }}"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-[#f5a623] px-5 py-3 font-extrabold text-neutral-950 transition duration-200 hover:bg-amber-400">
                            <span>Ver carrito</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <nav class="hidden border-t border-white/[0.06] bg-[#202122] lg:block" aria-label="Categorías de productos">
        <div class="mx-auto flex max-w-[1440px] items-center gap-7 overflow-x-auto px-8 py-2.5 text-[10px] font-extrabold uppercase tracking-wide text-white lg:gap-8">
            <a href="{{ route('catalog.index') }}" class="shrink-0 text-[#f5a623] transition hover:text-amber-300">Todos los productos</a>
            <a href="{{ route('catalog.index', ['search' => 'pulimento']) }}" class="shrink-0 transition hover:text-[#f5a623]">Pulimento y restauradores</a>
            <a href="{{ route('catalog.index', ['search' => 'limpieza']) }}" class="shrink-0 transition hover:text-[#f5a623]">Limpieza</a>
            <a href="{{ route('catalog.index', ['search' => 'cerámico']) }}" class="shrink-0 transition hover:text-[#f5a623]">Protección cerámica</a>
            <a href="{{ route('catalog.index', ['search' => 'accesorios']) }}" class="shrink-0 transition hover:text-[#f5a623]">Accesorios</a>
            <a href="{{ route('catalog.index', ['search' => 'kit']) }}" class="shrink-0 transition hover:text-[#f5a623]">Kits completos</a>
        </div>
    </nav>

    </header>

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
            class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>


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
            class="absolute inset-y-0 left-0 flex w-[85%] max-w-xs flex-col overflow-y-auto border-r border-white/10 bg-[#111315] px-5 py-6 text-white shadow-2xl">

            <div class="flex items-center justify-between">

                <img src="{{ asset('images/ifix-logo.svg') }}" alt="IFIX — Productos para detallado" class="h-auto w-[138px]">

                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-neutral-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Cerrar menú">

                    <span class="material-symbols-outlined text-lg">
                        close
                    </span>

                </button>

            </div>


            <div class="mt-4">
                <x-search class="border-white/15 bg-white/5 text-neutral-200 focus-within:border-[#f5a623]" />
            </div>


            <nav class="mt-6 flex flex-col gap-1" aria-label="Navegación móvil">

                @foreach ($navLinks as $link)

                <a
                    href="{{ $link['url'] }}"
                    class="{{ $link['active']

                            ? 'bg-[#f5a623]/10 text-[#f5a623] font-semibold'
                            : 'text-neutral-300 font-medium hover:bg-white/5 hover:text-white'
                        }} rounded-xl px-4 py-3 text-sm transition-colors duration-200"
                    @if ($link['active'])
                    aria-current="page"
                    @endif>

                    {{ $link['label'] }}

                </a>

                @endforeach

                    <a
                        href="{{ auth()->check() ? route('orders.index') : route('login') }}"
                        class="rounded-xl px-4 py-3 text-sm font-medium text-neutral-300 transition-colors hover:bg-white/5 hover:text-white">
                        Mis pedidos
                    </a>

                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="px-4 pb-2 text-[10px] font-bold uppercase tracking-widest text-neutral-500">Categorías</p>
                        <a href="{{ route('catalog.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-[#f5a623]">Todos los productos</a>
                        <a href="{{ route('catalog.index', ['search' => 'pulimento']) }}" class="block rounded-xl px-4 py-3 text-sm text-neutral-300 hover:bg-white/5">Pulimento y restauradores</a>
                        <a href="{{ route('catalog.index', ['search' => 'limpieza']) }}" class="block rounded-xl px-4 py-3 text-sm text-neutral-300 hover:bg-white/5">Limpieza</a>
                        <a href="{{ route('catalog.index', ['search' => 'cerámico']) }}" class="block rounded-xl px-4 py-3 text-sm text-neutral-300 hover:bg-white/5">Protección cerámica</a>
                        <a href="{{ route('catalog.index', ['search' => 'accesorios']) }}" class="block rounded-xl px-4 py-3 text-sm text-neutral-300 hover:bg-white/5">Accesorios</a>
                        <a href="{{ route('catalog.index', ['search' => 'kit']) }}" class="block rounded-xl px-4 py-3 text-sm text-neutral-300 hover:bg-white/5">Kits completos</a>
                    </div>

                </nav>

        </div>

    </div>

</div>