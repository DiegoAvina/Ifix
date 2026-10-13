@props(['collection', 'index' => 0])

<section class="{{ $index % 2 === 1 ? 'bg-surface-container-low' : 'bg-white' }} py-section-gap">
    <div class="max-w-screen-2xl mx-auto px-8">

        <div class="flex flex-col gap-6 mb-16 sm:flex-row sm:items-end sm:justify-between">

            <div>
                <span class="font-label-md text-label-md text-primary uppercase tracking-widest mb-3 block">
                    Colección
                </span>

                <h2 class="font-headline-md text-headline-md text-on-surface">
                    {{ $collection['name'] }}
                </h2>

                <p class="font-body-md text-body-md text-secondary mt-3 max-w-lg">
                    Piezas seleccionadas, disponibles para envío inmediato.
                </p>
            </div>

            <a
                href="{{ route('catalog.index', ['collection' => [$collection['id']]]) }}"
                class="group inline-flex shrink-0 items-center gap-2 font-label-md text-label-md uppercase tracking-wide text-primary">

                Ver toda la colección

                <span
                    class="material-symbols-outlined text-lg transition-transform duration-300 group-hover:translate-x-1">
                    arrow_forward
                </span>

            </a>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">

            @foreach($collection['products'] as $product)

                <x-product-card
                    :product="$product"
                />

            @endforeach

        </div>

    </div>
</section>