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
    'favicon_png' => '/favicon.png',

    /*
    | Optional größeres PNG für „Zum Home-Bildschirm“ (z. B. 180×180).
    */
    'apple_touch_icon' => '/apple-touch-icon.png',

    /*
    | Fallback für Open Graph / Twitter, wenn kein seiten-spezifisches Bild gesetzt ist.
    */
    'default_image' => '/favicon.png',

    'locale' => 'de_DE',

];
