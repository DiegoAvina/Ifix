@if(count($product->specifications))

@php
    // En móvil se muestran las primeras N y el resto tras "Ver todas"; en desktop, todas.
    $visibleSpecs = 6;
    $specCount = count($product->specifications);
@endphp

<section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7" x-data="{ all: false }">

    <h2 class="font-heading text-xl font-extrabold text-neutral-950 sm:text-2xl">
        Especificaciones
    </h2>

    <dl id="product-specs" class="mt-4 grid grid-cols-1 gap-x-10 sm:grid-cols-2">

        @foreach($product->specifications as $spec)

            <div
                class="flex items-start justify-between gap-4 border-b border-neutral-100 py-3 sm:gap-6 sm:py-3.5"
                @if ($loop->index >= $visibleSpecs)
                    :class="all ? 'flex' : 'hidden lg:flex'"
                @endif
            >

                <dt class="min-w-0 shrink-0 basis-2/5 break-words text-sm text-neutral-500">
                    {{ $spec->label }}
                </dt>

                <dd class="min-w-0 break-words text-right text-sm font-semibold text-neutral-900">
                    {{ $spec->value }}
                </dd>

            </div>

        @endforeach

    </dl>

    @if ($specCount > $visibleSpecs)
        <button
            type="button"
            x-cloak
            @click="all = !all"
            :aria-expanded="all.toString()"
            aria-controls="product-specs"
            class="mt-3 inline-flex min-h-11 items-center gap-1 rounded-full px-1 text-sm font-bold text-[#a76500] lg:hidden"
        >
            <span x-text="all ? 'Ver menos' : 'Ver todas ({{ $specCount }})'">Ver todas ({{ $specCount }})</span>
            <span class="material-symbols-outlined text-xl transition-transform" :class="all && 'rotate-180'" aria-hidden="true">expand_more</span>
        </button>
    @endif

</section>

@endif
