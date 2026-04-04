<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand & defaults for Open Graph / Twitter Cards
    |--------------------------------------------------------------------------
    */

    'site_name' => env('SEO_SITE_NAME', 'Luca Themann - Kochen'),

    'default_description' => 'Rezepte, Tipps und mehr von Luca Themann – Kochen mit Leidenschaft.',

    /*
    | Public paths (relative to the web root).
    */
    'favicon_svg' => '/favicon.svg',

    'favicon_ico' => '/favicon.ico',

    /*
    | Optional PNG for “Zum Home-Bildschirm” (ca. 180×180). Falls die Datei fehlt, kann sie ergänzt werden.
    */
    'apple_touch_icon' => '/apple-touch-icon.png',

    /*
    | Fallback für Open Graph / Twitter, wenn kein seiten-spezifisches Bild gesetzt ist.
    */
    'default_image' => '/favicon.svg',

    'locale' => 'de_DE',

];
