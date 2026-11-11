@extends('layouts.app')

@php
    $catalogPage = max(1, (int) request('page', 1));
    // Búsquedas, filtros y orden generan variantes del mismo listado.
    // En una página de categoría, la categoría la fija la ruta: no cuenta como filtro.
    $catalogIsFiltered = collect(request()->query())
        ->except($category ? ['page', 'collection'] : ['page'])
        ->filter(fn ($value) => filled($value))
        ->isNotEmpty();
    $pageSuffix = $catalogPage > 1 ? " | Página {$catalogPage}" : '';

    if ($category) {
        $catalogTitle = $category['title'].$pageSuffix.' | IFIX';
        $catalogDescription = $category['description'];
        $catalogCanonical = route('catalog.category', array_filter([$category['slug'], 'page' => $catalogPage > 1 ? $catalogPage : null]));
        $catalogHeading = $category['name'];
        $catalogIntro = $category['intro'];
    } else {
        $catalogTitle = 'Productos para detallado automotriz: ceras, pulimentos y más'.$pageSuffix.' | IFIX';
        $catalogDescription = 'Catálogo completo IFIX de productos para detallado automotriz: shampoo para auto, pulimentos de corte alto y medio, ceras de grafeno, recubrimientos cerámicos y restauradores de plásticos y llantas. Envíos a todo México.';
        $catalogCanonical = $catalogPage > 1 ? route('catalog.index', ['page' => $catalogPage]) : route('catalog.index');
        $catalogHeading = 'Productos para detallado automotriz';
        $catalogIntro = 'Química automotriz profesional para lavar, descontaminar, pulir, proteger y restaurar tu auto: shampoo, pulimentos, ceras, recubrimientos cerámicos y restauradores IFIX.';
    }

    $breadcrumbs = array_values(array_filter([
        ['name' => 'Inicio', 'item' => route('home')],
        ['name' => 'Productos', 'item' => route('catalog.index')],
        $category ? ['name' => $category['name'], 'item' => route('catalog.category', $category['slug'])] : null,
    ]));
@endphp

@section('title', $catalogTitle)
@section('meta_description', $catalogDescription)
@section('canonical', $catalogCanonical)

@if ($catalogIsFiltered)
    @section('meta_robots', 'noindex, follow')
@endif

