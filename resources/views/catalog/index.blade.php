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
        <section class="hidden border-b border-neutral-800 bg-[#151617] text-neutral-300 sm:block">
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

        {{-- Encabezado discreto: ruta, título, intro corta y categorías como pestañas. --}}
        <section class="border-b border-neutral-200 bg-white dark:border-white/10">
            <div class="mx-auto max-w-[1340px] px-4 pt-4 sm:px-6 sm:pt-7 lg:px-8">
                <nav class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-[0.14em] text-neutral-500" aria-label="Ruta de navegación">
                    <a href="{{ route('home') }}" class="transition hover:text-[#bd7710] dark:hover:text-[#f5a623]">Inicio</a>
                    <span class="text-neutral-400 dark:text-neutral-600" aria-hidden="true">/</span>
                    @if ($category)
                        <a href="{{ route('catalog.index') }}" class="transition hover:text-[#bd7710] dark:hover:text-[#f5a623]">Productos</a>
                        <span class="text-neutral-400 dark:text-neutral-600" aria-hidden="true">/</span>
                        <span class="text-neutral-800 dark:text-neutral-300" aria-current="page">{{ $category['name'] }}</span>
                    @else
                        <span class="text-neutral-800 dark:text-neutral-300" aria-current="page">Todos los productos</span>
                    @endif
                </nav>

                <h1 class="mt-2 font-heading text-2xl font-extrabold tracking-tight text-neutral-950 dark:text-white sm:text-3xl">
                    {{ $catalogHeading }}
                </h1>
                <p class="mt-1.5 line-clamp-2 max-w-2xl text-sm leading-6 text-neutral-500 dark:text-neutral-400">
                    {{ $catalogIntro }}
                </p>

                @php
                    $selectedCollections = array_map('strval', (array) request('collection', []));
                    $onlyOneCategory = $category || count($selectedCollections) === 1;
                    $tab = 'relative shrink-0 border-b-2 py-3 text-sm font-semibold transition';
                    $tabOn = 'border-[#f5a623] text-neutral-950 dark:text-white';
                    $tabOff = 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-900 dark:text-neutral-400 dark:hover:border-white/20 dark:hover:text-white';
                @endphp
                {{-- Enlaces a las páginas de categoría (no a filtros): así Google las rastrea.
                     En celular se desliza de lado, sin barra de scroll y con borde difuminado. --}}
                <nav class="-mx-4 mt-4 flex gap-6 overflow-x-auto px-4 [mask-image:linear-gradient(to_right,#000_calc(100%-40px),transparent)] [scrollbar-width:none] sm:mx-0 sm:mt-5 sm:px-0 lg:[mask-image:none] [&::-webkit-scrollbar]:hidden" aria-label="Categorías">
                    <a href="{{ route('catalog.index', $category ? [] : request()->except('collection', 'page')) }}"
                        @if (! $selectedCollections) aria-current="page" @endif
                        class="{{ $tab }} {{ $selectedCollections ? $tabOff : $tabOn }}">
                        Todos
                    </a>
                    @foreach ($categories as $chip)
                        @php $chipSelected = $onlyOneCategory && in_array((string) $chip['id'], $selectedCollections, true); @endphp
                        <a href="{{ $chip['url'] }}"
                            @if ($chipSelected) aria-current="page" @endif
                            class="{{ $tab }} {{ $chipSelected ? $tabOn : $tabOff }}">
                            {{ $chip['name'] }}
                            <span class="ml-1 text-xs font-medium tabular-nums text-neutral-400 dark:text-neutral-500">{{ $chip['count'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>

        <section class="min-h-[60vh] bg-[#f5f5f3] pb-8 pt-4 sm:py-9">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                @include('catalog.partials.toolbar', ['catalog' => $catalog])

                <div class="mt-4 grid grid-cols-1 gap-6 sm:mt-5 lg:grid-cols-[250px_minmax(0,1fr)] lg:gap-8">
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
