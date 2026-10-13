{{--
    Vista: Home / Página de inicio
    Extiende: layouts/app.blade.php

    Ensambla hero, galería de productos y banner de innovación.
    Los datos de productos y slides pueden venir del controlador.
--}}

@extends('layouts.app')

@section('title', 'Ifix | Innovando tu Mundo')

@section('content')

{{-- ── Hero Carousel ───────────────────────────────────────────────── --}}
@include('partials.hero')

{{-- ── Trust Bar ───────────────────────────────────────────────────── --}}
<section class="bg-white py-12 border-b border-outline-variant/20">
    <div class="max-w-screen-2xl mx-auto px-8 grid grid-cols-2 gap-8 lg:grid-cols-4">

        @foreach([
            ['icon' => 'local_shipping', 'title' => 'Envío rápido', 'text' => 'A todo el país'],
            ['icon' => 'verified_user', 'title' => 'Garantía oficial', 'text' => 'En cada dispositivo'],
            ['icon' => 'encrypted', 'title' => 'Pago seguro', 'text' => 'Datos protegidos'],
            ['icon' => 'support_agent', 'title' => 'Soporte experto', 'text' => 'Antes y después de comprar'],
        ] as $feature)

        <div class="flex items-center gap-4">

            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-surface-container-low text-primary">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $feature['icon'] }}</span>
            </span>

            <div class="min-w-0">
                <p class="font-label-md text-label-md text-on-surface">{{ $feature['title'] }}</p>
                <p class="text-sm text-secondary truncate">{{ $feature['text'] }}</p>
            </div>

        </div>

        @endforeach

    </div>
</section>

@include('partials.brands')

@foreach($collections as $collection)

<x-collection-section
    :collection="$collection"
    :index="$loop->index" />

@endforeach

{{-- ── Innovation Banner ───────────────────────────────────────────── --}}
<section class="relative isolate overflow-hidden bg-surface-container-low py-section-gap">

    {{-- Decoración: manchas de luz difuminadas --}}
    <div
        aria-hidden="true"
        class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl">
    </div>

    <div
        aria-hidden="true"
        class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl">
    </div>

    {{-- Decoración: trama de puntos muy sutil --}}
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 opacity-[0.04] [mask-image:radial-gradient(ellipse_65%_65%_at_50%_50%,black,transparent)]"
        style="background-image:radial-gradient(currentColor 1px, transparent 1px); background-size:24px 24px; color:theme('colors.primary')">
    </div>

    <div class="relative max-w-screen-2xl mx-auto px-8 grid grid-cols-12">

        <div class="col-span-12 lg:col-span-6 lg:col-start-4 text-center">

            <span
                class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-full border border-primary/15 bg-white shadow-sm shadow-primary/5">
                <span class="material-symbols-outlined text-2xl text-primary" aria-hidden="true">
                    auto_awesome
                </span>
            </span>

            <h2 class="font-display-lg text-display-lg mb-8">Innovación</h2>

            <p class="font-body-lg text-body-lg text-secondary mb-12 mx-auto max-w-2xl">
                Nuestra búsqueda de la perfección es incansable. Cada milímetro de un dispositivo Ifix
                es analizado para asegurar que cumple con el estándar de Precisión Digital.
            </p>

            <a href="#"
                class="group inline-flex items-center gap-3 rounded-full border border-primary text-primary px-10 py-4
                          font-label-md text-label-md uppercase
                          transition-all duration-300 hover:bg-primary hover:text-white hover:shadow-xl hover:shadow-primary/20">
                Descubre el Centro de Innovación
                <span
                    class="material-symbols-outlined text-lg transition-transform duration-300 group-hover:translate-x-1"
                    aria-hidden="true">
                    arrow_forward
                </span>
            </a>

        </div>

    </div>
</section>

@endsection