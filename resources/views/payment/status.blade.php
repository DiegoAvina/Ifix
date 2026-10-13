@extends('layouts.app')

@section('title', 'Estado de tu pago | Ifix')

@section('content')

@php
    $isPaid = $order && $order->isPlaced;

    $icon = $isPaid ? 'check_circle' : ($tone === 'failure' ? 'error' : 'schedule');

    $iconClasses = $isPaid
        ? 'bg-emerald-50 text-emerald-600'
        : ($tone === 'failure' ? 'bg-error-container text-on-error-container' : 'bg-amber-50 text-amber-600');

    $title = $isPaid
        ? '¡Tu pago fue confirmado!'
        : ($tone === 'failure' ? 'No pudimos procesar tu pago' : 'Estamos verificando tu pago');

    $description = $isPaid
        ? 'Ya registramos tu pago y tu pedido está en proceso.'
        : ($tone === 'failure'
            ? 'Mercado Pago reportó un problema con este intento de pago. Puedes intentar de nuevo desde tu pedido.'
            : 'Esto puede tardar unos segundos mientras confirmamos tu pago con Mercado Pago (por ejemplo, si pagaste con OXXO o SPEI, puede tardar más). Refresca esta página en un momento.');
@endphp

<div class="min-h-screen bg-surface-container-low">

    <div class="mx-auto max-w-2xl px-6 py-16 text-center">

        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full {{ $iconClasses }}">
            <span class="material-symbols-outlined text-3xl">{{ $icon }}</span>
        </span>

        <h1 class="mt-6 text-2xl font-black text-on-surface">{{ $title }}</h1>

        <p class="mx-auto mt-3 max-w-md text-sm text-secondary">{{ $description }}</p>

        @if ($order)

            <div class="mt-8 rounded-2xl border border-outline-variant/30 bg-white p-6 text-left">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary">Pedido</span>
                    <span class="font-semibold text-on-surface">#{{ $order->reference }}</span>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-sm text-secondary">Estado</span>
                    <span class="font-semibold text-on-surface">{{ $order->statusLabel }}</span>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-dashed border-outline-variant/40 pt-3">
                    <span class="text-sm text-secondary">Total</span>
                    <span class="text-lg font-black text-primary">{{ $order->total }}</span>
                </div>

            </div>

            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">

                @if ($isPaid)

                    <a href="{{ route('checkout.confirmation', $order->id) }}"
                        class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary hover:brightness-110">
                        Ver confirmación del pedido
                    </a>

                @else

                    <button
                        type="button"
                        data-action="reload-page"
                        class="rounded-xl border border-outline-variant px-6 py-3 text-sm font-semibold text-on-surface hover:border-primary hover:text-primary">
                        Actualizar estado
                    </button>

                    <form method="POST" action="{{ route('payments.mercadopago.pay', $order->id) }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary hover:brightness-110">
                            Intentar pagar de nuevo
                        </button>
                    </form>

                @endif

            </div>

        @else

            <a href="{{ route('orders.index') }}"
                class="mt-8 inline-flex rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary hover:brightness-110">
                Ver mis pedidos
            </a>

        @endif

    </div>

</div>

@endsection
