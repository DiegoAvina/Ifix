{{--
    Partial: Hero Carousel
    Incluir con: @include('partials.hero')

    $slides (opcional) → array de slides. Si no se pasa, usa los de ejemplo.
    Cada slide:
        eyebrow      → string  Etiqueta superior pequeña
        title        → string  Título principal (display)
        description  → string  Párrafo descriptivo
        primaryBtn   → array   ['label' => '...', 'url' => '#']
        secondaryBtn → array   ['label' => '...', 'url' => '#']
        image        → string  URL de la imagen
        imageAlt     → string  Alt de la imagen
--}}

@php
    $slides = $slides ?? [
        [
            'eyebrow'      => 'Rendimiento de Precisión',
            'title'        => 'Innovando tu Mundo',
            'description'  => 'La nueva OptiBook Pro 16. Diseñada para creadores que exigen precisión quirúrgica y potencia bruta en un chasis plateado minimalista.',
            'primaryBtn'   => ['label' => 'Comprar Ahora', 'url' => '#'],
            'secondaryBtn' => ['label' => 'Saber Más',     'url' => '#'],
            'image'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDhvg51NLayB1EqU0v6jXw4W6RuPeSYwKeMlqGcx8Z5pKb-BZJsnd9my_C0HfQQx_Oh2rLyhh1-4mBi1uQFrpjaIfaIbUtmeiWVmK5MZsAs1nfoC1ppaOCjxVi_2p4mV0m8VL4u7zVHfTBx_Y3mJEHUz22uWIkXs6DBvEKlNR31jqCcYhgqCRW9DMbRCltA8lmHYJYVeYtNC7P4zUPVJT5kQqw2AlBNhgJmHbhYJJPCwQa6W3TpwhPN_ulKVIyKMQY_wCzHE7vwPq93',
            'imageAlt'     => 'OptiBook Pro 16',
        ],
        [
            'eyebrow'      => 'Inmersión Absoluta',
            'title'        => 'OptiVR Horizon',
            'description'  => 'Expande tus límites. El sistema de realidad virtual más avanzado con pantallas micro-OLED 8K y seguimiento ocular de grado médico.',
            'primaryBtn'   => ['label' => 'Reservar',    'url' => '#'],
            'secondaryBtn' => ['label' => 'Explorar VR', 'url' => '#'],
            'image'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuArwZOLb_r61b2BTy2nqmXXIIH1pZJ4iZMT7LRSNTI90HNN4Wx2rFgbxKuHV4NdidWfndRfoGMWH7n3bjwWerpJTPp3wVXeHbjh3x21Nb_CW7VlR2V0HyAKw6Bl8MEoZa3K3PJbI4f1u_cJzTOp67L7ViRwev8YMbbp98cbg_f24yT3kWq22cWLQJkB3ITnjLRRqO8UMKePt42M_BJKXkwD_l-T-J_OkKlC49lVyIq1eli9tg99zFY0QnK9dhRicpf2Qj-pgOwk-Jh-',
            'imageAlt'     => 'OptiVR Horizon',
        ],
    ];
@endphp

<section class="relative bg-white overflow-hidden">
    <div class="relative" id="hero-carousel">

        <div class="carousel-container relative">
            @foreach ($slides as $index => $slide)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="max-w-screen-2xl mx-auto px-8 py-24 grid grid-cols-12 gap-gutter items-center">

                        {{-- Texto --}}
                        <div class="col-span-12 lg:col-span-5 z-10">
                            <span class="font-label-md text-label-md text-primary mb-6 block uppercase tracking-widest">
                                {{ $slide['eyebrow'] }}
                            </span>
                            <h1 class="font-display-xl text-display-xl text-on-surface mb-8">
                                {{ $slide['title'] }}
                            </h1>
                            <p class="font-body-lg text-body-lg text-secondary max-w-lg mb-12">
                                {{ $slide['description'] }}
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $slide['primaryBtn']['url'] }}"
                                   class="rounded-full bg-primary text-on-primary px-8 py-4 font-label-md text-label-md transition-all duration-300 hover:brightness-110 active:opacity-90">
                                    {{ $slide['primaryBtn']['label'] }}
                                </a>
                                <a href="{{ $slide['secondaryBtn']['url'] }}"
                                   class="rounded-full border border-outline-variant text-on-surface px-8 py-4 font-label-md text-label-md transition-all duration-300 hover:border-primary hover:text-primary">
                                    {{ $slide['secondaryBtn']['label'] }}
                                </a>
                            </div>
                        </div>

                        {{-- Imagen --}}
                        <div class="col-span-12 lg:col-span-7 relative flex justify-center">
                            <div class="w-full aspect-square max-w-2xl flex items-center justify-center">
                                <img
                                    src="{{ $slide['image'] }}"
                                    alt="{{ $slide['imageAlt'] }}"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Dots de navegación --}}
        <div class="absolute bottom-12 left-1/2 -translate-x-1/2 flex gap-3 z-20">
            @foreach ($slides as $index => $slide)
                <button
                    aria-label="Slide {{ $index + 1 }}"
                    data-slide-index="{{ $index }}"
                    class="carousel-dot w-2 h-2 rounded-full transition-all duration-300
                           {{ $index === 0 ? 'bg-primary' : 'bg-outline-variant hover:bg-outline' }}"
                ></button>
            @endforeach
        </div>

    </div>
</section>

@push('scripts')
<script nonce="{{ $cspNonce }}">
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');
    const dots   = document.querySelectorAll('.carousel-dot');

    function setSlide(index) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('bg-primary');
        dots[currentSlide].classList.add('bg-outline-variant');

        currentSlide = index;

        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('bg-primary');
        dots[currentSlide].classList.remove('bg-outline-variant');
    }

    // Auto-rotación cada 6 segundos
    setInterval(() => setSlide((currentSlide + 1) % slides.length), 6000);

    dots.forEach((dot) => {
        dot.addEventListener('click', () => setSlide(Number(dot.dataset.slideIndex)));
    });
</script>
@endpush
