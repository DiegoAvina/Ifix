{{--
    Metadatos SEO compartidos. Cada vista puede sobrescribir:
    title, meta_description, meta_robots, canonical, og_type, og_image.
--}}
@php
    $seoTitle = trim($__env->yieldContent('title', 'IFIX Detallado Automotriz | Productos profesionales para tu auto'));
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

{{-- Verificación de propiedad (Search Console / Bing Webmaster) --}}
@if (config('seo.google_site_verification'))
    <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif
@if (config('seo.bing_site_verification'))
    <meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
@endif

{{--
    Marca en todas las páginas: Organization + WebSite. Las demás vistas
    (Product, CollectionPage...) referencian estos nodos por su @id.
--}}
<script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'Organization',
                '@id' => url('/').'#organization',
                'name' => config('seo.name'),
                'alternateName' => config('seo.alternate_names'),
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(config('seo.logo')),
                ],
                'image' => asset('images/og-ifix.jpg'),
                'description' => config('seo.description'),
                'areaServed' => 'MX',
                'sameAs' => config('seo.same_as') ?: null,
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => config('seo.telephone'),
                    'contactType' => 'customer service',
                    'areaServed' => 'MX',
                    'availableLanguage' => 'Spanish',
                ],
            ]),
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'name' => config('seo.name'),
                'alternateName' => config('seo.alternate_names'),
                'url' => url('/'),
                'inLanguage' => 'es-MX',
                'publisher' => ['@id' => url('/').'#organization'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('catalog.index').'?search={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
