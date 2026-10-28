{{--
    Metadatos SEO compartidos. Cada vista puede sobrescribir:
    title, meta_description, meta_robots, canonical, og_type, og_image.
--}}
@php
    $seoTitle = trim($__env->yieldContent('title', 'IFIX | Productos para detallado automotriz profesional'));
    $seoDescription = trim($__env->yieldContent(
        'meta_description',
        'Tienda oficial IFIX: productos profesionales para detallado automotriz. Shampoo, pulimentos, ceras, selladores cerámicos y restauradores de plásticos con envío a todo México.'
    ));
    $seoRobots = trim($__env->yieldContent('meta_robots', 'index, follow, max-image-preview:large'));
    $seoCanonical = trim($__env->yieldContent('canonical', url()->current()));
    $seoType = trim($__env->yieldContent('og_type', 'website'));
    $seoImage = trim($__env->yieldContent('og_image', asset('images/og-ifix.jpg')));
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">
<meta name="theme-color" content="#111111">

{{-- Open Graph (WhatsApp, Facebook) --}}
<meta property="og:site_name" content="IFIX">
<meta property="og:locale" content="es_MX">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImage }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
