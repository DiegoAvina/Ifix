@extends('layouts.app')

@section('title', 'Catálogo IFIX | Detallado automotriz')

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
                    <span class="font-semibold text-neutral-900">Todos los productos</span>
                </nav>

                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-amber-900">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#f5a623]"></span>
                            Catálogo profesional IFIX
                        </span>
                        <h1 class="mt-3 font-heading text-3xl font-extrabold tracking-tight text-neutral-950 sm:text-4xl">
                            Catálogo General de Productos
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-neutral-600 sm:text-base">
                            Soluciones profesionales de química automotriz: nanotecnología SiO₂, abrillantadores y pulimentos para acabados impecables.
                        </p>
                    </div>

                    @php
                        $collectionFilter = collect($catalog->filters)->firstWhere('key', 'collection');
                    @endphp
                    <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" aria-label="Filtrar por colección">
                        <a href="{{ route('catalog.index', request()->except('collection', 'page')) }}"
                            @class([
                                'shrink-0 rounded-full px-4 py-2 text-xs font-bold transition sm:text-sm',
                                'bg-neutral-950 text-white' => !request()->has('collection'),
                                'bg-neutral-100 text-neutral-800 hover:bg-neutral-200' => request()->has('collection'),
                            ])>
                            Todos ({{ $catalog->products->paginator->total() }})
                        </a>
                        @foreach ($collectionFilter?->options ?? [] as $option)
                            <a href="{{ $option->url }}"
                                @class([
                                    'shrink-0 rounded-full px-4 py-2 text-xs font-bold transition sm:text-sm',
                                    'bg-neutral-950 text-white' => $option->selected,
                                    'bg-neutral-100 text-neutral-800 hover:bg-neutral-200' => !$option->selected,
                                ])>
                                {{ $option->label }} ({{ $option->count }})
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
