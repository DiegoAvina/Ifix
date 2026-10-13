@extends('layouts.app')

@section('title', 'Mis pedidos | Ifix')

@section('content')

<div class="min-h-screen bg-surface-container-low">

    <div class="mx-auto max-w-4xl px-6 py-10">

        <h1 class="text-2xl font-bold text-on-surface">Mis pedidos</h1>

        @if ($orders->isEmpty())

            <div class="mt-8 rounded-2xl border border-dashed border-outline-variant bg-white px-6 py-16 text-center">
                <span class="material-symbols-outlined text-6xl text-secondary/40">receipt_long</span>
                <p class="mt-4 font-semibold text-on-surface">Aún no tienes pedidos</p>
                <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary hover:brightness-110">
                    Ir a comprar
                </a>
            </div>

        @else

            <div class="mt-6 space-y-4">

                @foreach ($orders as $order)

                    <a
                        href="{{ route('checkout.confirmation', $order->id) }}"
                        class="flex items-center justify-between rounded-2xl border border-outline-variant/30 bg-white p-6 transition hover:border-primary/40">

                        <div>
                            <p class="font-semibold text-on-surface">Pedido #{{ $order->reference }}</p>
                            <p class="mt-1 text-sm text-secondary">
                                {{ $order->createdAt->translatedFormat('d \d\e F, Y') }} — {{ $order->statusLabel }}
                            </p>
                        </div>

                        <p class="text-lg font-bold text-primary">{{ $order->total }}</p>

                    </a>

                @endforeach

            </div>

        @endif

    </div>

</div>

@endsection
