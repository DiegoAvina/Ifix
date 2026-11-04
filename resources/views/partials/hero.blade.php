@php
    $heroSlides = ! empty($slides) ? $slides : [[
        'eyebrow' => 'CUIDA · RESTAURA · PROTEGE',
        'title' => 'El detalle hace la diferencia',
        'description' => 'Productos profesionales para detallado y restauración de plásticos y pintura.',
        'primaryBtn' => ['label' => 'Comprar ahora', 'url' => route('catalog.index')],
        'secondaryBtn' => ['label' => 'Conoce IFIX', 'url' => '#productos-destacados'],
        // Foto: Luay Barani en Unsplash (Unsplash License).
        'image' => asset('images/hero-detailing.jpg'),
        'imageAlt' => 'Detallado profesional automotriz IFIX',
    ]];
    $isCarousel = count($heroSlides) > 1;
@endphp

<section
    @if ($isCarousel) data-hero-carousel aria-roledescription="carrusel" aria-label="Ofertas" @endif
    class="swiper relative overflow-hidden bg-[#08090a] text-white [--swiper-pagination-bullet-inactive-color:#fff] [--swiper-pagination-bullet-inactive-opacity:0.5] [--swiper-pagination-color:#f5a623]">
    <div class="swiper-wrapper">
        @foreach ($heroSlides as $slide)
            <div class="swiper-slide !h-auto">
                <div class="relative isolate flex h-full min-h-[440px] items-center overflow-hidden sm:min-h-[520px]">
                    <img
                        src="{{ $slide['image'] }}"
                        alt="{{ $slide['imageAlt'] }}"
                        class="absolute inset-0 -z-20 h-full w-full object-cover object-center opacity-40 sm:opacity-55"
                        @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black via-black/85 to-black/20"></div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/60 via-transparent to-black/10"></div>

                    <div class="mx-auto w-full max-w-[1340px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                        <div class="max-w-2xl">
                            <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.3em] text-[#f5a623] sm:text-sm">
                                <span class="h-px w-8 bg-[#f5a623]"></span>
                                {{ $slide['eyebrow'] }}
                            </span>
                            <{{ $loop->first ? 'h1' : 'h2' }} class="mt-5 font-heading text-4xl font-black uppercase leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">
                                {{ $slide['title'] }}
                            </{{ $loop->first ? 'h1' : 'h2' }}>
                            @if ($slide['description'])
                                <p class="mt-5 max-w-lg text-sm leading-7 text-neutral-200 sm:mt-7 sm:text-lg">
                                    {{ $slide['description'] }}
                                </p>
                            @endif
                            <div class="mt-8 flex flex-wrap gap-3 sm:mt-10">
                                <a href="{{ $slide['primaryBtn']['url'] }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-[#f5a623] px-6 py-3.5 text-sm font-extrabold uppercase tracking-wide text-neutral-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-amber-400 sm:px-8">
                                    {{ $slide['primaryBtn']['label'] }}
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                                </a>
                                @isset($slide['secondaryBtn'])
                                    <a href="{{ $slide['secondaryBtn']['url'] }}"
                                        class="inline-flex items-center gap-2 rounded-full border border-white/50 px-6 py-3.5 text-sm font-bold text-white transition hover:border-white hover:bg-white/10 sm:px-8">
                                        {{ $slide['secondaryBtn']['label'] }}
                                    </a>
                                @endisset
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($isCarousel)
        <div class="swiper-pagination"></div>

        <div class="absolute bottom-6 right-6 z-10 hidden gap-2 sm:flex lg:right-8">
            <button type="button" data-hero-prev aria-label="Oferta anterior"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-white/50 text-white transition hover:border-white hover:bg-white/10">
                <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
            </button>
            <button type="button" data-hero-next aria-label="Oferta siguiente"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-white/50 text-white transition hover:border-white hover:bg-white/10">
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </button>
        </div>
    @endif
</section>
