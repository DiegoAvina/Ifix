@props(['collection', 'index' => 0])

<section id="{{ $index === 0 ? 'productos-destacados' : 'coleccion-' . $collection['id'] }}"
    class="{{ $index % 2 === 1 ? 'bg-[#f8f8f7]' : 'bg-white' }} py-12 sm:py-16">
    <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-6 mb-16 sm:flex-row sm:items-end sm:justify-between">

            <div>
                <span class="mb-2 block text-xs font-bold uppercase tracking-[0.24em] text-[#bd7710]">
                    Selección IFIX
                </span>

                <h2 class="font-heading text-2xl font-extrabold text-neutral-950 sm:text-3xl">
                    {{ $collection['name'] }}
                </h2>

                <p class="mt-2 max-w-lg text-sm text-neutral-600">
                    Productos profesionales para lograr un acabado impecable.
                </p>
            </div>

            <a
                href="{{ route('catalog.index', ['collection' => [$collection['id']]]) }}"
                class="group inline-flex shrink-0 items-center gap-2 text-sm font-bold uppercase tracking-wide text-neutral-800 transition hover:text-[#bd7710]">

                Ver todos

                <span
                    class="material-symbols-outlined text-lg transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">
                    arrow_forward
                </span>

            </a>

        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">

            @foreach($collection['products'] as $product)

                <x-product-card
                    :product="$product"
                />

            @endforeach

        </div>

    </div>
</section>