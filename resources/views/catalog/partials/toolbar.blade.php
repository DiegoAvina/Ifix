@php
    $paginator = $catalog->products->paginator;
@endphp

<div class="flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-5">
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-neutral-800">
        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-[#f5a623] px-2 text-xs font-extrabold text-neutral-950">
            {{ $paginator->total() }}
        </span>
        <span class="font-semibold">productos encontrados</span>
        @if ($paginator->total())
            <span class="hidden text-neutral-300 sm:inline">|</span>
            <span class="text-xs text-neutral-500">
                Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
            </span>
        @endif
    </div>

    <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
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
        <div class="flex h-10 items-center rounded-full border border-neutral-200 bg-neutral-50 px-3 focus-within:border-[#d98b12] sm:w-56">
            <span class="material-symbols-outlined text-lg text-neutral-400" aria-hidden="true">search</span>
            <input id="catalog-search" type="search" name="search" value="{{ is_string(request('search')) ? request('search') : '' }}"
                placeholder="Buscar productos..."
                class="min-w-0 flex-1 border-0 bg-transparent px-2 text-xs text-neutral-900 placeholder:text-neutral-400 focus:ring-0">
        </div>

        <label class="sr-only" for="sort-select">Ordenar productos</label>
        <div class="relative">
            <select id="sort-select" name="sort" data-autosubmit
                class="h-10 w-full appearance-none rounded-full border-0 bg-neutral-100 py-2 pl-4 pr-9 text-xs font-semibold text-neutral-800 transition hover:bg-neutral-200 focus:ring-2 focus:ring-[#f5a623] sm:w-auto">
                @foreach ($catalog->sort as $option)
                    <option value="{{ $option->value }}" @selected($option->selected)>{{ $option->label }}</option>
                @endforeach
            </select>
            <span class="pointer-events-none material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-base text-neutral-500" aria-hidden="true">expand_more</span>
        </div>

        <button type="submit" class="h-10 rounded-full bg-neutral-950 px-4 text-xs font-bold text-white transition hover:bg-[#bd7710] sm:hidden">
            Buscar
        </button>

        <button type="button" @click="$dispatch('open-catalog-filters')"
            class="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-neutral-200 bg-white px-4 text-xs font-bold text-neutral-800 transition hover:border-[#f5a623] lg:hidden"
            aria-label="Abrir filtros del catálogo">
            <span class="material-symbols-outlined text-base" aria-hidden="true">tune</span>
            Filtros
        </button>
    </form>
</div>
