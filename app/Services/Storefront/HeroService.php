<?php

namespace App\Services\Storefront;

use App\Models\HeroSlide;
use App\Support\SafeHtml;

class HeroService
{
    public function all(): array
    {
        return HeroSlide::query()
            ->live()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($slide) {

                $image = str_starts_with($slide->image, 'http')
                    ? $slide->image
                    : (
                        str_starts_with($slide->image, '/images/')
                            ? asset($slide->image)
                            : asset('storage/'.$slide->image)
                    );

                return [

                    'eyebrow' => $slide->eyebrow ?: 'Oferta',

                    'title' => $slide->title,

                    'description' => $slide->subtitle,

                    'primaryBtn' => [
                        'label' => $slide->button_text ?: 'Ver más',
                        'url' => SafeHtml::url($slide->button_url, route('catalog.index')),
                    ],

                    'image' => $image,

                    'imageAlt' => $slide->title,

                ];
            })
            ->toArray();
    }
}
