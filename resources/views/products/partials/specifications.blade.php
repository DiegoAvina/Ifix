@if(count($product->specifications))

<section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7">

    <h2 class="font-heading text-xl font-extrabold text-neutral-950 sm:text-2xl">
        Especificaciones
    </h2>

    <dl class="mt-4 grid grid-cols-1 gap-x-10 sm:grid-cols-2">

        @foreach($product->specifications as $spec)

            <div class="flex items-center justify-between gap-6 border-b border-neutral-100 py-3.5">

                <dt class="text-sm text-neutral-500">
                    {{ $spec->label }}
                </dt>

                <dd class="text-right text-sm font-semibold text-neutral-900">
                    {{ $spec->value }}
                </dd>

            </section>

        @endforeach

    </dl>

</div>

@endif
