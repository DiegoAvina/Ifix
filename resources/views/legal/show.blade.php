@extends('layouts.app')

@section('title', $page->title.' | IFIX')

@section('content')
<div class="min-h-screen bg-[#f5f5f3] py-8 text-neutral-900 dark:bg-[#111315] dark:text-white sm:py-12">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-neutral-500 transition hover:text-[#bd7710]">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            Inicio
        </a>

        <header class="mt-5">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#bd7710]">Información legal</p>
            <h1 class="mt-2 font-heading text-3xl font-black tracking-tight sm:text-4xl">{{ $page->title }}</h1>
            <p class="mt-2 text-sm text-neutral-500">
                Última actualización:
                <time datetime="{{ $page->updated_at->toDateString() }}">{{ $page->updated_at->timezone('America/Mexico_City')->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</time>
            </p>
        </header>

        <article class="prose prose-neutral mt-8 max-w-none rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:prose-invert dark:border-white/10 sm:p-10
                        prose-headings:font-heading prose-headings:tracking-tight prose-h2:mt-10 prose-h2:text-xl prose-a:text-amber-700 dark:prose-a:text-amber-400 prose-li:my-1">
            {!! $content !!}
        </article>
    </div>
</div>
@endsection
