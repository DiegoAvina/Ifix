@php

$from = now()->addDays(2);

$to = now()->addDays(4);

@endphp

<div class="mt-8 rounded-2xl border border-outline-variant/30 bg-surface-container-low p-6">

    <div class="flex items-start gap-4">

        <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-primary"
        >
            <span class="material-symbols-outlined" aria-hidden="true">local_shipping</span>
        </div>

        <div>

            <p class="font-semibold text-on-surface">

                Entrega estimada

            </p>

            <p class="mt-1 text-secondary">

                Recíbelo entre

                <span class="font-semibold text-on-surface">

                    {{ $from->translatedFormat('d \\d\\e F') }}

                </span>

                y

                <span class="font-semibold text-on-surface">

                    {{ $to->translatedFormat('d \\d\\e F') }}

                </span>

            </p>

            <p class="mt-2 text-sm font-medium text-emerald-700">

                Envío gratis en compras mayores a $999

            </p>

        </div>

    </div>

    <div class="my-5 border-t border-outline-variant/30"></div>

    <div class="flex items-center gap-3">

        <span class="material-symbols-outlined text-lg text-primary" aria-hidden="true">
            replay
        </span>

        <span class="text-sm text-secondary">

            Devoluciones fáciles durante 30 días

        </span>

    </div>

</div>