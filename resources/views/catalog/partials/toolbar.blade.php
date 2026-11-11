@php
    $paginator = $catalog->products->paginator;
    $currentSearch = is_string(request('search')) ? request('search') : '';
    // Filtros aplicados (sin contar búsqueda, orden ni página): se muestran en el botón de Filtros del celular.
    // En /categorias/{slug} la categoría la fija la ruta: no cuenta como filtro.
    $activeFilters = collect(request()->except(['search', 'sort', 'page', ...request()->attributes->get('catalog_fixed_query', [])]))
        ->flatten()
        ->filter(fn ($value) => filled($value))
        ->count();
@endphp

{{--
    En el celular la barra va sin caja y en una sola fila (Ordenar + Filtros)
    para que los productos aparezcan pronto; el buscador solo se muestra si
    hay una búsqueda activa (el navbar ya tiene uno). Desde sm, la caja completa.
--}}
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:rounded-2xl sm:border sm:border-neutral-200 sm:bg-white sm:p-4 sm:px-5 sm:shadow-sm">
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-neutral-800">
        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-[#f5a623] px-2 text-xs font-extrabold text-neutral-950 sm:h-7 sm:min-w-7">
            {{ $paginator->total() }}
        </span>
        <span class="font-semibold">{{ $paginator->total() === 1 ? 'producto' : 'productos' }}<span class="hidden sm:inline"> encontrados</span></span>
        @if ($paginator->total())
            <span class="hidden text-neutral-300 sm:inline">|</span>
            <span class="hidden text-xs text-neutral-500 sm:inline">
                Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
            </span>
        @endif
    </div>

    <form method="GET" action="{{ route('catalog.index') }}" class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
        @foreach (request()->except(['search', 'sort', 'page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <label class="sr-only" for="catalog-search">Buscar productos</label>
        <div @class([
            'col-span-2 h-10 items-center rounded-full border border-neutral-200 bg-white px-3 focus-within:border-[#d98b12] sm:flex sm:w-56 sm:bg-neutral-50',
            'flex' => $currentSearch !== '',
            'hidden' => $currentSearch === '',
        ])>
            <span class="material-symbols-outlined text-lg text-neutral-400" aria-hidden="true">search</span>
            <input id="catalog-search" type="search" name="search" value="{{ $currentSearch }}"
                placeholder="Buscar productos..." enterkeyhint="search"
                class="min-w-0 flex-1 border-0 bg-transparent px-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:ring-0 sm:text-xs">
        </div>

        <label class="sr-only" for="sort-select">Ordenar productos</label>
        <div class="relative">
            <span class="pointer-events-none material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-neutral-500 sm:hidden" aria-hidden="true">swap_vert</span>
            <select id="sort-select" name="sort" data-autosubmit
                class="h-10 w-full appearance-none truncate rounded-full border border-neutral-200 bg-white py-2 pl-9 pr-8 text-xs font-semibold text-neutral-800 transition hover:bg-neutral-50 focus:ring-2 focus:ring-[#f5a623] sm:w-auto sm:border-0 sm:bg-neutral-100 sm:pl-4 sm:pr-9 sm:hover:bg-neutral-200">
                @foreach ($catalog->sort as $option)
                    <option value="{{ $option->value }}" @selected($option->selected)>{{ $option->label }}</option>
                @endforeach
            </select>
            <span class="pointer-events-none material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-base text-neutral-500" aria-hidden="true">expand_more</span>
        </div>

        <button type="button" @click="$dispatch('open-catalog-filters')"
            class="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-neutral-200 bg-white px-4 text-xs font-bold text-neutral-800 transition hover:border-[#f5a623] lg:hidden"
            aria-label="Abrir filtros del catálogo{{ $activeFilters ? " ({$activeFilters} aplicados)" : '' }}">
            <span class="material-symbols-outlined text-base" aria-hidden="true">tune</span>
            Filtros
            @if ($activeFilters)
                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#f5a623] px-1.5 text-[10px] font-extrabold text-neutral-950">{{ $activeFilters }}</span>
            @endif
        </button>
    </form>
</div>
