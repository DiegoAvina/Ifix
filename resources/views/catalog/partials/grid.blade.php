@if(count($catalog->products->items))

    <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3">

        @foreach($catalog->products->items as $product)

            <x-product-card
                :product="$product"
            />

        @endforeach

    </div>

@else

    <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">

        <h3 class="text-lg font-semibold text-gray-900">
            No encontramos productos
        </h3>

        <p class="mt-2 text-gray-500">
            Intenta cambiar los filtros o realizar otra búsqueda.
        </p>

    </div>

@endif