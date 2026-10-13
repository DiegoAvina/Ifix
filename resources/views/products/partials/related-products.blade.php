@if(count($product->relatedProducts))

<div>

    <div class="mb-10">

        <span class="font-label-md text-label-md text-primary uppercase tracking-widest mb-3 block">
            También te puede interesar
        </span>

        <h2 class="font-headline-md text-headline-md text-on-surface">
            Productos relacionados
        </h2>

    </div>

    <div class="grid grid-cols-1 gap-gutter md:grid-cols-2 xl:grid-cols-4">

        @foreach($product->relatedProducts as $related)

            <x-product-card :product="$related" />

        @endforeach

    </div>

</div>

@endif
