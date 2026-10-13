@extends('layouts.app')

@section('title', $product->name)
@section('content')

<div class="bg-surface-container-low min-h-screen">

    <div class="max-w-screen-2xl mx-auto px-8 py-10">

        {{-- Breadcrumb --}}
        <nav class="mb-8 flex items-center gap-2 text-sm text-secondary">

            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Inicio</a>

            <span class="material-symbols-outlined text-base" aria-hidden="true">chevron_right</span>
 
            <a href="{{ route('catalog.index', ['brand' => [$product->brand->id]]) }}" class="hover:text-primary transition-colors">{{ $product->brand->name }}</a>

            <span class="material-symbols-outlined text-base" aria-hidden="true">chevron_right</span>

            <span class="truncate text-on-surface">{{ $product->name }}</span>

        </nav>

        {{-- Hero --}}
        <div class="rounded-[2rem] border border-outline-variant/30 bg-white p-8 shadow-sm">

            <div class="grid grid-cols-1 gap-16 lg:grid-cols-2">

                @include('products.partials.gallery')

                @include('products.partials.info')

            </div>

        </div>

        <div class="mt-14">

            @include('products.partials.description')

        </div>

        @if(count($product->specifications))

        <div class="mt-14">

            @include('products.partials.specifications')

        </div>

        @endif

        @if(count($product->relatedProducts))

        <div class="mt-16">

            @include('products.partials.related-products')

        </div>

        @endif

    </div>

</div>

@endsection