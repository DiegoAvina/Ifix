@extends('layouts.app')

@section('title', 'Pedido confirmado | Ifix')

@section('content')

<div class="min-h-screen bg-surface-container-low">

    <div class="mx-auto max-w-4xl px-6 py-14">

        @if (session('error'))
            <div class="mb-8 rounded-2xl border border-error/20 bg-error-container px-5 py-4 text-sm font-medium text-on-error-container">
                {{ session('error') }}
            </div>
        @endif

        <div class="text-center">

            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                <span class="material-symbols-outlined text-3xl text-emerald-600">check_circle</span>
            </span>

            <h1 class="mt-6 text-3xl font-black text-on-surface">¡Gracias por tu pedido!</h1>

            <p class="mt-2 text-secondary">
                Pedido <span class="font-semibold text-on-surface">#{{ $order->reference }}</span>
                — {{ $order->createdAt->translatedFormat('d \d\e F, Y') }}
            </p>

            <span class="mt-4 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold {{ $order->isPlaced ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                <span class="h-2 w-2 rounded-full {{ $order->isPlaced ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                {{ $order->statusLabel }}
            </span>

            @if ($order->isPlaced)

                <p class="mx-auto mt-3 max-w-md text-sm text-secondary">
                    Tu pago ya fue confirmado.
                </p>

            @else

                <p class="mx-auto mt-3 max-w-md text-sm text-secondary">
                    Tu pedido quedó registrado. Completa el pago para que empecemos a prepararlo.
                </p>

                <form method="POST" action="{{ route('payments.mercadopago.pay', $order->id) }}" class="mt-5">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 font-semibold text-on-primary transition hover:brightness-110">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Pagar con Mercado Pago
                    </button>
                </form>

            @endif

        </div>


        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">

            <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
                <h2 class="font-semibold text-on-surface">Dirección de envío</h2>

                @if ($order->shippingAddress)
                    <p class="mt-2 text-sm text-secondary">
                        {{ $order->shippingAddress->firstName }} {{ $order->shippingAddress->lastName }}<br>
                        {{ $order->shippingAddress->lineOne }}
                        @if ($order->shippingAddress->lineTwo), {{ $order->shippingAddress->lineTwo }}@endif<br>
                        {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }}, {{ $order->shippingAddress->postcode }}<br>
                        {{ $order->shippingAddress->countryName }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-secondary">No aplica (sin envío físico).</p>
                @endif
            </div>

            <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
                <h2 class="font-semibold text-on-surface">Dirección de facturación</h2>

                @if ($order->billingAddress)
                    <p class="mt-2 text-sm text-secondary">
                        {{ $order->billingAddress->firstName }} {{ $order->billingAddress->lastName }}<br>
                        {{ $order->billingAddress->lineOne }}
                        @if ($order->billingAddress->lineTwo), {{ $order->billingAddress->lineTwo }}@endif<br>
                        {{ $order->billingAddress->city }}, {{ $order->billingAddress->state }}, {{ $order->billingAddress->postcode }}<br>
                        {{ $order->billingAddress->countryName }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-secondary">Sin definir.</p>
                @endif
            </div>

        </div>


        <div class="mt-6 rounded-2xl border border-outline-variant/30 bg-white p-6">

            <h2 class="font-semibold text-on-surface">Productos</h2>

            <div class="mt-4 divide-y divide-outline-variant/30">

                @foreach ($order->lines as $line)

                    <div class="flex items-center justify-between py-4">

                        <div>
                            <p class="font-medium text-on-surface">{{ $line->description }}</p>
                            <p class="mt-1 text-sm text-secondary">
                                {{ $line->quantity }} × {{ $line->unitPrice }}
                            </p>
                        </div>

                        <p class="font-semibold text-on-surface">{{ $line->total }}</p>

                    </div>

                @endforeach

            </div>


            <div class="mt-4 border-t border-outline-variant/30 pt-4">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary">Subtotal</span>
                    <span class="font-medium text-on-surface">{{ $order->subtotal }}</span>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm text-secondary">IVA</span>
                    <span class="text-sm text-on-surface">{{ $order->tax }}</span>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm text-secondary">Envío</span>
                    <span class="text-sm text-on-surface">{{ $order->shipping }}</span>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-dashed border-outline-variant/40 pt-4">
                    <span class="font-bold text-on-surface">Total</span>
                    <span class="text-xl font-black text-primary">{{ $order->total }}</span>
                </div>

            </div>

        </div>


        <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">

            <a href="{{ route('orders.index') }}"
                class="rounded-xl border border-outline-variant px-6 py-3 text-sm font-semibold text-on-surface hover:border-primary hover:text-primary">
                Ver mis pedidos
            </a>

            <a href="{{ route('home') }}"
                class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary hover:brightness-110">
                Seguir comprando
            </a>

        </div>

    </div>

</div>

@endsection
