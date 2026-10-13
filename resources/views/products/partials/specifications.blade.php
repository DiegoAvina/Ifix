@if(count($product->specifications))

<div class="rounded-3xl border border-outline-variant/30 bg-white p-8">

    <h2 class="font-headline-md text-headline-md text-on-surface mb-8">
        Especificaciones
    </h2>

    <dl class="grid grid-cols-1 gap-x-10 sm:grid-cols-2">

        @foreach($product->specifications as $spec)

            <div class="flex items-center justify-between gap-6 border-b border-outline-variant/20 py-4">

                <dt class="text-sm text-secondary">
                    {{ $spec->label }}
                </dt>

                <dd class="text-sm font-semibold text-on-surface text-right">
                    {{ $spec->value }}
                </dd>

            </div>

        @endforeach

    </dl>

</div>

@endif
