{{--
    Partial: Footer
    Incluir con: @include('partials.footer')

    $footerLinks (opcional) → array de enlaces del footer
--}}

@php
    $footerLinks = $footerLinks ?? [
        ['label' => 'Política de Privacidad', 'url' => '#'],
        ['label' => 'Términos de Servicio',   'url' => '#'],
        ['label' => 'Sostenibilidad',          'url' => '#'],
        ['label' => 'Carreras',                'url' => '#'],
    ];

    $year = date('Y');
@endphp

<footer class="w-full border-t border-neutral-800 bg-[#0b0c0e] text-white shadow-none">
    <div class="mx-auto flex max-w-[1340px] flex-col items-center justify-between gap-8 px-4 py-12
                font-inter text-sm uppercase tracking-widest antialiased sm:px-6 md:flex-row lg:px-8">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="text-lg font-black tracking-[0.2em] text-[#f5a623]">
            IFIX
        </a>

        {{-- Links --}}
        <nav class="flex flex-wrap justify-center gap-8" aria-label="Footer">
            @foreach ($footerLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="text-neutral-400 transition-colors hover:text-[#f5a623]">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Copyright --}}
        <p class="text-center text-neutral-500">
            &copy; {{ $year }} IFIX. Detallado automotriz profesional.
        </p>

    </div>
</footer>
