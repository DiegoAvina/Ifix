{{--
    En qué paso del proceso de detallado va este producto. Los pasos y su
    categoría se editan en el panel (Nosotros → Proceso de detallado).
    $process: AboutPage::processFor() → steps, previous, next.
--}}
<section class="overflow-hidden rounded-2xl bg-neutral-950 p-5 text-white sm:p-7" aria-labelledby="proceso-producto">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#f5a623]">Proceso de detallado</p>
            <h2 id="proceso-producto" class="mt-1 font-heading text-xl font-extrabold sm:text-2xl">¿En qué paso se usa?</h2>
        </div>
        @if (\App\Models\AboutPage::current()->published)
            <a href="{{ route('about') }}#proceso-titulo" class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-neutral-400 transition hover:text-[#f5a623]">
                Ver el proceso completo
                <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
        @endif
    </div>

    {{-- Pasos: el actual resaltado; en celular, carrusel horizontal --}}
    <ol class="-mx-5 mt-6 flex snap-x gap-2 overflow-x-auto px-5 pb-2 sm:mx-0 sm:px-0 [scrollbar-width:none]">
        @foreach ($process['steps'] as $step)
            <li class="min-w-[8.5rem] flex-1 snap-start">
                <div @class([
                    'relative h-full rounded-xl border p-3 transition',
                    'border-[#f5a623] bg-[#f5a623] text-neutral-950 shadow-[0_0_30px_rgba(245,166,35,.35)]' => $step['current'],
                    'border-white/10 bg-white/[0.04] text-neutral-400' => ! $step['current'],
                ])
                    @if ($step['current']) aria-current="step" @endif>
                    <div class="flex items-center justify-between">
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">{{ $step['category']['icon'] ?? 'auto_awesome' }}</span>
                        <span class="font-heading text-xs font-black opacity-60">{{ $loop->iteration }}</span>
                    </div>
                    <p @class(['mt-3 font-heading text-sm font-black', 'text-white' => ! $step['current']])>{{ $step['title'] }}</p>
                    @if ($step['current'])
                        <p class="mt-0.5 text-[10px] font-extrabold uppercase tracking-wider">Este producto</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    {{-- Una categoría puede estar en varios pasos (p. ej. Limpieza: Lavar y Descontaminar). --}}
    @foreach (collect($process['steps'])->where('current', true) as $current)
        @if ($current['text'])
            <p class="mt-4 text-sm leading-relaxed text-neutral-300">
                <strong class="text-white">{{ $current['title'] }}:</strong> {{ $current['text'] }}
            </p>
        @endif
    @endforeach

    {{-- Antes y después: lleva al cliente a completar el proceso --}}
    @if ($process['previous'] || $process['next'])
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach ([['previous', 'Antes de este paso', 'west'], ['next', 'Siguiente paso', 'east']] as [$key, $label, $arrow])
                @php $step = $process[$key]; @endphp
                @if ($step)
                    <{{ $step['category'] ? 'a' : 'div' }}
                        @if ($step['category']) href="{{ $step['category']['url'] }}" @endif
                        @class([
                            'group flex items-center gap-4 rounded-xl border border-white/10 p-4 transition',
                            'hover:border-[#f5a623]/60 hover:bg-white/[0.04]' => $step['category'],
                            'sm:col-start-2' => $key === 'next' && ! $process['previous'],
                        ])>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-[#f5a623]">
                            <span class="material-symbols-outlined" aria-hidden="true">{{ $step['category']['icon'] ?? 'auto_awesome' }}</span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[10px] font-extrabold uppercase tracking-[0.18em] text-neutral-500">{{ $label }}</span>
                            <span class="block font-heading font-black">{{ $step['title'] }}</span>
                            @if ($step['category'])
                                <span class="block text-xs text-neutral-400">{{ $step['category']['count'] }} {{ $step['category']['count'] === 1 ? 'producto' : 'productos' }} en {{ $step['category']['name'] }}</span>
                            @endif
                        </span>
                        @if ($step['category'])
                            <span class="material-symbols-outlined text-neutral-500 transition group-hover:translate-x-1 group-hover:text-[#f5a623]" aria-hidden="true">{{ $arrow }}</span>
                        @endif
                    </{{ $step['category'] ? 'a' : 'div' }}>
                @endif
            @endforeach
        </div>
    @endif
</section>
