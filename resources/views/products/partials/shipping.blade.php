@php

$from = now()->addDays(2);

$to = now()->addDays(4);

@endphp

<div class="mt-5 rounded-2xl border border-neutral-200 bg-[#f5f5f3] p-4 sm:p-5">

    <div class="flex items-start gap-4">

        <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#bd7710] shadow-sm"
        >
            <span class="material-symbols-outlined" aria-hidden="true">local_shipping</span>
        </div>

        <div>

            <p class="text-sm font-bold text-neutral-900">

                Entrega estimada

            </p>

            <p class="mt-1 text-xs leading-5 text-neutral-600">

                Recíbelo entre

                <span class="font-semibold text-neutral-900">

                    {{ $from->translatedFormat('d \\d\\e F') }}

                </span>

                y

                <span class="font-semibold text-neutral-900">

                    {{ $to->translatedFormat('d \\d\\e F') }}

                </span>

            </p>

            <p class="mt-2 text-xs font-bold text-emerald-700">

                Envío gratis desde ${{ number_format(config('shipping.free_shipping_from'), 0) }}

            </p>

        </div>

    </div>

    <div class="my-4 border-t border-neutral-200"></div>

    <div class="flex items-center gap-3">

        <span class="material-symbols-outlined text-lg text-[#bd7710]" aria-hidden="true">
            replay
        </span>

        <span class="text-xs text-neutral-600">

            Devoluciones fáciles durante 30 días

        </span>

    </div>

</div>