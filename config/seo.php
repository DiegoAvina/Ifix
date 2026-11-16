<?php

/*
|--------------------------------------------------------------------------
| SEO de marca
|--------------------------------------------------------------------------
|
| Datos de la marca que se publican en todas las páginas como schema.org
| Organization + WebSite. Google usa `name` / `alternate_names` para el nombre
| del sitio en los resultados y para entender búsquedas de marca
| ("ifix", "ifix detallado", "ifix detallado automotriz"...).
|
*/

return [

    'name' => 'IFIX',

    'alternate_names' => [
        'Ifix',
        'IFIX Detallado',
        'IFIX Detallado Automotriz',
        'IFIX México',
    ],

    'description' => 'IFIX es una marca mexicana de productos profesionales para detallado automotriz: shampoo para auto, pulimentos, ceras, selladores cerámicos y restauradores de plásticos.',

    // Logo para Google (mín. 112 px de alto, fondo sólido o transparente).
    'logo' => 'images/ifix-logo-email.png',

    'telephone' => '+52-55-4169-8230',

    /*
     | Perfiles oficiales (Facebook, Instagram, TikTok, YouTube, Mercado Libre...).
     | Separados por coma en SEO_SAME_AS. Ayudan a Google a ligar la marca con
     | sus redes y a mostrar el panel de conocimiento.
     */
    'same_as' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_SAME_AS', ''))))),

    // Código de "Etiqueta HTML" de Google Search Console (solo el content="...").
    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),

    // Bing Webmaster Tools (también alimenta a DuckDuckGo y Yahoo).
    'bing_site_verification' => env('BING_SITE_VERIFICATION'),

];
