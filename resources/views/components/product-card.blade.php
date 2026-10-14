@props(['product'])

@php
    $name = $product->name;
    $brand = $product->brand?->name;
    $image = $product->image?->url ?? asset('images/demo-detailing-product.svg');
    $imageAlt = $product->image?->alt ?? $name;
    $inventory = $product->inventory;
    $available = $inventory->available;
    $detailUrl = $product->url;
    $addToCartUrl = $product->addToCartUrl;
    $variantId = $product->variantId;
@endphp

<article class="group relative flex min-w-0 flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white transition hover:-translate-y-1 hover:border-neutral-300 hover:shadow-xl hover:shadow-neutral-900/10">
    <a href="{{ $detailUrl }}" class="relative flex aspect-square items-center justify-center overflow-hidden bg-[#f4f4f2] p-5 sm:p-7">
        @foreach ($product->badges as $badge)
            @if ($badge->label !== 'Disponible')
                <span class="absolute left-3 top-3 z-10 rounded-full bg-[#f5a623] px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wide text-neutral-950 sm:left-4 sm:top-4 sm:px-3 sm:text-[10px]">
                    {{ $badge->label }}
                </span>
                @break
            @endif
        @endforeach
        <img src="{{ $image }}" alt="{{ $imageAlt }}" loading="lazy"
            class="h-full w-full object-contain transition duration-500 group-hover:scale-105">
    </a>

    <div class="flex flex-1 flex-col p-3 sm:p-4">
        @if ($brand)
            <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#bd7710] sm:text-[11px]">{{ $brand }}</p>
        @endif

        <a href="{{ $detailUrl }}" class="mt-1.5">
            <h3 class="line-clamp-2 min-h-10 text-xs font-bold leading-snug text-neutral-900 transition group-hover:text-[#bd7710] sm:min-h-11 sm:text-sm">
                {{ $name }}
            </h3>
        </a>

        @if ($inventory->sku)
            <p class="mt-1.5 text-[9px] uppercase tracking-wide text-neutral-400 sm:text-[10px]">SKU: {{ $inventory->sku }}</p>
        @endif

        <p class="mt-3 text-lg font-extrabold tabular-nums text-neutral-950 sm:text-xl">{{ $product->price->formatted }}</p>

        <div class="mt-auto pt-4">
            @if ($available && $variantId)
                <button
                    type="button"
                    x-data="addToCartButton('{{ $addToCartUrl }}', {{ $variantId }}, {{ $inventory->minQuantity }}, {{ $inventory->quantityIncrement }})"
                    @click="add()"
                    :disabled="loading || !variantId"
                    aria-label="Agregar {{ $name }} al carrito"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#f5a623] px-3 py-2.5 text-xs font-extrabold text-neutral-950 transition hover:bg-amber-400 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-60 sm:py-3 sm:text-sm">
                    <template x-if="!loading && !added">
                        <span class="inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">add_shopping_cart</span>
                            Agregar
                        </span>
                    </template>
                    <template x-if="loading">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-30"/>
                            <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </template>
                    <template x-if="added">
                        <span class="inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                            Agregado
                        </span>
                    </template>
                </button>
            @else
                <span class="flex w-full items-center justify-center rounded-lg bg-neutral-100 px-3 py-2.5 text-xs font-semibold text-neutral-500 sm:py-3 sm:text-sm">
                    No disponible
                </span>
            @endif
        </div>
    </div>
</article>
