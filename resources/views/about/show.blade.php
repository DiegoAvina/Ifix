@extends('layouts.app')

@section('title', 'Nosotros: misión, visión y valores | IFIX')
@section('meta_description', \Illuminate\Support\Str::limit($page->intro ?: $page->mission ?: $page->headline, 155))

{{--
    Página "Nosotros". El contenido se edita en el panel de Lunar
    (Tienda en línea → Nosotros) y es texto plano: se imprime escapado.
    Marcas, categorías y número de productos salen del catálogo real.

    La página es un detallado completo: el hero se pule con el cursor,
    la misión tiene efecto hidrofóbico (gotas), la visión es acabado espejo,
    el proceso va de mate a brillante y los valores son etiquetas de producto.
--}}

@push('styles')
<style>
    /* ---------- Entrada al hacer scroll (solo si Alpine arrancó) ---------- */
    .about-anim .reveal { opacity: 0; transform: translateY(28px); transition: opacity .9s cubic-bezier(.2,.7,.2,1), transform .9s cubic-bezier(.2,.7,.2,1); transition-delay: var(--d, 0ms); }
    .about-anim .reveal.is-visible { opacity: 1; transform: none; }

    .about-word { display: inline-block; opacity: 0; transform: translateY(.6em); filter: blur(8px); animation: about-word 1s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: var(--d); }
    @keyframes about-word { to { opacity: 1; transform: none; filter: none; } }

    .about-shine-text { background: linear-gradient(100deg, #fff 10%, #f5a623 45%, #ffe2a8 55%, #fff 90%); background-size: 220% auto; -webkit-background-clip: text; background-clip: text; color: transparent; animation: about-shine 7s linear infinite; }
    @keyframes about-shine { to { background-position: -220% center; } }

    /* ---------- Hero: pintura opaca con marcas de swirl ---------- */
    .about-dull { filter: saturate(.35) brightness(.5) contrast(.8) blur(1.5px); transform: scale(1.02); }
    .about-swirls {
        background:
            repeating-radial-gradient(circle at 68% 58%, rgba(255,255,255,.07) 0 1px, transparent 1px 7px),
            repeating-radial-gradient(circle at 88% 30%, rgba(255,255,255,.06) 0 1px, transparent 1px 9px),
            repeating-radial-gradient(circle at 45% 85%, rgba(255,255,255,.05) 0 1px, transparent 1px 8px),
            linear-gradient(rgba(190,190,190,.10), rgba(190,190,190,.10));
        mix-blend-mode: screen;
    }
    .about-polished { filter: saturate(1.2) contrast(1.08) brightness(1.06); }
    .about-pad { background: radial-gradient(circle, #111 0 28%, #2a2a2a 29% 34%, #f3e7a1 35% 70%, #d9c66a 71% 100%); box-shadow: 0 0 0 3px rgba(0,0,0,.35), 0 10px 30px rgba(0,0,0,.5), 0 0 40px rgba(245,166,35,.35); }
    .about-pad::after { content: ""; position: absolute; inset: 0; border-radius: 9999px; background: conic-gradient(from 0deg, transparent 0 20%, rgba(255,255,255,.55) 25%, transparent 30% 70%, rgba(255,255,255,.4) 75%, transparent 80%); animation: about-spin .35s linear infinite; }

    /* ---------- Cinta de enmascarar ---------- */
    .about-tape {
        background: repeating-linear-gradient(90deg, rgba(0,0,0,.035) 0 2px, transparent 2px 5px), #f2c14e;
        clip-path: polygon(0 8%, 1% 0, 99% 4%, 100% 0, 100% 92%, 99% 100%, 1% 96%, 0 100%);
    }
    .about-marquee { animation: about-marquee 36s linear infinite; }
    @keyframes about-marquee { to { transform: translateX(-50%); } }

    /* ---------- Misión: pintura con efecto hidrofóbico ---------- */
    .about-paint { background: radial-gradient(120% 80% at 20% 0%, #2c3b52 0%, #142033 45%, #0a111c 100%); }
    .about-paint::before { content: ""; position: absolute; inset: 0; background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.08) 45%, transparent 60%); pointer-events: none; }
    .about-drop {
        position: absolute; border-radius: 50% 50% 48% 52% / 55% 55% 45% 45%;
        background: radial-gradient(circle at 34% 28%, rgba(255,255,255,.95) 0 9%, rgba(255,255,255,.22) 22%, rgba(160,190,230,.08) 55%, rgba(255,255,255,.18) 100%);
        box-shadow: inset -2px -3px 5px rgba(255,255,255,.18), inset 2px 2px 4px rgba(0,0,0,.25), 2px 5px 7px rgba(0,0,0,.45);
        transition: transform 1.4s cubic-bezier(.5,0,.75,0), opacity 1.4s ease-in;
        transition-delay: var(--dd);
    }
    .about-paint:hover .about-drop { transform: translateY(var(--fall)) scaleY(1.25); opacity: 0; }

    /* ---------- Visión: acabado espejo ---------- */
    .about-mirror { background: linear-gradient(160deg, #2a2112 0%, #0b0c0e 55%, #15120c 100%); }
    .about-sweep::before { content: ""; position: absolute; inset: -50% auto -50% -60%; width: 40%; transform: rotate(20deg); background: linear-gradient(90deg, transparent, rgba(255,226,168,.22), transparent); animation: about-sweep 5s ease-in-out infinite; pointer-events: none; }
    @keyframes about-sweep { 0%, 35% { left: -60%; } 75%, 100% { left: 130%; } }
    .about-reflect { transform: scaleY(-1); -webkit-mask-image: linear-gradient(to top, rgba(0,0,0,.35), transparent 70%); mask-image: linear-gradient(to top, rgba(0,0,0,.35), transparent 70%); }

    /* ---------- Proceso: de mate a espejo ---------- */
    .about-swatch { background: radial-gradient(circle at 35% 30%, rgba(255,255,255,var(--gloss)) 0 12%, transparent 45%), radial-gradient(circle at 50% 50%, #3a2a0c, #0b0c0e 75%); box-shadow: inset 0 0 0 1px rgba(255,255,255,.08), 0 0 calc(var(--gloss) * 40px) rgba(245,166,35,calc(var(--gloss) * .6)); }
    .about-track { background: linear-gradient(90deg, #4a4d52, #8a6a2a 50%, #f5a623 80%, #fff3d6); }

    /* ---------- Valores: etiqueta de producto ---------- */
    .about-label { transform: perspective(900px) rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)); transition: transform .25s ease-out, box-shadow .3s; transform-style: preserve-3d; }
    .about-label::after { content: ""; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; background: linear-gradient(115deg, transparent 35%, rgba(255,255,255,.55) 48%, transparent 60%); background-size: 250% 100%; background-position: 120% 0; transition: background-position .9s ease; }
    .about-label:hover::after { background-position: -20% 0; }

    .about-outline { -webkit-text-stroke: 1.5px currentColor; color: transparent; }
    .about-spin { animation: about-spin 22s linear infinite; }
    @keyframes about-spin { to { transform: rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
        .about-anim .reveal, .about-word { opacity: 1 !important; transform: none !important; filter: none !important; animation: none !important; transition: none !important; }
        .about-shine-text, .about-marquee, .about-spin, .about-sweep::before, .about-pad::after { animation: none !important; }
        .about-drop, .about-label { transition: none !important; }
    }
</style>
@endpush

@section('content')
@php
    $words = preg_split('/\s+/', trim($page->headline));
    $reveal = 'x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { $el.classList.add(\'is-visible\'); o.disconnect() } }, { threshold: .15 }).observe($el)"';
    // Clases completas (no armadas con variables) para que Tailwind las incluya.
    $statCols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][count($stats)] ?? 'md:grid-cols-4';
    $processCols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5'][count($process)] ?? 'lg:grid-cols-6';

    // Gotas de agua del efecto hidrofóbico: posiciones fijas (mismo resultado en cada visita).
    mt_srand(9);
    $drops = [];
    for ($i = 0; $i < 26; $i++) {
        $size = mt_rand(6, 30);
        $drops[] = ['x' => mt_rand(2, 96), 'y' => mt_rand(4, 92), 'w' => $size, 'h' => (int) round($size * mt_rand(85, 105) / 100), 'delay' => mt_rand(0, 500), 'fall' => mt_rand(60, 220)];
    }
    mt_srand();

    // Código de barras decorativo de cada etiqueta, derivado del nombre del valor.
    $barcode = function (string $seed): array {
        $hash = md5($seed);
        $bars = [];
        $x = 0;
        foreach (str_split($hash) as $i => $char) {
            $w = 1 + hexdec($char) % 3;
            if ($i % 2 === 0) {
                $bars[] = ['x' => $x, 'w' => $w];
            }
            $x += $w + 1;
        }

        return ['bars' => $bars, 'width' => $x];
    };
@endphp

<div x-data x-init="$el.classList.add('about-anim')" class="overflow-hidden bg-[#f5f5f3] text-neutral-900 dark:bg-[#0b0c0e] dark:text-white">

    {{-- =================== HERO: PULE LA PINTURA =================== --}}
    <section
        x-data="polishReveal({ src: @js(asset('images/hero-detailing.jpg')) })"
        @pointermove="move($event)"
        @pointerdown="move($event)"
        @pointerleave="leave()"
        @pointerup="$event.pointerType !== 'mouse' && leave()"
        :class="done ? '' : 'cursor-none'"
        class="relative isolate min-h-[92vh] touch-pan-y select-none overflow-hidden bg-[#0b0c0e] text-white"
        aria-label="Presentación"
    >
        <img src="{{ asset('images/hero-detailing.jpg') }}" alt="" class="about-dull absolute inset-0 -z-20 h-full w-full object-cover object-[70%_center]" aria-hidden="true">
        <div class="about-swirls absolute inset-0 -z-20" aria-hidden="true"></div>
        <canvas x-ref="canvas" class="about-polished absolute inset-0 -z-10 h-full w-full" aria-hidden="true"></canvas>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-[#0b0c0e] via-[#0b0c0e]/75 to-transparent" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 bg-gradient-to-t from-[#0b0c0e] to-transparent" aria-hidden="true"></div>

        {{-- Pad de pulir que sigue al cursor --}}
        <div
            x-show="padVisible"
            x-cloak
            class="about-pad pointer-events-none absolute left-0 top-0 h-24 w-24 rounded-full"
            :style="`transform: translate(${padX - 48}px, ${padY - 48}px)`"
            aria-hidden="true"
        ></div>

        {{-- Medidor de corrección --}}
        <div class="pointer-events-none absolute right-4 top-6 w-56 rounded-2xl border border-white/10 bg-black/40 p-4 backdrop-blur-md sm:right-8 sm:top-8" aria-hidden="true">
            <div class="flex items-center justify-between text-[11px] font-extrabold uppercase tracking-[0.2em]">
                <span class="text-neutral-400" x-text="done ? 'Acabado espejo' : 'Corrección de pintura'">Corrección de pintura</span>
                <span class="tabular-nums text-[#f5a623]" x-text="progress + '%'">0%</span>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10">
                <div class="h-full rounded-full bg-gradient-to-r from-[#bd7710] via-[#f5a623] to-[#fff3d6] transition-[width] duration-300" :style="`width: ${progress}%`"></div>
            </div>
            <p class="mt-3 text-xs text-neutral-400" x-text="done ? '¡Así se ve un auto tratado con IFIX!' : 'Pasa el cursor sobre la foto para pulirla.'">Pasa el cursor sobre la foto para pulirla.</p>
        </div>

        <div class="pointer-events-none mx-auto flex min-h-[92vh] w-full max-w-[1340px] flex-col justify-end px-4 pb-16 pt-40 sm:px-6 lg:px-8">
            @if ($page->eyebrow)
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-extrabold uppercase tracking-[0.25em] text-[#f5a623] backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#f5a623]" aria-hidden="true"></span>
                    {{ $page->eyebrow }}
                </p>
            @endif

            <h1 class="mt-6 max-w-4xl font-heading text-[clamp(2.5rem,6.5vw,6rem)] font-black leading-[0.95] tracking-tight">
                @foreach ($words as $i => $word)
                    <span class="about-word {{ $loop->last ? 'about-shine-text' : '' }}" style="--d: {{ 120 + $i * 90 }}ms">{{ $word }}</span>{{ $loop->last ? '' : ' ' }}
                @endforeach
            </h1>

            @if ($page->intro)
                <p class="reveal mt-6 max-w-xl text-lg leading-relaxed text-neutral-300" style="--d: 500ms" {!! $reveal !!}>{{ $page->intro }}</p>
            @endif

            {{-- Datos reales del catálogo --}}
            <dl class="reveal mt-10 flex flex-wrap gap-3" style="--d: 700ms" {!! $reveal !!}>
                @foreach ([['products', 'productos', 'inventory_2'], ['categories', 'categorías', 'category'], ['brands', 'marcas', 'verified']] as [$key, $label, $icon])
                    @if ($shelf[$key] > 0)
                        <div class="flex items-center gap-2 rounded-full border border-white/10 bg-black/40 px-4 py-2 backdrop-blur">
                            <span class="material-symbols-outlined order-first text-lg text-[#f5a623]" aria-hidden="true">{{ $icon }}</span>
                            <dt class="order-2 text-sm text-neutral-400">{{ $label }}</dt>
                            <dd class="order-1 font-heading font-black tabular-nums">{{ $shelf[$key] }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>
    </section>

    {{-- =================== CINTA DE ENMASCARAR =================== --}}
    @if (count($values))
        <div class="relative z-10 -mt-6 -rotate-[1.5deg] px-2" aria-hidden="true">
            <div class="about-tape overflow-hidden py-3 text-[#3a2a06] shadow-[0_8px_20px_rgba(0,0,0,.35)]">
                <div class="about-marquee flex w-max gap-8 whitespace-nowrap font-heading text-xl font-black uppercase tracking-tight sm:text-2xl">
                    @for ($copy = 0; $copy < 2; $copy++)
                        @for ($n = 0; $n < max(1, (int) ceil(8 / count($values))); $n++)
                            @foreach ($values as $value)
                                <span class="flex items-center gap-8">{{ $value['title'] }} <span class="text-[#3a2a06]/40">✦</span></span>
                            @endforeach
                        @endfor
                    @endfor
                </div>
            </div>
        </div>
    @endif

    {{-- =================== MISIÓN Y VISIÓN =================== --}}
    @if ($page->mission || $page->vision)
        <section id="mision" class="scroll-mt-28 py-24 sm:py-32">
            <div class="mx-auto grid max-w-[1340px] gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">

                @if ($page->mission)
                    {{-- Pintura recién protegida: el agua forma gotas y escurre al pasar el cursor --}}
                    <article class="reveal about-paint relative isolate overflow-hidden rounded-[2rem] p-8 text-white shadow-xl sm:p-12" {!! $reveal !!}>
                        <div class="absolute inset-0 -z-10" aria-hidden="true">
                            @foreach ($drops as $drop)
                                <span class="about-drop" style="left: {{ $drop['x'] }}%; top: {{ $drop['y'] }}%; width: {{ $drop['w'] }}px; height: {{ $drop['h'] }}px; --dd: {{ $drop['delay'] }}ms; --fall: {{ $drop['fall'] }}px"></span>
                            @endforeach
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-black/30 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.2em] text-sky-200 backdrop-blur">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">water_drop</span>
                                Efecto hidrofóbico
                            </span>
                            <span class="about-outline font-heading text-6xl font-black text-white/20 sm:text-7xl" aria-hidden="true">01</span>
                        </div>
                        <h2 class="mt-16 font-heading text-5xl font-black tracking-tight sm:text-6xl">Misión</h2>
                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.25em] text-[#f5a623]">Lo que hacemos hoy</p>
                        <p class="mt-6 max-w-lg whitespace-pre-line rounded-2xl bg-black/25 p-5 text-lg leading-relaxed text-neutral-100 backdrop-blur-sm">{{ $page->mission }}</p>
                    </article>
                @endif

                @if ($page->vision)
                    {{-- Acabado espejo: destello que recorre la tarjeta y el título se refleja --}}
                    <article class="reveal about-mirror about-sweep relative isolate overflow-hidden rounded-[2rem] border border-[#f5a623]/25 p-8 text-white shadow-xl sm:p-12" style="--d: 150ms" {!! $reveal !!}>
                        <div class="about-spin absolute -right-24 -top-24 -z-10 h-72 w-72 rounded-full bg-[conic-gradient(from_0deg,transparent,rgba(245,166,35,.35),transparent_40%)] blur-2xl" aria-hidden="true"></div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#f5a623]/30 bg-[#f5a623]/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#ffe2a8]">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">flare</span>
                                Acabado espejo
                            </span>
                            <span class="about-outline font-heading text-6xl font-black text-[#f5a623]/25 sm:text-7xl" aria-hidden="true">02</span>
                        </div>
                        <div class="mt-16">
                            <h2 class="about-shine-text font-heading text-5xl font-black tracking-tight sm:text-6xl">Visión</h2>
                            <p class="about-reflect -mt-3 h-8 overflow-hidden font-heading text-5xl font-black leading-none tracking-tight text-[#f5a623] sm:text-6xl" aria-hidden="true">Visión</p>
                        </div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-[#f5a623]">A dónde vamos</p>
                        <p class="mt-6 max-w-lg whitespace-pre-line text-lg leading-relaxed text-neutral-300">{{ $page->vision }}</p>
                    </article>
                @endif

            </div>
        </section>
    @endif

    {{-- =================== PROCESO: DE MATE A ESPEJO =================== --}}
    @if (count($process))
        <section class="pb-24 sm:pb-32" aria-labelledby="proceso-titulo">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                <div class="reveal max-w-2xl" {!! $reveal !!}>
                    <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-[#bd7710] dark:text-[#f5a623]">Lo que encuentras aquí</p>
                    <h2 id="proceso-titulo" class="mt-3 font-heading text-4xl font-black tracking-tight sm:text-6xl">Del lavado al acabado espejo</h2>
                    <p class="mt-4 text-neutral-500 dark:text-neutral-400">Todo el proceso de detallado, paso a paso, con los productos para cada etapa.</p>
                </div>

                <div class="reveal mt-12 hidden items-center gap-4 text-[11px] font-extrabold uppercase tracking-[0.2em] text-neutral-500 lg:flex" {!! $reveal !!}>
                    <span>Mate</span>
                    <div class="about-track h-1.5 flex-1 rounded-full"></div>
                    <span class="text-[#bd7710] dark:text-[#f5a623]">Espejo</span>
                </div>

                <ol class="mt-8 grid gap-4 sm:grid-cols-2 {{ $processCols }}">
                    @foreach ($process as $step)
                        @php $gloss = count($process) > 1 ? round(0.1 + 0.9 * $loop->index / (count($process) - 1), 2) : 1; @endphp
                        <li class="reveal group relative flex flex-col rounded-[1.75rem] border border-neutral-200 bg-white p-6 transition duration-500 hover:-translate-y-1 hover:border-[#f5a623]/50 dark:border-white/10 dark:bg-white/[0.03]" style="--d: {{ $loop->index * 110 }}ms; --gloss: {{ $gloss }}" {!! $reveal !!}>
                            <div class="flex items-center justify-between">
                                <span class="about-swatch flex h-14 w-14 items-center justify-center rounded-full text-[#f5a623]">
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">{{ $step['category']['icon'] ?? 'auto_awesome' }}</span>
                                </span>
                                <span class="font-heading text-4xl font-black text-neutral-200 dark:text-white/10" aria-hidden="true">{{ $loop->iteration }}</span>
                            </div>
                            <h3 class="mt-6 font-heading text-2xl font-black tracking-tight">{{ $step['title'] }}</h3>
                            @if ($step['text'])
                                <p class="mt-2 flex-1 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">{{ $step['text'] }}</p>
                            @endif
                            @if ($step['category'])
                                <a href="{{ $step['category']['url'] }}" class="mt-6 inline-flex items-center gap-1 text-sm font-bold text-[#bd7710] transition hover:gap-2 dark:text-[#f5a623]">
                                    {{ $step['category']['count'] }} {{ $step['category']['count'] === 1 ? 'producto' : 'productos' }} en {{ $step['category']['name'] }}
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- =================== VALORES: NUESTRA FÓRMULA =================== --}}
    @if (count($values))
        <section class="border-y border-neutral-200 bg-white py-24 dark:border-white/10 dark:bg-white/[0.02] sm:py-32" aria-labelledby="valores-titulo">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                <div class="reveal flex flex-col justify-between gap-6 md:flex-row md:items-end" {!! $reveal !!}>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-[#bd7710] dark:text-[#f5a623]">Nuestros valores</p>
                        <h2 id="valores-titulo" class="mt-3 font-heading text-4xl font-black tracking-tight sm:text-6xl">La fórmula IFIX</h2>
                    </div>
                    <p class="max-w-sm text-neutral-500 dark:text-neutral-400">Los ingredientes que van en cada producto que elegimos y en cada pedido que enviamos.</p>
                </div>

                <ul class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($values as $value)
                        @php $code = $barcode($value['title']); @endphp
                        <li class="reveal" style="--d: {{ ($loop->index % 4) * 100 }}ms" {!! $reveal !!}>
                            <article
                                class="about-label relative flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-black/10 bg-[#fbfaf6] text-[#0b0c0e] shadow-[0_20px_40px_-20px_rgba(0,0,0,.35)] hover:shadow-[0_30px_60px_-20px_rgba(245,166,35,.45)]"
                                @mousemove="const r = $el.getBoundingClientRect(); $el.style.setProperty('--ry', (($event.clientX - r.left) / r.width - .5) * 14 + 'deg'); $el.style.setProperty('--rx', (.5 - ($event.clientY - r.top) / r.height) * 14 + 'deg')"
                                @mouseleave="$el.style.setProperty('--rx', '0deg'); $el.style.setProperty('--ry', '0deg')"
                            >
                                <header class="flex items-center justify-between bg-[#0b0c0e] px-5 py-3 text-[11px] font-extrabold uppercase tracking-[0.2em]">
                                    <span class="text-[#f5a623]">IFIX</span>
                                    <span class="text-white/60">Fórmula {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                </header>
                                <div class="h-1.5 bg-gradient-to-r from-[#bd7710] via-[#f5a623] to-[#ffe2a8]" aria-hidden="true"></div>

                                <div class="flex flex-1 flex-col p-6">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f5a623] text-[#0b0c0e]">
                                        <span class="material-symbols-outlined text-2xl" aria-hidden="true">{{ $value['icon'] }}</span>
                                    </span>
                                    <h3 class="mt-5 font-heading text-3xl font-black uppercase leading-none tracking-tight">{{ $value['title'] }}</h3>
                                    @if ($value['description'])
                                        <p class="mt-4 text-[10px] font-extrabold uppercase tracking-[0.2em] text-neutral-400">Modo de acción</p>
                                        <p class="mt-1 flex-1 text-sm leading-relaxed text-neutral-600">{{ $value['description'] }}</p>
                                    @endif
                                </div>

                                <footer class="flex items-end justify-between gap-4 border-t border-dashed border-black/15 px-6 py-4" aria-hidden="true">
                                    <svg viewBox="0 0 {{ $code['width'] }} 30" class="h-8 w-28" preserveAspectRatio="none">
                                        @foreach ($code['bars'] as $bar)
                                            <rect x="{{ $bar['x'] }}" y="0" width="{{ $bar['w'] }}" height="30" fill="#0b0c0e" />
                                        @endforeach
                                    </svg>
                                    <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-neutral-400">Uso diario</span>
                                </footer>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- =================== MARCAS EN NUESTRO ESTANTE =================== --}}
    @if (count($brands))
        <section class="py-24 sm:py-28" aria-labelledby="marcas-titulo">
            <div class="mx-auto max-w-[1340px] px-4 sm:px-6 lg:px-8">
                <p id="marcas-titulo" class="reveal text-center text-xs font-extrabold uppercase tracking-[0.25em] text-[#bd7710] dark:text-[#f5a623]" {!! $reveal !!}>En nuestro estante</p>
                <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
                    @foreach ($brands as $brand)
                        <li class="reveal" style="--d: {{ $loop->index * 80 }}ms" {!! $reveal !!}>
                            <span class="about-outline cursor-default font-heading text-5xl font-black uppercase tracking-tight text-neutral-400 transition duration-300 hover:text-[#bd7710] hover:[-webkit-text-fill-color:currentColor] dark:text-white/30 dark:hover:text-[#f5a623] sm:text-7xl">{{ $brand['name'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- =================== CIFRAS =================== --}}
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

    {{-- =================== HISTORIA =================== --}}
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

    {{-- =================== CIERRE =================== --}}
    @if ($page->closing_title)
        <section class="px-4 pb-24 sm:px-6 lg:px-8">
            <div class="reveal about-sweep relative isolate mx-auto max-w-[1340px] overflow-hidden rounded-[2.5rem] bg-[#0b0c0e] px-6 py-20 text-center text-white sm:px-16" {!! $reveal !!}>
                <img src="{{ asset('images/hero-detailing.jpg') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover object-[70%_center] opacity-40" loading="lazy" aria-hidden="true">
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#0b0c0e] via-[#0b0c0e]/70 to-[#0b0c0e]/40" aria-hidden="true"></div>
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
