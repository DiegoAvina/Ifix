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
                        class="hero-image absolute inset-0 -z-20 h-full w-full object-cover object-center opacity-40 sm:opacity-55"
                        @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black via-black/85 to-black/20"></div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/60 via-transparent to-black/10"></div>

                    <div class="mx-auto w-full max-w-[1340px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                        <div class="max-w-2xl">
                            <span data-hero-reveal style="--reveal-delay: 0ms" class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.3em] text-[#f5a623] sm:text-sm">
                                <span class="h-px w-8 bg-[#f5a623]"></span>
                                {{ $slide['eyebrow'] }}
                            </span>
                            <{{ $loop->first ? 'h1' : 'h2' }} data-hero-reveal style="--reveal-delay: 90ms" class="mt-5 font-heading text-4xl font-black uppercase leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">
                                {{ $slide['title'] }}
                            </{{ $loop->first ? 'h1' : 'h2' }}>
                            @if ($slide['description'])
                                <p data-hero-reveal style="--reveal-delay: 180ms" class="mt-5 max-w-lg text-sm leading-7 text-neutral-200 sm:mt-7 sm:text-lg">
                                    {{ $slide['description'] }}
                                </p>
                            @endif
                            <div data-hero-reveal style="--reveal-delay: 270ms" class="mt-8 flex flex-wrap gap-3 sm:mt-10">
                                {{-- Principal: destello al pasar el mouse, la flecha avanza y el botón se hunde al hacer clic. --}}
                                <a href="{{ $slide['primaryBtn']['url'] }}"
                                    class="group relative inline-flex items-center gap-3 overflow-hidden rounded-full bg-[#f5a623] py-2 pl-6 pr-2 text-sm font-extrabold uppercase tracking-wide text-neutral-950 shadow-[0_10px_30px_-10px_rgba(245,166,35,0.65)] transition duration-300 hover:-translate-y-0.5 hover:bg-amber-400 hover:shadow-[0_16px_40px_-12px_rgba(245,166,35,0.85)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f5a623] focus-visible:ring-offset-2 focus-visible:ring-offset-black active:translate-y-0 active:scale-[0.97] sm:pl-8">
                                    <span class="pointer-events-none absolute inset-y-0 -left-1/2 w-1/3 -skew-x-12 bg-gradient-to-r from-transparent via-white/50 to-transparent opacity-0 transition-all duration-700 ease-out group-hover:left-[110%] group-hover:opacity-100" aria-hidden="true"></span>
                                    <span class="relative">{{ $slide['primaryBtn']['label'] }}</span>
                                    <span class="relative flex h-9 w-9 items-center justify-center rounded-[50%] bg-neutral-950 text-[#f5a623] transition duration-300 group-hover:translate-x-0.5 group-hover:rotate-[-45deg]" aria-hidden="true">
                                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                                    </span>
                                </a>
                                @isset($slide['secondaryBtn'])
                                    {{-- Secundario: lleva más abajo en la página, por eso la flecha apunta hacia abajo. --}}
                                    <a href="{{ $slide['secondaryBtn']['url'] }}"
                                        class="group inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/5 px-6 py-3.5 text-sm font-bold text-white backdrop-blur-sm transition duration-300 hover:border-[#f5a623]/70 hover:bg-white/10 hover:text-[#f5a623] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black active:scale-[0.97] sm:px-8">
                                        {{ $slide['secondaryBtn']['label'] }}
                                        <span class="material-symbols-outlined text-lg transition duration-300 group-hover:translate-y-0.5" aria-hidden="true">arrow_downward</span>
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

        <div class="absolute bottom-6 right-6 z-10 hidden items-center gap-3 sm:flex lg:right-8">
            <p class="mr-1 font-heading text-sm font-bold tabular-nums text-white/60" aria-hidden="true">
                <span data-hero-current class="text-white">01</span> / {{ str_pad(count($heroSlides), 2, '0', STR_PAD_LEFT) }}
            </p>
            <button type="button" data-hero-prev aria-label="Oferta anterior"
                class="group flex h-12 w-12 items-center justify-center rounded-[50%] border border-white/30 bg-black/30 text-white backdrop-blur-sm transition duration-300 hover:border-[#f5a623] hover:bg-[#f5a623] hover:text-neutral-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f5a623] active:scale-90">
                <span class="material-symbols-outlined transition duration-300 group-hover:-translate-x-0.5" aria-hidden="true">arrow_back</span>
            </button>
            {{-- El anillo se llena mientras corre el tiempo de la oferta (--hero-progress). --}}
            <button type="button" data-hero-next aria-label="Oferta siguiente"
                class="group relative flex h-12 w-12 items-center justify-center rounded-[50%] border border-white/30 bg-black/30 text-white backdrop-blur-sm transition duration-300 hover:border-[#f5a623] hover:bg-[#f5a623] hover:text-neutral-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f5a623] active:scale-90">
                <svg class="hero-next-ring pointer-events-none absolute -inset-px h-[calc(100%+2px)] w-[calc(100%+2px)] -rotate-90" viewBox="0 0 48 48" aria-hidden="true">
                    <circle cx="24" cy="24" r="23" fill="none" stroke="#f5a623" stroke-width="2" stroke-linecap="round" pathLength="100" />
                </svg>
                <span class="material-symbols-outlined transition duration-300 group-hover:translate-x-0.5" aria-hidden="true">arrow_forward</span>
            </button>
        </div>
    @endif
</section>
