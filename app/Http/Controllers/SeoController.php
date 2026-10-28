<?php

namespace App\Http\Controllers;

use App\Services\Storefront\StorefrontAvailabilityQuery;
use Illuminate\Http\Response;
use Lunar\Models\Product;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        // Fuera de producción (local, staging) no se indexa nada.
        if (! app()->isProduction()) {
            $lines[] = 'Disallow: /';
        } else {
            array_push(
                $lines,
                'Disallow: /carrito',
                'Disallow: /checkout',
                'Disallow: /mis-pedidos',
                'Disallow: /profile',
                'Disallow: /pagos',
                'Disallow: /payments',
                'Disallow: /api',
                'Disallow: /lunar',
                '',
                'Sitemap: '.route('seo.sitemap'),
            );
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(StorefrontAvailabilityQuery $availability): Response
    {
        $products = $availability
            ->apply(Product::query()->where('status', 'published'))
            ->with(['defaultUrl', 'media'])
            ->get();

        return response()
            ->view('seo.sitemap', ['products' => $products])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
