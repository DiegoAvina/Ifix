@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50">

    <!-- @include('catalog.partials.hero') -->

    <div class="container mx-auto px-6 py-10">

        <div class="grid grid-cols-12 gap-8">

            {{-- Sidebar --}}
            <aside class="col-span-3">

                <x-catalog.sidebar
                    :filters="$catalog->filters" />

            </aside>

            {{-- Contenido --}}
            <section class="col-span-9">

                {{-- Buscador + Ordenamiento --}}
                @include('catalog.partials.toolbar', [
                'catalog' => $catalog,
                ])

                {{-- Productos --}}
                @include('catalog.partials.grid', [
                'products' => $catalog->products,
                ])

                @include('catalog.partials.pagination', [
                'paginator' => $catalog->products->paginator,
                ])

            </section>

        </div>

    </div>

</div>

@endsection