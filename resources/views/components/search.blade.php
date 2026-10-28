{{--
    Componente: Search Bar
    Uso: <x-search />

    Envía la búsqueda al catálogo de productos y, mientras se escribe,
    muestra debajo los productos relacionados (tolera errores de escritura).
--}}

@props([
    'placeholder' => 'Buscar productos...',
    'class' => '',
])

<form
    method="GET"
    action="{{ route('catalog.index') }}"
    role="search"
    x-data="searchBox(@js(route('search.suggestions')))"
    @click.outside="close()"
    @keydown.escape="close()"
    @submit="submit($event)"
    class="relative flex min-w-0 items-center gap-2 transition-colors {{ $class }}"
>
    <input
        type="search"
        name="search"
        value="{{ is_string(request('search')) ? request('search') : '' }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        maxlength="100"
        x-ref="input"
        x-model="query"
        @input.debounce.250ms="fetchResults()"
        @focus="if (results.length || searched) open = true"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        role="combobox"
        aria-autocomplete="list"
        :aria-expanded="open"
        class="min-w-0 flex-1 border-none bg-transparent text-sm font-normal text-current placeholder-neutral-400 focus:ring-0"
    />

    <span
        x-show="loading"
        x-cloak
        class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-neutral-300 border-t-[#bd7710]"
        aria-hidden="true"
    ></span>

    <button
        type="submit"
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:text-[#bd7710]"
        aria-label="Buscar productos"
    >
        <span class="material-symbols-outlined text-[19px]" aria-hidden="true">search</span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="absolute left-0 right-0 top-full z-[110] mt-2 overflow-hidden rounded-2xl border border-neutral-200 bg-white text-left text-neutral-900 shadow-2xl shadow-black/20 dark:border-white/10 dark:bg-[#111315] dark:text-white"
        role="listbox"
    >
        <template x-if="results.length">
            <ul class="max-h-[60vh] overflow-y-auto py-2">
                <template x-for="(product, index) in results" :key="product.id">
                    <li>
                        <a
                            :href="product.url"
                            role="option"
                            :aria-selected="index === active"
                            @mouseenter="active = index"
                            :class="index === active ? 'bg-neutral-100 dark:bg-white/10' : ''"
                            class="flex items-center gap-3 px-3 py-2 transition"
                        >
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-neutral-200 bg-white p-1 dark:border-white/10">
                                <template x-if="product.image">
                                    <img :src="product.image" :alt="product.name" class="h-full w-full object-contain" loading="lazy">
                                </template>
                                <template x-if="!product.image">
                                    <span class="material-symbols-outlined text-neutral-300" aria-hidden="true">image</span>
                                </template>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold" x-text="product.name"></span>
                                <span class="block truncate text-xs text-neutral-500 dark:text-neutral-400" x-text="product.sku"></span>
                            </span>

                            <span class="shrink-0 text-sm font-bold text-[#bd7710]" x-text="product.price"></span>
                        </a>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="!results.length && searched && !loading">
            <p class="px-4 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                No encontramos productos para "<span class="font-semibold" x-text="query"></span>".
            </p>
        </template>

        <a
            x-show="results.length"
            :href="catalogUrl()"
            class="block border-t border-neutral-200 px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wide text-[#bd7710] transition hover:bg-neutral-50 dark:border-white/10 dark:hover:bg-white/5"
        >
            Ver todos los resultados
        </a>
    </div>

</form>
