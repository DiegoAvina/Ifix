@extends('layouts.app')

@section('title', 'Mi cuenta | IFIX')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="min-h-screen bg-[#f5f5f3] py-8 text-neutral-900 dark:bg-[#111315] dark:text-white sm:py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#bd7710]">Tu espacio IFIX</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Hola, {{ $user->name }}</h1>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Administra tu cuenta, revisa pedidos y mantén tus datos al día.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400 sm:self-auto">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">shopping_bag</span>
                Explorar productos
            </a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('orders.index') }}" class="group rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-[#f5a623] hover:shadow-lg dark:border-white/10 dark:bg-[#17191b] sm:p-6">
                <div class="flex items-center justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f5a623]/15 text-[#bd7710] dark:text-[#f5a623]">
                        <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>
                    </span>
                    <span class="material-symbols-outlined text-neutral-400 transition group-hover:translate-x-1 group-hover:text-[#bd7710]" aria-hidden="true">arrow_forward</span>
                </div>
                <p class="mt-4 text-2xl font-black">{{ $ordersCount }}</p>
                <p class="mt-1 text-sm font-semibold text-neutral-600 dark:text-neutral-400">Mis pedidos</p>
            </a>

            <div class="rounded-2xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-[#17191b] sm:p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                </span>
                <p class="mt-4 text-lg font-extrabold">{{ $user->hasVerifiedEmail() ? 'Cuenta verificada' : 'Verifica tu correo' }}</p>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $user->email }}</p>
            </div>
        </div>

        <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-[#17191b] sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="rounded-2xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-[#17191b] sm:p-8">
                    @include('profile.partials.update-password-form')
                </section>

                <section class="rounded-2xl border border-red-200 bg-white p-5 dark:border-red-900/50 dark:bg-[#17191b] sm:p-8">
                    @include('profile.partials.delete-user-form')
                </section>
            </div>

            <aside class="rounded-2xl border border-neutral-200 bg-white p-5 dark:border-white/10 dark:bg-[#17191b] sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold">Pedidos recientes</h2>
                        <p class="mt-1 text-xs text-neutral-500">Tu actividad más reciente</p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[#bd7710] hover:text-[#f5a623]">Ver todos</a>
                </div>

                @if ($orders->isEmpty())
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-4xl text-neutral-400" aria-hidden="true">inventory_2</span>
                        <p class="mt-3 text-sm font-semibold">Todavía no tienes pedidos</p>
                        <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex rounded-lg bg-[#f5a623] px-4 py-2 text-xs font-extrabold text-neutral-950 hover:bg-amber-400">Ir a la tienda</a>
                    </div>
                @else
                    <div class="mt-5 divide-y divide-neutral-200 dark:divide-white/10">
                        @foreach ($orders as $order)
                            <a href="{{ route('checkout.confirmation', $order->id) }}" class="block py-4 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold">#{{ $order->reference }}</p>
                                        <p class="mt-1 text-xs text-neutral-500">{{ $order->createdAt->translatedFormat('d M Y') }}</p>
                                    </div>
                                    <span class="text-xs font-extrabold">{{ $order->total }}</span>
                                </div>
                                <p class="mt-2 text-xs text-neutral-500">{{ $order->statusLabel }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </div>
</div>
@endsection
