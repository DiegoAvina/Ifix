{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('catalog.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
@foreach ($products as $product)
    <url>
        <loc>{{ route('catalog.show', $product->defaultUrl?->slug ?? $product->id) }}</loc>
        <lastmod>{{ $product->updated_at?->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
@foreach ($product->getMedia('images') as $media)
        <image:image>
            <image:loc>{{ $media->getUrl() }}</image:loc>
        </image:image>
@endforeach
    </url>
@endforeach
</urlset>
