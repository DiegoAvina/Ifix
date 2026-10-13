<!DOCTYPE html>
<html class="light" lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- CSRF --}}
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Ifix | Innovando tu Mundo')
    </title>


    {{-- Fonts --}}

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    >


    {{-- Custom styles --}}

    <style>

        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 300,
                'GRAD' 0,
                'opsz' 24;
        }

        body {
            background-color: #fcf9f8;
            color: #1c1b1b;
        }

        .carousel-item {
            opacity: 0;
            transition:
                opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .carousel-item.active {
            opacity: 1;
            position: relative;
            pointer-events: auto;
        }

    </style>


    {{-- Vite --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


    @stack('styles')

</head>


<body class="font-body-md antialiased overflow-x-hidden">


    {{-- ========================================================= --}}
    {{-- NAVBAR IFIX --}}
    {{-- ========================================================= --}}

    @include('partials.navbar')


    {{-- ========================================================= --}}
    {{-- CONTENIDO --}}
    {{-- ========================================================= --}}

    <main>

        @yield('content')

    </main>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    @include('partials.footer')


    {{-- ========================================================= --}}
    {{-- SCRIPTS --}}
    {{-- ========================================================= --}}

    @stack('scripts')


</body>

</html>