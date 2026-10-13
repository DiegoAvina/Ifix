@props([
    'product',
])

@php
    $name = $product->name;

    $brand = $product->brand?->name;

    $price = $product->price->formatted;

    $image = $product->image?->url
        ?? asset('images/product-placeholder.png');

    $imageAlt = $product->image?->alt
        ?? $name;

    $inventory = $product->inventory;

    $stock = $inventory->stock;

    $available = $inventory->available;

    $sku = $inventory->sku;

    $detailUrl = $product->url;

    $addToCartUrl = $product->addToCartUrl;

    $variantId = $product->variantId;

    $minQuantity = $inventory->minQuantity;

    $quantityIncrement = $inventory->quantityIncrement;

    $badges = $product->badges;
@endphp

<div
    class="group relative flex flex-col overflow-hidden rounded-3xl border border-outline-variant/30 bg-white transition-all duration-300 hover:-translate-y-1.5 hover:border-primary/20 hover:shadow-2xl hover:shadow-primary/10">

    {{-- Imagen --}}
    <a
        href="{{ $detailUrl }}"
        class="relative block aspect-square overflow-hidden bg-gradient-to-b from-surface-container-low to-surface-container p-10">

        {{-- Badges --}}
        <div class="absolute top-4 left-4 z-10 flex flex-col gap-1.5">

            @foreach($badges as $badge)

                @php
                    $classes = match($badge->type) {

                        'danger' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',

                        'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',

                        'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',

                        'info' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',

                        default => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200',

                    };
                @endphp

                <span
                    class="w-fit rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-wide shadow-sm backdrop-blur-sm {{ $classes }}">

                    {{ $badge->label }}

                </span>

            @endforeach

        </div>

        <img
            src="{{ $image }}"
            alt="{{ $imageAlt }}"
            class="h-full w-full object-contain transition-transform duration-500 ease-out group-hover:scale-110">

    </a>

    {{-- Información --}}
    <div class="flex flex-1 flex-col p-6">

        @if($brand)
            <p class="font-label-md text-label-md text-primary/80">
                {{ $brand }}
            </p>
        @endif

        <a href="{{ $detailUrl }}" class="mt-2 block">

            <h3
                class="line-clamp-2 min-h-[56px] text-lg font-semibold text-on-surface transition-colors duration-300 group-hover:text-primary">

                {{ $name }}

            </h3>

        </a>

        @if($sku)
            <p class="mt-2 text-xs text-secondary/70">
                SKU: {{ $sku }}
            </p>
        @endif

        <div class="mt-5 flex items-baseline gap-2">

            <p class="text-2xl font-black tracking-tight text-on-surface tabular-nums">
                {{ $price }}
            </p>

        </div>

        <div class="mt-3">

            @if($available && $stock > 0)

                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $stock }} disponibles
                </span>

            @elseif($available)

                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Disponible bajo pedido
                </span>

            @else

                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                    Sin existencias
                </span>

            @endif

        </div>

        <div class="mt-auto flex gap-2.5 pt-6">

            <a
                href="{{ $detailUrl }}"
                class="flex-1 rounded-2xl border border-outline-variant py-3 text-center text-sm font-semibold text-on-surface transition-all duration-300 hover:border-primary hover:bg-primary hover:text-on-primary">

                Ver producto

            </a>

            @if($available)

                <button
                    type="button"
                    x-data="addToCartButton('{{ $addToCartUrl }}', {{ $variantId ?? 'null' }}, {{ $minQuantity }}, {{ $quantityIncrement }})"
                    @click="add()"
                    :disabled="loading || !variantId"
                    aria-label="Agregar {{ $name }} al carrito"
                    class="flex items-center justify-center rounded-2xl bg-primary px-5 text-on-primary shadow-lg shadow-primary/20 transition-all duration-300 hover:scale-105 hover:shadow-primary/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100">

                    <template x-if="!loading && !added">
                        <span class="material-symbols-outlined transition-transform duration-300 group-hover:rotate-6">
                            shopping_cart
                        </span>
                    </template>

                    <template x-if="loading">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-30" />
                            <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </template>

                    <template x-if="added">
                        <span class="material-symbols-outlined">
                            check_circle
                        </span>
                    </template>

                </button>

            @else

                <span
                    aria-disabled="true"
                    title="Sin existencias"
                    class="flex cursor-not-allowed items-center justify-center rounded-2xl bg-surface-container px-5 text-secondary/50">

                    <span class="material-symbols-outlined">
                        shopping_cart
                    </span>

                </span>

            @endif

        </div>

    </div>

</div>