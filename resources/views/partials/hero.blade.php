@php
    $slide = $slides[0] ?? [
        'eyebrow' => 'CUIDA · RESTAURA · PROTEGE',
        'title' => 'El detalle hace la diferencia',
        'description' => 'Productos profesionales para detallado y restauración de plásticos y pintura.',
        'primaryBtn' => ['label' => 'Comprar ahora', 'url' => route('catalog.index')],
        'secondaryBtn' => ['label' => 'Conoce IFIX', 'url' => '#productos-destacados'],
        'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD1mBUA-D2yBdZgwyHSdjfVMBpHZ6HYX5SXix_goaUFpaTfF3qj0EZ_MijLVj4oDU6vPipv--se8suz-9yW87rFlB6HLadUeAkWSxHMEaY-0TS9ACaEkgtUpwXvznRTioTmltEMU2ihlMLNlCTlE7JYMq4ds0ivm0TxhpV4RJcuG20CKeTmzgviTjYKnvlindP6W9Gwj5pXIxuqi6_NE-1CCVHRb6i4qOLBw_KjbGgIOxHO9A6-1jNk9MUA6J945nCFffM',
        'imageAlt' => 'Detallado profesional automotriz IFIX',
    ];
@endphp

<section class="relative isolate flex min-h-[440px] items-center overflow-hidden bg-[#08090a] text-white sm:min-h-[520px]">
    <img
        src="{{ $slide['image'] }}"
        alt="{{ $slide['imageAlt'] }}"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-center opacity-40 sm:opacity-55"
        fetchpriority="high">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black via-black/85 to-black/20"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/60 via-transparent to-black/10"></div>

    <div class="mx-auto w-full max-w-[1340px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="max-w-2xl">
            <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.3em] text-[#f5a623] sm:text-sm">
                <span class="h-px w-8 bg-[#f5a623]"></span>
                {{ $slide['eyebrow'] }}
            </span>
            <h1 class="mt-5 font-heading text-4xl font-black uppercase leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">
                {{ $slide['title'] }}
            </h1>
            <p class="mt-5 max-w-lg text-sm leading-7 text-neutral-200 sm:mt-7 sm:text-lg">
                {{ $slide['description'] }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3 sm:mt-10">
                <a href="{{ $slide['primaryBtn']['url'] }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#f5a623] px-6 py-3.5 text-sm font-extrabold uppercase tracking-wide text-neutral-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-amber-400 sm:px-8">
                    {{ $slide['primaryBtn']['label'] }}
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </a>
                <a href="{{ $slide['secondaryBtn']['url'] }}"
                    class="inline-flex items-center gap-2 rounded-full border border-white/50 px-6 py-3.5 text-sm font-bold text-white transition hover:border-white hover:bg-white/10 sm:px-8">
                    {{ $slide['secondaryBtn']['label'] }}
                </a>
            </div>
        </div>
    </div>
</section>
