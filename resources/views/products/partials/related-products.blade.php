@if(count($product->relatedProducts))

<div>

    <div class="mb-10">

        <span class="mb-2 block text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#a76500]">
            También te puede interesar
        </span>

        <h2 class="font-heading text-2xl font-extrabold text-neutral-950">
            Productos relacionados
        </h2>

    </div>

    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">

        @foreach($product->relatedProducts as $related)

            <x-product-card :product="$related" />

        @endforeach

    </div>

</div>

@endif