@push('head')
    <script type="application/ld+json" nonce="{{ $cspNonce }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => collect($breadcrumbs)->map(fn ($crumb, $i) => ['@type' => 'ListItem', 'position' => $i + 1] + $crumb)->all(),
                ],
                [
                    '@type' => 'CollectionPage',
                    'name' => $catalogHeading,
                    'description' => $catalogDescription,
                    'url' => $catalogCanonical,
                    'inLanguage' => 'es-MX',
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'numberOfItems' => $catalog->products->paginator->total(),
                        'itemListElement' => collect($catalog->products->items)->values()->map(fn ($product, $i) => [
                            '@type' => 'ListItem',
                            'position' => ($catalog->products->paginator->firstItem() ?? 1) + $i,
                            'url' => $product->url,
                            'name' => $product->name,
                        ])->all(),
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <div x-data="{ filtersOpen: false }" @open-catalog-filters.window="filtersOpen = true">
        <section class="border-b border-neutral-800 bg-[#151617] text-neutral-300">
            <div class="mx-auto flex max-w-[1340px] flex-wrap items-center justify-center gap-x-5 gap-y-2 px-4 py-2.5 text-[10px] font-semibold sm:px-6 sm:text-xs lg:justify-between lg:px-8">
                <span class="inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">verified</span>
                    Productos 100% originales IFIX
                </span>
                <span class="hidden items-center gap-2 sm:inline-flex">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">local_shipping</span>
                    Envíos a todo México
                </span>
                <span class="hidden items-center gap-2 md:inline-flex">
                    <span class="material-symbols-outlined text-base text-[#f5a623]" aria-hidden="true">lock</span>
                    Pago seguro
                </span>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-[1340px] px-4 pb-7 pt-6 sm:px-6 sm:pb-9 lg:px-8">
                <nav class="mb-5 flex items-center gap-2 text-xs text-neutral-500" aria-label="Ruta de navegación">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1 transition hover:text-[#bd7710]">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">home</span>
                        Inicio
                    </a>
                    <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                    @if ($category)
                        <a href="{{ route('catalog.index') }}" class="transition hover:text-[#bd7710]">Productos</a>
                        <span class="material-symbols-outlined text-sm text-neutral-400" aria-hidden="true">chevron_right</span>
                        <span class="font-semibold text-neutral-900" aria-current="page">{{ $category['name'] }}</span>
                    @else
                        <span class="font-semibold text-neutral-900" aria-current="page">Todos los productos</span>
                    @endif
                </nav>

                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-amber-900">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#f5a623]"></span>
                            Catálogo profesional IFIX
                        </span>
                        <h1 class="mt-3 font-heading text-3xl font-extrabold tracking-tight text-neutral-950 sm:text-4xl">
                            {{ $catalogHeading }}
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-600 sm:text-base">
                            {{ $catalogIntro }}
                        </p>
                    </div>

                    @php
                        $selectedCollections = array_map('strval', (array) request('collection', []));
                        $onlyOneCategory = $category || count($selectedCollections) === 1;
                    @endphp
                    {{-- Enlaces a las páginas de categoría (no a filtros): así Google las rastrea. --}}
                    <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Categorías">
                        <a href="{{ route('catalog.index', $category ? [] : request()->except('collection', 'page')) }}"
                            @class([
                                'shrink-0 rounded-full px-4 py-2 text-xs font-bold transition sm:text-sm',
                                'bg-neutral-950 text-white' => ! $selectedCollections,
                                'bg-neutral-100 text-neutral-800 hover:bg-neutral-200' => $selectedCollections,
                            ])>
                            Todos
                        </a>
                        @foreach ($categories as $chip)
                            @php $chipSelected = $onlyOneCategory && in_array((string) $chip['id'], $selectedCollections, true); @endphp
                            <a href="{{ $chip['url'] }}"
                                @if ($chipSelected) aria-current="page" @endif
                                @class([
                                    'shrink-0 rounded-full px-4 py-2 text-xs font-bold transition sm:text-sm',
                                    'bg-neutral-950 text-white' => $chipSelected,
                                    'bg-neutral-100 text-neutral-800 hover:bg-neutral-200' => ! $chipSelected,
                                ])>
                                {{ $chip['name'] }} ({{ $chip['count'] }})
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>

        <section class="min-h-[60vh] bg-[#f5f5f3] py-6 sm:py-9">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                @include('catalog.partials.toolbar', ['catalog' => $catalog])

                <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-[250px_minmax(0,1fr)] lg:gap-8">
                    <div class="hidden lg:block">
                        <x-catalog.sidebar :filters="$catalog->filters" />
                    </div>

                    <section aria-label="Productos del catálogo">
                        @include('catalog.partials.grid', ['catalog' => $catalog])
                        @include('catalog.partials.pagination', ['paginator' => $catalog->products->paginator])
                    </section>
                </div>
            </div>
        </section>

        <div
            x-cloak
            x-show="filtersOpen"
            x-transition.opacity
            @keydown.escape.window="filtersOpen = false"
            class="fixed inset-0 z-[120] lg:hidden"
            role="dialog"
            aria-modal="true"
            aria-label="Filtros del catálogo">
            <button type="button" class="absolute inset-0 h-full w-full bg-black/50" @click="filtersOpen = false" aria-label="Cerrar filtros"></button>
            <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                class="absolute inset-y-0 left-0 w-[min(88vw,360px)] overflow-y-auto bg-[#f5f5f3] p-4 shadow-2xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-heading text-lg font-extrabold text-neutral-950">Filtros</h2>
                    <button type="button" @click="filtersOpen = false" class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-neutral-700" aria-label="Cerrar filtros">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </div>
                <x-catalog.sidebar :filters="$catalog->filters" />
            </div>
        </div>
    </div>
@endsection
