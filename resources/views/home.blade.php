@extends('layouts.app')

@section('title', 'IFIX | Productos para detallado automotriz profesional')
@section('meta_description', 'Compra productos IFIX para detallado automotriz: shampoo para autos, pulimentos de corte, ceras de grafeno y silicio, selladores cerámicos y restauradores de plásticos. Envíos a todo México.')

@push('head')
    <script type="application/ld+json" nonce="{{ $cspNonce }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/').'#organization',
                    'name' => 'IFIX',
                    'url' => url('/'),
                    'logo' => asset('images/og-ifix.jpg'),
                    'description' => 'Productos para detallado automotriz.',
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+52-55-4169-8230',
                        'contactType' => 'customer service',
                        'areaServed' => 'MX',
                        'availableLanguage' => 'Spanish',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'name' => 'IFIX',
                    'url' => url('/'),
                    'inLanguage' => 'es-MX',
                    'publisher' => ['@id' => url('/').'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('catalog.index').'?search={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    @include('partials.hero')

    <section class="border-b border-neutral-200 bg-white">
        <div class="mx-auto grid max-w-[1340px] grid-cols-2 gap-5 px-4 py-6 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['icon' => 'local_shipping', 'title' => 'Envíos a todo México', 'text' => 'Recibe tu pedido en casa'],
                ['icon' => 'verified_user', 'title' => 'Calidad profesional', 'text' => 'Productos probados por expertos'],
                ['icon' => 'lock', 'title' => 'Compra segura', 'text' => 'Pagos protegidos'],
                ['icon' => 'support_agent', 'title' => 'Te asesoramos', 'text' => 'Te ayudamos a elegir'],
            ] as $feature)
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-50 text-[#d98b12]">
                        <span class="material-symbols-outlined" aria-hidden="true">{{ $feature['icon'] }}</span>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-neutral-900">{{ $feature['title'] }}</p>
                        <p class="mt-1 text-xs text-neutral-500">{{ $feature['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-[#f8f8f7] py-12 sm:py-16">
        <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#bd7710]">Encuentra lo que buscas</p>
                    <h2 class="mt-2 font-heading text-2xl font-extrabold text-neutral-950 sm:text-3xl">Categorías</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="hidden items-center gap-1 text-sm font-semibold text-neutral-700 hover:text-[#bd7710] sm:flex">
                    Ver todo <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </a>
            </div>

            @php
                // Mosaico: la primera categoría es grande. En escritorio el
                // mosaico de 4 columnas solo cuadra con exactamente 5.
                $categoryCount = count($categories);
                $bento = $categoryCount === 5;
            @endphp

            <div @class([
                'grid grid-cols-2 gap-3 sm:gap-4',
                'lg:grid-cols-4 lg:auto-rows-[230px]' => $bento,
                'sm:grid-cols-3' => ! $bento && $categoryCount >= 3,
                'lg:grid-cols-4' => ! $bento && $categoryCount >= 4,
            ])>
                @foreach ($categories as $category)
                    @php
                        // Destacada: la primera, si así el resto llena filas completas en el celular (2 columnas).
                        $featured = $loop->first && $categoryCount % 2 === 1;
                    @endphp
                    <a href="{{ $category['url'] }}"
                        @class([
                            'group relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-neutral-950 p-4 text-white shadow-sm ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f5a623] sm:p-5',
                            'col-span-2 h-60 sm:h-72' => $featured,
                            'h-52 sm:h-64 lg:h-72' => ! $featured,
                            'lg:row-span-2 lg:h-auto lg:p-7' => $featured && $bento,
                            'lg:h-auto' => $bento && ! $featured,
                        ])>
                        @if ($category['image'] ?? null)
                            <img src="{{ $category['image'] }}" alt="" loading="lazy" decoding="async"
                                class="absolute inset-0 -z-20 h-full w-full object-cover object-center opacity-90 transition duration-700 ease-out group-hover:scale-105 group-hover:opacity-100">
                        @else
                            <span class="material-symbols-outlined absolute -right-4 -top-4 -z-20 text-[160px] text-white/5" aria-hidden="true">{{ $category['icon'] }}</span>
                        @endif

                        {{-- Degradado para que el texto se lea sobre cualquier foto. --}}
                        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-neutral-950 via-neutral-950/55 to-neutral-950/0" aria-hidden="true"></span>
                        <span class="absolute inset-x-0 bottom-0 -z-10 h-1 origin-left scale-x-0 bg-[#f5a623] transition duration-500 group-hover:scale-x-100" aria-hidden="true"></span>

                        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-black/45 px-2.5 py-1 text-[11px] font-bold text-neutral-100 ring-1 ring-white/15 backdrop-blur sm:left-5 sm:top-5">
                            <span class="material-symbols-outlined text-[15px] text-[#f5a623]" aria-hidden="true">{{ $category['icon'] }}</span>
                            {{ $category['count'] }} {{ $category['count'] === 1 ? 'producto' : 'productos' }}
                        </span>

                        <span class="flex items-end justify-between gap-3">
                            <span class="min-w-0">
                                <span @class([
                                    'block font-heading font-extrabold leading-tight',
                                    'text-2xl sm:text-3xl' => $featured,
                                    'text-base sm:text-lg' => ! $featured,
                                ])>{{ $category['name'] }}</span>
                                @if ($category['description'] ?? null)
                                    <span @class([
                                        'mt-1 block text-neutral-300',
                                        'text-sm' => $featured,
                                        'hidden text-xs sm:block' => ! $featured,
                                    ])>{{ $category['description'] }}</span>
                                @endif
                            </span>
                            {{-- En tarjetas chicas del celular la flecha le quita espacio al nombre. --}}
                            <span @class([
                                'h-9 w-9 shrink-0 items-center justify-center rounded-[50%] bg-white/10 ring-1 ring-white/20 backdrop-blur transition duration-300 group-hover:bg-[#f5a623] group-hover:text-neutral-950 group-hover:ring-[#f5a623]',
                                'flex' => $featured,
                                'hidden sm:flex' => ! $featured,
                            ])>
                                <span class="material-symbols-outlined text-lg transition group-hover:translate-x-0.5" aria-hidden="true">arrow_forward</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>

            <a href="{{ route('catalog.index') }}" class="mt-5 flex items-center justify-center gap-1 text-sm font-semibold text-neutral-700 hover:text-[#bd7710] sm:hidden">
                Ver todo el catálogo <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
    </section>

    @foreach ($collections as $collection)
        @if (count($collection['products']))
            <x-collection-section :collection="$collection" :index="$loop->index" />
        @endif
    @endforeach

    <section class="relative isolate overflow-hidden bg-neutral-950 py-16 text-white sm:py-24">
        <img
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuD1mBUA-D2yBdZgwyHSdjfVMBpHZ6HYX5SXix_goaUFpaTfF3qj0EZ_MijLVj4oDU6vPipv--se8suz-9yW87rFlB6HLadUeAkWSxHMEaY-0TS9ACaEkgtUpwXvznRTioTmltEMU2ihlMLNlCTlE7JYMq4ds0ivm0TxhpV4RJcuG20CKeTmzgviTjYKnvlindP6W9Gwj5pXIxuqi6_NE-1CCVHRb6i4qOLBw_KjbGgIOxHO9A6-1jNk9MUA6J945nCFffM"
            alt=""
            class="absolute inset-0 -z-10 h-full w-full object-cover object-[center_70%] opacity-20"
            loading="lazy">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-neutral-950 via-neutral-950/90 to-neutral-950/50"></div>
        <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#f5a623]">Cuidado que se nota</p>
                <h2 class="mt-4 font-heading text-3xl font-black uppercase leading-tight sm:text-5xl">Restaura el acabado.<br>Protege cada detalle.</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-neutral-300 sm:text-base">
                    Productos profesionales IFIX para devolver color, brillo y vida a las superficies de tu auto.
                </p>
                <a href="{{ collect($categories)->firstWhere('name', 'Restauradores')['url'] ?? route('catalog.index', ['search' => 'restaurador']) }}"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-[#f5a623] px-6 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">
                    Explorar restauradores
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
@endsection
