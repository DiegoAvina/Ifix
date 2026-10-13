<?php

namespace App\Services;

use App\Models\HeroSlide;
use Illuminate\Support\Facades\Storage;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Product;

class StorefrontService
{
    /**
     * Hero Slider
     */
    public function heroSlides(): array
    {
        return HeroSlide::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($slide) {

                $image = str_starts_with($slide->image, 'http')
                    ? $slide->image
                    : (
                        str_starts_with($slide->image, '/images/')
                        ? asset($slide->image)
                        : asset(Storage::url($slide->image))
                    );

                return [
                    'eyebrow' => 'Patrocinado',

                    'title' => $slide->title,

                    'description' => $slide->subtitle,

                    'primaryBtn' => [
                        'label' => $slide->button_text ?: 'Ver más',
                        'url' => $slide->button_url ?: '#',
                    ],

                    'secondaryBtn' => [
                        'label' => 'Explorar',
                        'url' => $slide->button_url ?: '#',
                    ],

                    'image' => $image,

                    'imageAlt' => $slide->title,
                ];
            })
            ->toArray();
    }

    /**
     * Marcas
     */
    public function brands(): array
    {
        return Brand::query()

            ->orderBy('name')

            ->get()

            ->map(function ($brand) {

                return [

                    'id' => $brand->id,

                    'name' => $brand->name,

                ];
            })

            ->toArray();
    }
    /**
     * Productos Destacados
     */
    public function featuredProducts(): array
    {
        return [];
    }

    /**
     * Colecciones
     */
    public function collections(): array
    {
        return [

    'featured_collection_slug' => 'destacados',

];
    }
}
