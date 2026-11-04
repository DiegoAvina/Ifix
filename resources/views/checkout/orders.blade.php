@extends('layouts.app')

@section('title', 'Mis pedidos | IFIX')
@section('meta_robots', 'noindex, nofollow')

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
            <div class="mt-8 space-y-6">
                @foreach ($orders as $order)
                    @php
                        $statusTone = match ($order->status) {
                            'payment-received', 'dispatched' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
                            'awaiting-payment', 'payment-offline', 'payment-review' => 'bg-amber-100 text-amber-900 dark:bg-amber-500/15 dark:text-amber-300',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
                            default => 'bg-neutral-100 text-neutral-700 dark:bg-white/10 dark:text-neutral-200',
                        };
                        $units = collect($order->lines)->sum('quantity');
                        $shipTo = $order->shippingAddress;
                    @endphp

                    <article class="rounded-2xl border border-neutral-200 bg-white dark:border-white/10 dark:bg-[#17191b]">
                        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-neutral-200 p-5 dark:border-white/10 sm:p-6">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-extrabold">Pedido #{{ $order->reference }}</h2>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusTone }}">{{ $order->statusLabel }}</span>
                                </div>
                                <p class="mt-1 text-sm text-neutral-500">
                                    {{ $order->createdAt->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
                                    <span class="mx-1" aria-hidden="true">·</span>
                                    {{ $units }} {{ $units === 1 ? 'artículo' : 'artículos' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Total</p>
                                <p class="mt-1 text-lg font-black">{{ $order->total }}</p>
                            </div>
                        </header>

                        <ul class="divide-y divide-neutral-200 px-5 dark:divide-white/10 sm:px-6" aria-label="Productos del pedido {{ $order->reference }}">
                            @foreach ($order->lines as $line)
                                <li class="flex items-center gap-4 py-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-white/10">
                                        @if ($line->image)
                                            <img src="{{ $line->image }}" alt="" class="h-full w-full object-contain p-1" loading="lazy">
                                        @else
                                            <span class="material-symbols-outlined text-neutral-400" aria-hidden="true">inventory_2</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        @if ($line->url)
                                            <a href="{{ $line->url }}" class="line-clamp-2 text-sm font-bold transition hover:text-[#bd7710]">{{ $line->description }}</a>
                                        @else
                                            <p class="line-clamp-2 text-sm font-bold">{{ $line->description }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-neutral-500">
                                            @if ($line->option){{ $line->option }} <span aria-hidden="true">·</span> @endif
                                            @if ($line->identifier)SKU {{ $line->identifier }} <span aria-hidden="true">·</span> @endif
                                            {{ $line->quantity }} × {{ $line->unitPrice }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-sm font-extrabold">{{ $line->total }}</p>
                                </li>
                            @endforeach
                        </ul>

                        <footer class="grid gap-4 border-t border-neutral-200 p-5 text-sm dark:border-white/10 sm:grid-cols-2 sm:p-6">
                            <div class="text-neutral-600 dark:text-neutral-400">
                                @if ($shipTo)
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Se envía a</p>
                                    <p class="mt-1 font-semibold text-neutral-900 dark:text-white">{{ trim($shipTo->firstName.' '.$shipTo->lastName) }}</p>
                                    <p>{{ $shipTo->city }}{{ $shipTo->state ? ', '.$shipTo->state : '' }}{{ $shipTo->postcode ? ' · C.P. '.$shipTo->postcode : '' }}</p>
                                @endif
                            </div>
                            <dl class="w-full space-y-1 sm:max-w-xs sm:justify-self-end">
                                <div class="flex justify-between gap-8"><dt class="text-neutral-500">Subtotal</dt><dd>{{ $order->subtotal }}</dd></div>
                                <div class="flex justify-between gap-8"><dt class="text-neutral-500">Envío</dt><dd>{{ $order->shipping }}</dd></div>
                                <div class="flex justify-between gap-8 font-extrabold"><dt>Total</dt><dd>{{ $order->total }}</dd></div>
                            </dl>
                        </footer>

                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-white/10 sm:px-6">
                            @if ($order->status === 'awaiting-payment')
                                <p class="mr-auto text-xs font-semibold text-amber-800 dark:text-amber-300">Este pedido aún no está pagado.</p>
                                <a href="{{ route('checkout.confirmation', $order->id) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#f5a623] px-4 py-2 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">payments</span>
                                    Completar pago
                                </a>
                            @else
                                <a href="{{ route('checkout.confirmation', $order->id) }}" class="inline-flex items-center gap-1 text-sm font-bold text-neutral-700 transition hover:text-[#bd7710] dark:text-neutral-200">
                                    Ver detalle del pedido
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
