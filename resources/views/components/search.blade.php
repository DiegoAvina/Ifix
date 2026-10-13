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
    class="hidden sm:flex items-center border-b border-outline-variant px-2 py-1 focus-within:border-primary transition-colors {{ $class }}"
>

    <span
        class="material-symbols-outlined text-outline text-sm"
        data-icon="search"
    >
        search
    </span>

    <input
        type="search"
        name="search"
        value="{{ request('search') }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        class="bg-transparent border-none focus:ring-0 text-sm font-light w-32 md:w-48 placeholder-secondary/50"
    />

</form>

{{-- Mobile --}}
<button
    class="lg:hidden text-secondary hover:text-primary transition-colors"
    aria-label="Abrir búsqueda"
>
    <span
        class="material-symbols-outlined"
        data-icon="search"
    >
        search
    </span>
</button>