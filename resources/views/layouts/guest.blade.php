<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'IFIX') }}</title>
        <meta name="robots" content="noindex, follow">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-shell min-h-screen bg-[#111315] font-sans text-white antialiased transition-colors">
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" aria-label="IFIX, ir al inicio" class="transition-opacity hover:opacity-80">
                <img src="{{ asset('images/ifix-logo.svg') }}" alt="IFIX — Productos para detallado" class="h-auto w-[132px]">
            </a>

            <button
                type="button"
                x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
                @theme-changed.window="darkMode = $event.detail.dark"
                @click="window.toggleTheme()"
                class="flex h-10 items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 text-sm font-semibold text-neutral-200 transition hover:border-[#f5a623]/50 hover:text-[#f5a623]">
                <span class="material-symbols-outlined text-lg" x-text="darkMode ? 'light_mode' : 'dark_mode'" aria-hidden="true"></span>
                <span x-text="darkMode ? 'Modo claro' : 'Modo oscuro'"></span>
            </button>
        </header>

        <main class="mx-auto grid min-h-[calc(100vh-88px)] w-full max-w-7xl items-center gap-8 px-5 pb-10 pt-4 sm:px-8 lg:grid-cols-[1fr_0.9fr] lg:gap-16">
            <section class="auth-brand-panel relative hidden min-h-[560px] overflow-hidden rounded-3xl border border-white/10 bg-[#090a0b] p-10 lg:flex lg:flex-col lg:justify-between xl:p-14">
                <div class="pointer-events-none absolute -right-24 -top-20 h-80 w-80 rounded-full bg-[#f5a623]/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 -left-20 h-80 w-80 rounded-full bg-[#f5a623]/[0.07] blur-3xl"></div>

                <div class="relative">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#f5a623]/25 bg-[#f5a623]/[0.08] px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-[#f5a623]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#f5a623]"></span>
                        Detallado automotriz profesional
                    </span>
                    <h1 class="mt-8 max-w-lg text-4xl font-extrabold leading-tight tracking-tight text-white xl:text-5xl">
                        El cuidado de tu auto, <span class="text-[#f5a623]">a otro nivel.</span>
                    </h1>
                    <p class="mt-5 max-w-md text-base leading-7 text-neutral-400">
                        Accede a productos profesionales, administra tus pedidos y encuentra todo para mantener tu vehículo impecable.
                    </p>
                </div>

                <div class="relative grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <span class="material-symbols-outlined text-2xl text-[#f5a623]" aria-hidden="true">verified</span>
                        <p class="mt-3 text-sm font-bold text-white">Calidad profesional</p>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">Productos seleccionados para cuidar cada detalle.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <span class="material-symbols-outlined text-2xl text-[#f5a623]" aria-hidden="true">local_shipping</span>
                        <p class="mt-3 text-sm font-bold text-white">Envío gratis</p>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">En compras desde $499 MXN.</p>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-md">
                <div class="mb-6 text-center lg:text-left">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#bd7710]">Tu espacio IFIX</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900">Bienvenido</h2>
                    <p class="mt-2 text-sm text-gray-600">Inicia sesión o crea una cuenta para continuar.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl shadow-black/10 sm:p-8">
                    {{ $slot }}
                </div>
                <p class="mt-6 text-center text-xs text-gray-500">© {{ date('Y') }} IFIX · Productos para detallado</p>
            </section>
        </main>
    </body>
</html>
