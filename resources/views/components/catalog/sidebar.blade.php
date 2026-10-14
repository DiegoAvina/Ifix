<aside class="catalog-filter-sidebar w-full space-y-3 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
        <h2 class="font-heading text-base font-extrabold text-neutral-950">Filtros</h2>
        @if (request()->query())
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-[#a76500] transition hover:text-neutral-950">Limpiar</a>
        @endif
    </div>

    @foreach ($filters as $filter)
        <section class="border-b border-neutral-100 py-3 last:border-0 last:pb-0">
            <h3 class="mb-3 text-[11px] font-extrabold uppercase tracking-[0.14em] text-neutral-800">
                {{ $filter->title === 'Colecciones' ? 'Categorías' : ($filter->title === 'Precio' ? 'Rango de precio' : $filter->title) }}
            </h3>

            @if ($filter->key === 'price')
                <form method="GET" action="{{ route('catalog.index') }}" class="space-y-3">
                    @foreach (request()->except(['min_price', 'max_price', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="grid grid-cols-2 gap-2">
                        <label class="text-[10px] font-medium text-neutral-500">
                            Mínimo
                            <span class="mt-1 flex items-center rounded-lg border border-neutral-200 px-2">
                                <span class="text-xs text-neutral-400">$</span>
                                <input type="number" name="min_price" step="0.01" min="0" max="{{ $filter->extra->max }}"
                                    value="{{ request('min_price', $filter->extra->min) }}"
                                    class="h-9 w-full border-0 bg-transparent px-1 text-xs text-neutral-900 focus:ring-0">
                            </span>
                        </label>
                        <label class="text-[10px] font-medium text-neutral-500">
                            Máximo
                            <span class="mt-1 flex items-center rounded-lg border border-neutral-200 px-2">
                                <span class="text-xs text-neutral-400">$</span>
                                <input type="number" name="max_price" step="0.01" min="{{ $filter->extra->min }}" max="{{ $filter->extra->max }}"
                                    value="{{ request('max_price', $filter->extra->max) }}"
                                    class="h-9 w-full border-0 bg-transparent px-1 text-xs text-neutral-900 focus:ring-0">
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-neutral-950 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#bd7710]">
                        Aplicar precio
                    </button>
                </form>
            @else
                <div class="space-y-1">
                    @foreach ($filter->options as $option)
                        <a href="{{ $option->url }}"
                            @class([
                                'catalog-filter-option group flex items-center justify-between gap-3 rounded-lg px-2.5 py-2 text-xs transition',
                                'bg-amber-50 text-neutral-950' => $option->selected,
                                'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-950' => !$option->selected,
                                'is-selected' => $option->selected,
                            ])>
                            <span class="flex min-w-0 items-center gap-2.5">
                                <span @class([
                                    'catalog-filter-checkbox flex h-4 w-4 shrink-0 items-center justify-center rounded border transition',
                                    'border-[#d98b12] bg-[#f5a623] text-neutral-950' => $option->selected,
                                    'border-neutral-300 bg-white group-hover:border-[#d98b12]' => !$option->selected,
                                ]) aria-hidden="true">
                                    @if ($option->selected)
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                    @endif
                                </span>
                                <span class="truncate">{{ $option->label }}</span>
                            </span>
                            @if ($option->count > 0)
                                <span class="shrink-0 text-[10px] text-neutral-400">{{ $option->count }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach

    <div class="rounded-xl bg-neutral-950 p-4 text-white">
        <div class="flex items-center gap-2 text-[#f5a623]">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">support_agent</span>
            <p class="text-xs font-extrabold">¿Dudas de compatibilidad?</p>
        </div>
        <p class="mt-2 text-[11px] leading-5 text-neutral-300">Te ayudamos a elegir los productos adecuados para tu auto.</p>
        <a href="{{ route('catalog.index') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] font-bold text-[#f5a623] hover:text-amber-300">
            Explorar productos
            <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
        </a>
    </div>
</aside>
