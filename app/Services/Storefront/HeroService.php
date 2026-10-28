<?php

namespace App\Services\Storefront;

use App\Models\HeroSlide;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\Storage;

class HeroService
{
    public function all(): array
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

                $buttonUrl = SafeHtml::url($slide->button_url);

                return [

                    'eyebrow' => 'Patrocinado',

                    'title' => $slide->title,

                    'description' => $slide->subtitle,

                    'primaryBtn' => [
                        'label' => $slide->button_text ?: 'Ver más',
                        'url' => $buttonUrl,
                    ],

                    'secondaryBtn' => [
                        'label' => 'Explorar',
                        'url' => $buttonUrl,
                    ],

                    'image' => $image,

                    'imageAlt' => $slide->title,

                ];
            })
            ->toArray();
    }
}