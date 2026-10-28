@extends('layouts.app')

@php
    $productSummary = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description))),
        155,
    ) ?: "Compra {$product->name} en la tienda oficial IFIX. Envíos a todo México.";
@endphp

@section('title', $product->name . ' | IFIX')
@section('meta_description', $productSummary)
@section('canonical', $product->url)
@section('og_type', 'product')

@if ($product->image)
    @section('og_image', $product->image->url)
@endif

@push('head')
    <meta property="product:price:amount" content="{{ number_format($product->price->raw, 2, '.', '') }}">
    <meta property="product:price:currency" content="{{ $product->price->currency }}">

    <script type="application/ld+json" nonce="{{ $cspNonce }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Product',
                    'name' => $product->name,
                    'description' => $productSummary,
                    'sku' => $product->inventory->sku,
                    'image' => collect($product->gallery)->pluck('url')->filter()->values()->all(),
                    'brand' => $product->brand->name ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
                    'url' => $product->url,
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => $product->url,
                        'price' => number_format($product->price->raw, 2, '.', ''),
                        'priceCurrency' => $product->price->currency,
                        'availability' => $product->inventory->available
                            ? 'https://schema.org/InStock'
                            : 'https://schema.org/OutOfStock',
                        'itemCondition' => 'https://schema.org/NewCondition',
                        'seller' => ['@type' => 'Organization', 'name' => 'IFIX'],
                    ],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Catálogo', 'item' => route('catalog.index')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => $product->url],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <div class="min-h-screen bg-[#f5f5f3]">
        <section class="border-b border-neutral-800 bg-[#151617] text-neutral-300">
            <div class="mx-auto flex max-w-[1340px] flex-wrap items-center justify-center gap-x-6 gap-y-2 px-4 py-2.5 text-[10px] font-semibold sm:px-6 sm:text-xs lg:justify-between lg:px-8">
                <span class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">local_shipping</span>
                    Envíos a todo México
                </span>
                <span class="hidden items-center gap-2 sm:inline-flex">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">verified</span>
                    Productos originales IFIX
                </span>
                <span class="hidden items-center gap-2 md:inline-flex">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">lock</span>
                    Compra segura
                </span>
            </div>
        </section>

        <div class="mx-auto max-w-[1340px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-5 flex flex-wrap items-center gap-2 text-xs text-neutral-500" aria-label="Ruta de navegación">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 transition hover:text-[#bd7710]">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">home</span>
                    Inicio
                </a>
                <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                <a href="{{ route('catalog.index') }}" class="transition hover:text-[#bd7710]">Catálogo</a>
                @if ($product->brand->id)
                    <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                    <a href="{{ route('catalog.index', ['brand' => [$product->brand->id]]) }}" class="transition hover:text-[#bd7710]">
                        {{ $product->brand->name }}
                    </a>
                @endif
                <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                <span class="max-w-[55vw] truncate font-semibold text-neutral-900 sm:max-w-md">{{ $product->name }}</span>
            </nav>

            <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1.08fr)_minmax(380px,.92fr)] lg:gap-7">
                @include('products.partials.gallery')
                @include('products.partials.info')
            </div>

            <div class="mt-8 grid gap-5 lg:mt-10">
                @include('products.partials.description')

                @if (count($product->specifications))
                    @include('products.partials.specifications')
                @endif
            </div>

            @if (count($product->relatedProducts))
                <section class="mt-12 border-t border-neutral-200 pt-9 sm:mt-16 sm:pt-12">
                    @include('products.partials.related-products')
                </section>
            @endif
        </div>
    </div>
@endsection
