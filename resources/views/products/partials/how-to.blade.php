{{-- Modo de uso: pasos capturados en el panel (Ficha técnica → Modo de uso). --}}
<section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="como-se-usa">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f5a623]/15 text-[#bd7710]">
            <span class="material-symbols-outlined" aria-hidden="true">checklist</span>
        </span>
        <h2 id="como-se-usa" class="font-heading text-xl font-extrabold text-neutral-950 sm:text-2xl">Cómo se usa</h2>
    </div>

    <ol class="mt-6 grid gap-3 sm:grid-cols-2">
        @foreach ($product->howTo as $step)
            <li class="flex gap-4 rounded-xl bg-[#f5f5f3] p-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-950 font-heading text-sm font-black text-[#f5a623]" aria-hidden="true">{{ $loop->iteration }}</span>
                <p class="pt-1 text-sm leading-relaxed text-neutral-700">
                    <span class="sr-only">Paso {{ $loop->iteration }}:</span>
                    {{ $step }}
                </p>
            </li>
        @endforeach
    </ol>
</section>
