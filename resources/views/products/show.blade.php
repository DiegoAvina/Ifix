@extends('layouts.app')

@php
    $category ??= null;
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
                    'brand' => ['@type' => 'Brand', 'name' => $product->brand->name ?: config('seo.name')],
                    'category' => $category ? 'Detallado automotriz > '.$category['name'] : 'Detallado automotriz',
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
                        'seller' => ['@id' => url('/').'#organization'],
                        // Igual a lo que promete products.partials.shipping ("Devoluciones fáciles durante 30 días").
                        'hasMerchantReturnPolicy' => [
                            '@type' => 'MerchantReturnPolicy',
                            'applicableCountry' => 'MX',
                            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                            'merchantReturnDays' => 30,
                            'returnMethod' => 'https://schema.org/ReturnByMail',
                        ],
                    ],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ...collect(array_filter([
                            ['name' => 'Inicio', 'item' => route('home')],
                            ['name' => 'Productos', 'item' => route('catalog.index')],
                            $category ? ['name' => $category['name'], 'item' => $category['url']] : null,
                            ['name' => $product->name, 'item' => $product->url],
                        ]))->values()->map(fn ($crumb, $i) => ['@type' => 'ListItem', 'position' => $i + 1] + $crumb)->all(),
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    {{-- pb extra en móvil: deja sitio a la barra de compra fija de products.partials.info --}}
    <div @class([
        'min-h-screen bg-[#f5f5f3]',
        'pb-24 lg:pb-0' => $product->variantId && $product->inventory->available,
    ])>
        <section class="border-b border-neutral-800 bg-[#151617] text-neutral-300">
            <div class="mx-auto flex max-w-[1340px] flex-wrap items-center justify-center gap-x-6 gap-y-2 px-4 py-2 text-[11px] font-semibold sm:px-6 sm:text-xs lg:justify-between lg:px-8">
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

        <div class="mx-auto max-w-[1340px] px-4 pb-6 pt-2 sm:px-6 sm:py-8 lg:px-8">
            {{-- Móvil: un solo enlace "volver" con área táctil de 44px; la ruta completa ocupa espacio y sus enlaces son muy pequeños para el dedo. --}}
            @php
                $back = $category
                    ? ['url' => $category['url'], 'label' => $category['name']]
                    : ['url' => route('catalog.index'), 'label' => 'Productos'];
            @endphp
            <a href="{{ $back['url'] }}" class="-ml-2 mb-1 inline-flex min-h-11 max-w-full items-center gap-1 rounded-lg px-2 text-sm font-semibold text-neutral-600 active:bg-neutral-200/60 sm:hidden">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">arrow_back</span>
                <span class="truncate">{{ $back['label'] }}</span>
            </a>

            <nav class="mb-5 hidden flex-wrap items-center gap-2 text-xs text-neutral-500 sm:flex" aria-label="Ruta de navegación">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 transition hover:text-[#bd7710]">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">home</span>
                    Inicio
                </a>
                <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                <a href="{{ route('catalog.index') }}" class="transition hover:text-[#bd7710]">Productos</a>
                @if ($category)
                    <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                    <a href="{{ $category['url'] }}" class="transition hover:text-[#bd7710]">{{ $category['name'] }}</a>
                @elseif ($product->brand->id)
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

            <div class="mt-5 grid gap-4 sm:mt-8 sm:gap-5 lg:mt-10">
                @include('products.partials.description')

                @if (count($product->howTo))
                    @include('products.partials.how-to')
                @endif

                @if ($process ?? null)
                    @include('products.partials.process')
                @endif

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
