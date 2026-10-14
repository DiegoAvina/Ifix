@if(count($catalog->products->items))
    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-3">

        @foreach($catalog->products->items as $product)

            <x-product-card
                :product="$product"
            />

        @endforeach

    </div>

@else
    <div class="rounded-2xl border border-dashed border-neutral-300 bg-white px-5 py-16 text-center">
        <span class="material-symbols-outlined text-4xl text-neutral-300" aria-hidden="true">search_off</span>
        <h3 class="mt-3 font-heading text-lg font-extrabold text-neutral-900">
            No encontramos productos
        </h3>
        <p class="mt-2 text-sm text-neutral-500">
            Intenta cambiar los filtros o realizar otra búsqueda.
        </p>
        <a href="{{ route('catalog.index') }}" class="mt-5 inline-flex rounded-full bg-neutral-950 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#bd7710]">
            Ver todos los productos
        </a>
    </div>
@endif