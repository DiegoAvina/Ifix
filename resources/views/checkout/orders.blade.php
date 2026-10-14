@extends('layouts.app')

@section('title', 'Mis pedidos | IFIX')

@section('content')
<div class="min-h-screen bg-[#f5f5f3] py-8 text-neutral-900 dark:bg-[#111315] dark:text-white sm:py-12">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-neutral-500 transition hover:text-[#bd7710]">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Mi cuenta
        </a>

        <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#bd7710]">Tu historial IFIX</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Mis pedidos</h1>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Consulta el estado y el resumen de tus compras.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">add_shopping_cart</span>
                Seguir comprando
            </a>
        </div>

        @if ($orders->isEmpty())
            <section class="mt-8 rounded-2xl border border-neutral-200 bg-white px-6 py-16 text-center dark:border-white/10 dark:bg-[#17191b]">
                <span class="material-symbols-outlined text-6xl text-neutral-400" aria-hidden="true">receipt_long</span>
                <h2 class="mt-4 text-xl font-extrabold">Aún no tienes pedidos</h2>
                <p class="mt-2 text-sm text-neutral-500">Cuando realices una compra, aquí podrás consultar sus detalles.</p>
                <a href="{{ route('catalog.index') }}" class="mt-6 inline-flex rounded-xl bg-[#f5a623] px-6 py-3 text-sm font-extrabold text-neutral-950 hover:bg-amber-400">Explorar productos</a>
            </section>
        @else
            <div class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <a href="{{ route('checkout.confirmation', $order->id) }}" class="group block rounded-2xl border border-neutral-200 bg-white p-5 transition hover:border-[#f5a623] hover:shadow-lg dark:border-white/10 dark:bg-[#17191b] sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#f5a623]/15 text-[#bd7710] dark:text-[#f5a623]">
                                    <span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-extrabold">Pedido #{{ $order->reference }}</p>
                                    <p class="mt-1 text-sm text-neutral-500">{{ $order->createdAt->translatedFormat('d \d\e F, Y') }}</p>
                                    <span class="mt-3 inline-flex rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold text-neutral-700 dark:bg-white/10 dark:text-neutral-200">{{ $order->statusLabel }}</span>
                                </div>
                            </div>
                            <div class="ml-16 flex items-center gap-3 sm:ml-0">
                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Total</p>
                                    <p class="mt-1 text-lg font-black">{{ $order->total }}</p>
                                </div>
                                <span class="material-symbols-outlined text-neutral-400 transition group-hover:translate-x-1 group-hover:text-[#bd7710]" aria-hidden="true">arrow_forward</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-neutral-200 pt-4 dark:border-white/10">
                            <p class="text-xs font-semibold text-neutral-500">
                                {{ count($order->lines) }} {{ count($order->lines) === 1 ? 'producto' : 'productos' }}
                                @if ($order->lines)
                                    <span class="mx-1">·</span>
                                    {{ collect($order->lines)->take(2)->pluck('description')->join(', ') }}
                                    @if (count($order->lines) > 2)
                                        y {{ count($order->lines) - 2 }} más
                                    @endif
                                @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
