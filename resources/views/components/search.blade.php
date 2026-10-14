{{-- 
    Componente: Search Bar
    Uso: <x-search />

    Envía la búsqueda al catálogo de productos.
--}}

@props([
    'placeholder' => 'Buscar productos...',
    'class' => '',
])

<form
    method="GET"
    action="{{ route('catalog.index') }}"
    class="flex min-w-0 items-center gap-2 transition-colors {{ $class }}"
>
    <input
        type="search"
        name="search"
        value="{{ request('search') }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        class="min-w-0 flex-1 border-none bg-transparent text-sm font-normal text-current placeholder-neutral-400 focus:ring-0"
    />

    <button
        type="submit"
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:text-[#bd7710]"
        aria-label="Buscar productos"
    >
        <span class="material-symbols-outlined text-[19px]" aria-hidden="true">search</span>
    </button>

</form>