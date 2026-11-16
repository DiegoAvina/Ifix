@extends('layouts.app')

@section('title', 'Nosotros: misión, visión y valores | IFIX')
@section('meta_description', \Illuminate\Support\Str::limit($page->intro ?: $page->mission ?: $page->headline, 155))

{{--
    Página "Nosotros". Todo el contenido se edita en el panel de Lunar
    (Tienda en línea → Nosotros) y es texto plano: se imprime escapado.
--}}

@push('styles')
<style>
    /* ---------- Movimiento de entrada (solo si Alpine arrancó) ---------- */
    .about-anim .reveal { opacity: 0; transform: translateY(28px); transition: opacity .9s cubic-bezier(.2,.7,.2,1), transform .9s cubic-bezier(.2,.7,.2,1); transition-delay: var(--d, 0ms); }
    .about-anim .reveal.is-visible { opacity: 1; transform: none; }

    .about-word { display: inline-block; opacity: 0; transform: translateY(.6em) rotate(4deg); filter: blur(8px); animation: about-word 1s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: var(--d); }
    @keyframes about-word { to { opacity: 1; transform: none; filter: none; } }

    /* ---------- Fondo del hero ---------- */
    .about-grid { background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px); background-size: 64px 64px; mask-image: radial-gradient(ellipse at 50% 40%, #000 30%, transparent 75%); }
    .about-orb { position: absolute; border-radius: 9999px; filter: blur(80px); animation: about-float 14s ease-in-out infinite alternate; }
    @keyframes about-float { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(6%, -8%) scale(1.15); } }

    .about-gradient-text { background: linear-gradient(100deg, #fff 10%, #f5a623 45%, #ffd27a 60%, #fff 90%); background-size: 220% auto; -webkit-background-clip: text; background-clip: text; color: transparent; animation: about-shine 8s linear infinite; }
    @keyframes about-shine { to { background-position: -220% center; } }

    /* ---------- Tarjetas con luz que sigue al cursor ---------- */
    .about-spot { --x: 50%; --y: 50%; position: relative; isolation: isolate; }
    .about-spot::before { content: ""; position: absolute; inset: 0; border-radius: inherit; z-index: -1; opacity: 0; transition: opacity .4s; background: radial-gradient(420px circle at var(--x) var(--y), rgba(245,166,35,.16), transparent 60%); }
    .about-spot:hover::before { opacity: 1; }
    .about-spot::after { content: ""; position: absolute; inset: 0; border-radius: inherit; padding: 1px; pointer-events: none; background: radial-gradient(300px circle at var(--x) var(--y), rgba(245,166,35,.8), transparent 60%); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; opacity: 0; transition: opacity .4s; }
    .about-spot:hover::after { opacity: 1; }

    .about-outline { -webkit-text-stroke: 1.5px currentColor; color: transparent; }

    /* ---------- Cinta de valores ---------- */
    .about-marquee { animation: about-marquee 32s linear infinite; }
    @keyframes about-marquee { to { transform: translateX(-50%); } }

    /* ---------- Anillos de la misión ---------- */
    .about-ring { animation: about-spin 24s linear infinite; }
    .about-ring--rev { animation-direction: reverse; animation-duration: 36s; }
    @keyframes about-spin { to { transform: rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
        .about-anim .reveal, .about-word { opacity: 1 !important; transform: none !important; filter: none !important; animation: none !important; transition: none !important; }
        .about-orb, .about-gradient-text, .about-marquee, .about-ring { animation: none !important; }
    }
</style>
@endpush

@section('content')
@php
    $words = preg_split('/\s+/', trim($page->headline));
    $spot = '@mousemove="const r = $el.getBoundingClientRect(); $el.style.setProperty(\'--x\', ($event.clientX - r.left) + \'px\'); $el.style.setProperty(\'--y\', ($event.clientY - r.top) + \'px\')"';
    // Clases completas (no armadas con variables) para que Tailwind las incluya.
    $statCols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][count($stats)] ?? 'md:grid-cols-4';
    $reveal = 'x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { $el.classList.add(\'is-visible\'); o.disconnect() } }, { threshold: .15 }).observe($el)"';
@endphp

<div x-data x-init="$el.classList.add('about-anim')" class="overflow-hidden bg-[#f5f5f3] text-neutral-900 dark:bg-[#0b0c0e] dark:text-white">

    {{-- ======================= HERO ======================= --}}
    <section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-[#0b0c0e] text-white">
        <div class="about-grid absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="about-orb -z-10 left-[-10%] top-[10%] h-[28rem] w-[28rem] bg-[#f5a623]/30" aria-hidden="true"></div>
        <div class="about-orb -z-10 bottom-[-15%] right-[-5%] h-[32rem] w-[32rem] bg-[#bd7710]/25 [animation-delay:-7s]" aria-hidden="true"></div>

        <div class="mx-auto w-full max-w-[1340px] px-4 py-24 sm:px-6 lg:px-8">
            @if ($page->eyebrow)
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-extrabold uppercase tracking-[0.25em] text-[#f5a623] backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#f5a623]" aria-hidden="true"></span>
                    {{ $page->eyebrow }}
                </p>
            @endif

            <h1 class="mt-8 max-w-5xl font-heading text-[clamp(2.5rem,7vw,6.5rem)] font-black leading-[0.95] tracking-tight">
                @foreach ($words as $i => $word)
                    <span class="about-word {{ $loop->last ? 'about-gradient-text' : '' }}" style="--d: {{ 120 + $i * 90 }}ms">{{ $word }}</span>{{ $loop->last ? '' : ' ' }}
                @endforeach
            </h1>

            @if ($page->intro)
                <p class="reveal mt-8 max-w-2xl text-lg leading-relaxed text-neutral-300 sm:text-xl" style="--d: 500ms" {!! $reveal !!}>{{ $page->intro }}</p>
            @endif

            <a href="#mision" class="reveal mt-14 inline-flex items-center gap-3 text-sm font-semibold uppercase tracking-[0.2em] text-neutral-400 transition hover:text-[#f5a623]" style="--d: 700ms" {!! $reveal !!}>
                <span class="flex h-11 w-11 items-center justify-center rounded-full border border-white/20">
                    <span class="material-symbols-outlined animate-bounce" aria-hidden="true">south</span>
                </span>
                Descubre quiénes somos
            </a>
        </div>
    </section>

    {{-- ======================= CINTA DE VALORES ======================= --}}
    @if (count($values))
        <div class="relative -mt-px overflow-hidden border-y border-[#f5a623]/40 bg-[#f5a623] py-4 text-[#0b0c0e]" aria-hidden="true">
            <div class="about-marquee flex w-max gap-10 whitespace-nowrap font-heading text-2xl font-black uppercase tracking-tight sm:text-3xl">
                @for ($copy = 0; $copy < 2; $copy++)
                    @for ($n = 0; $n < max(1, (int) ceil(8 / count($values))); $n++)
                        @foreach ($values as $value)
                            <span class="flex items-center gap-10">{{ $value['title'] }} <span class="material-symbols-outlined text-3xl">{{ $value['icon'] }}</span></span>
                        @endforeach
                    @endfor
                @endfor
            </div>
        </div>
    @endif

    {{-- ======================= MISIÓN Y VISIÓN ======================= --}}
    @if ($page->mission || $page->vision)
        <section id="mision" class="scroll-mt-28 py-24 sm:py-32">
            <div class="mx-auto grid max-w-[1340px] gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">

                @if ($page->mission)
                    <article class="reveal about-spot group overflow-hidden rounded-[2rem] border border-neutral-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.03] sm:p-12" {!! $spot !!} {!! $reveal !!}>
                        <div class="relative mb-10 h-28 w-28" aria-hidden="true">
                            <div class="about-ring absolute inset-0 rounded-full border-2 border-dashed border-[#f5a623]/60"></div>
                            <div class="about-ring about-ring--rev absolute inset-3 rounded-full border border-[#f5a623]/30"></div>
                            <div class="absolute inset-7 flex items-center justify-center rounded-full bg-[#f5a623] text-[#0b0c0e] shadow-[0_0_40px_rgba(245,166,35,.5)]">
                                <span class="material-symbols-outlined text-3xl">target</span>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 class="font-heading text-4xl font-black tracking-tight sm:text-5xl">Misión</h2>
                            <span class="about-outline font-heading text-6xl font-black text-neutral-300 dark:text-white/15 sm:text-7xl" aria-hidden="true">01</span>
                        </div>
                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.25em] text-[#bd7710] dark:text-[#f5a623]">Lo que hacemos hoy</p>
                        <p class="mt-6 whitespace-pre-line text-lg leading-relaxed text-neutral-600 dark:text-neutral-300">{{ $page->mission }}</p>
                    </article>
                @endif

                @if ($page->vision)
                    <article class="reveal about-spot group overflow-hidden rounded-[2rem] border border-neutral-800 bg-[#0b0c0e] p-8 text-white shadow-sm dark:border-[#f5a623]/25 dark:bg-gradient-to-br dark:from-[#1a1407] dark:to-[#0b0c0e] sm:p-12" style="--d: 150ms" {!! $spot !!} {!! $reveal !!}>
                        <div class="relative mb-10 flex h-28 w-28 items-center justify-center" aria-hidden="true">
                            <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-[#f5a623] via-[#ffd27a] to-transparent opacity-80 blur-xl transition duration-700 group-hover:scale-125"></div>
                            <div class="relative flex h-16 w-16 items-center justify-center rounded-full border border-white/20 bg-[#0b0c0e]/80 backdrop-blur">
                                <span class="material-symbols-outlined text-3xl text-[#f5a623]">visibility</span>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 class="font-heading text-4xl font-black tracking-tight sm:text-5xl">Visión</h2>
                            <span class="about-outline font-heading text-6xl font-black text-white/15 sm:text-7xl" aria-hidden="true">02</span>
                        </div>
                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.25em] text-[#f5a623]">A dónde vamos</p>
                        <p class="mt-6 whitespace-pre-line text-lg leading-relaxed text-neutral-300">{{ $page->vision }}</p>
                    </article>
                @endif

            </div>
        </section>
    @endif

    {{-- ======================= VALORES ======================= --}}
    @if (count($values))
        <section class="pb-24 sm:pb-32" aria-labelledby="valores-titulo">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                <div class="reveal flex flex-col justify-between gap-6 md:flex-row md:items-end" {!! $reveal !!}>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-[#bd7710] dark:text-[#f5a623]">Lo que nos mueve</p>
                        <h2 id="valores-titulo" class="mt-3 font-heading text-4xl font-black tracking-tight sm:text-6xl">Nuestros valores</h2>
                    </div>
                    <p class="max-w-sm text-neutral-500 dark:text-neutral-400">Los principios detrás de cada producto que elegimos y de cada pedido que enviamos.</p>
                </div>

                <ul class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
                    @foreach ($values as $value)
                        @php
                            // Mosaico: en escritorio alterna tarjetas anchas y angostas.
                            $wide = in_array($loop->index % 4, [0, 3], true);
                        @endphp
                        <li class="reveal about-spot group flex min-h-[15rem] flex-col justify-between overflow-hidden rounded-[1.75rem] border border-neutral-200 bg-white p-7 transition duration-500 hover:-translate-y-1 dark:border-white/10 dark:bg-white/[0.03] {{ $wide ? 'lg:col-span-7' : 'lg:col-span-5' }}"
                            style="--d: {{ ($loop->index % 4) * 100 }}ms" {!! $spot !!} {!! $reveal !!}>
                            <div class="flex items-start justify-between">
                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5a623]/10 text-[#bd7710] ring-1 ring-[#f5a623]/30 transition duration-500 group-hover:rotate-[-8deg] group-hover:scale-110 group-hover:bg-[#f5a623] group-hover:text-[#0b0c0e] dark:text-[#f5a623]">
                                    <span class="material-symbols-outlined text-3xl" aria-hidden="true">{{ $value['icon'] }}</span>
                                </span>
                                <span class="font-heading text-sm font-black tabular-nums text-neutral-300 dark:text-white/20" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="mt-10">
                                <h3 class="font-heading text-2xl font-black tracking-tight sm:text-3xl">{{ $value['title'] }}</h3>
                                @if ($value['description'])
                                    <p class="mt-3 max-w-md leading-relaxed text-neutral-500 dark:text-neutral-400">{{ $value['description'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ======================= CIFRAS ======================= --}}
    @if (count($stats))
        <section class="border-y border-neutral-200 bg-white py-20 dark:border-white/10 dark:bg-white/[0.02]" aria-label="IFIX en cifras">
            <dl class="mx-auto grid max-w-[1340px] grid-cols-2 gap-y-12 px-4 sm:px-6 {{ $statCols }} lg:px-8">
                @foreach ($stats as $stat)
                    <div class="reveal flex flex-col text-center" style="--d: {{ $loop->index * 100 }}ms" {!! $reveal !!}>
                        <dt class="order-2 mt-2 text-sm font-semibold uppercase tracking-[0.15em] text-neutral-500">{{ $stat['label'] }}</dt>
                        <dd class="order-1 font-heading text-5xl font-black tracking-tight text-[#bd7710] dark:text-[#f5a623] sm:text-6xl">{{ $stat['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    {{-- ======================= HISTORIA ======================= --}}
    @if (count($milestones))
        <section class="py-24 sm:py-32" aria-labelledby="historia-titulo">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <h2 id="historia-titulo" class="reveal text-center font-heading text-4xl font-black tracking-tight sm:text-6xl" {!! $reveal !!}>Nuestra historia</h2>

                <ol class="relative mt-16 space-y-12 before:absolute before:bottom-0 before:left-[1.1rem] before:top-0 before:w-px before:bg-gradient-to-b before:from-[#f5a623] before:via-[#f5a623]/40 before:to-transparent md:before:left-1/2">
                    @foreach ($milestones as $milestone)
                        <li class="reveal relative pl-14 md:w-1/2 md:pl-0 {{ $loop->odd ? 'md:pr-14 md:text-right' : 'md:ml-auto md:pl-14' }}" {!! $reveal !!}>
                            <span class="absolute left-2 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#f5a623] shadow-[0_0_0_6px_rgba(245,166,35,.15)] {{ $loop->odd ? 'md:left-auto md:-right-2.5' : 'md:-left-2.5' }}" aria-hidden="true"></span>
                            @if (filled($milestone['year'] ?? null))
                                <p class="font-heading text-sm font-black uppercase tracking-[0.2em] text-[#bd7710] dark:text-[#f5a623]">{{ $milestone['year'] }}</p>
                            @endif
                            <h3 class="mt-1 font-heading text-2xl font-black tracking-tight">{{ $milestone['title'] }}</h3>
                            @if (filled($milestone['text'] ?? null))
                                <p class="mt-2 leading-relaxed text-neutral-500 dark:text-neutral-400">{{ $milestone['text'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ======================= CIERRE ======================= --}}
    @if ($page->closing_title)
        <section class="px-4 pb-24 sm:px-6 lg:px-8">
            <div class="reveal relative isolate mx-auto max-w-[1340px] overflow-hidden rounded-[2.5rem] bg-[#0b0c0e] px-6 py-20 text-center text-white sm:px-16" {!! $reveal !!}>
                <div class="about-grid absolute inset-0 -z-10" aria-hidden="true"></div>
                <div class="about-orb -z-10 inset-0 m-auto h-80 w-80 bg-[#f5a623]/25" aria-hidden="true"></div>
                <h2 class="mx-auto max-w-3xl font-heading text-4xl font-black tracking-tight sm:text-6xl">{{ $page->closing_title }}</h2>
                @if ($page->closing_text)
                    <p class="mx-auto mt-5 max-w-xl text-lg text-neutral-300">{{ $page->closing_text }}</p>
                @endif
                <a href="{{ route('catalog.index') }}" class="group mt-10 inline-flex items-center gap-3 rounded-full bg-[#f5a623] px-8 py-4 font-heading text-sm font-black uppercase tracking-[0.15em] text-[#0b0c0e] shadow-[0_10px_40px_rgba(245,166,35,.35)] transition hover:scale-105 hover:bg-[#ffd27a]">
                    Ver productos
                    <span class="material-symbols-outlined transition group-hover:translate-x-1" aria-hidden="true">arrow_forward</span>
                </a>
            </div>
        </section>
    @endif

</div>
@endsection
