<?php

use App\Media\SafeMediaDefinitions;
use Lunar\Base\StandardMediaDefinitions;

return [

    'definitions' => [
        'asset' => StandardMediaDefinitions::class,
        'brand' => SafeMediaDefinitions::class,
        'collection' => SafeMediaDefinitions::class,
        'product' => SafeMediaDefinitions::class,
        'product-option' => SafeMediaDefinitions::class,
        'product-option-value' => SafeMediaDefinitions::class,
    ],

    'collection' => 'images',

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
