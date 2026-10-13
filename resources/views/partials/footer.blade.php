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

<footer class="bg-white w-full border-t border-gray-100 shadow-none">
    <div class="max-w-screen-2xl mx-auto px-8 py-16
                flex flex-col md:flex-row justify-between items-center gap-8
                font-inter text-sm uppercase tracking-widest antialiased">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="text-lg font-bold text-primary">
            Ifix
        </a>

        {{-- Links --}}
        <nav class="flex flex-wrap justify-center gap-8" aria-label="Footer">
            @foreach ($footerLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="text-gray-400 hover:text-primary transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Copyright --}}
        <p class="text-gray-400">
            &copy; {{ $year }} Ifix. Ingeniería de Precisión.
        </p>

    </div>
</footer>
