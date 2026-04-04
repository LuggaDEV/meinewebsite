<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $cookiebotCbid = filled(config('cookiebot.domain_group_id'))
                ? config('cookiebot.domain_group_id')
                : 'ba02e5dd-8ce8-4bb5-a40a-800717273f88';
        @endphp
        @if (config('cookiebot.enabled') && ! request()->is('admin', 'admin/*'))
            {{-- Consent-Banner (uc.js) – muss vor anderen Skripten im <head> stehen --}}
            <script
                id="Cookiebot"
                src="https://consent.cookiebot.com/uc.js"
                data-cbid="{{ $cookiebotCbid }}"
                data-blockingmode="auto"
                type="text/javascript"
            ></script>
        @endif

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>Luca Themann - Kochen</title>

        <link rel="icon" href="{{ asset(config('seo.favicon_png')) }}" type="image/png" sizes="32x32">
        <link rel="icon" href="{{ asset(config('seo.favicon_png')) }}" type="image/png" sizes="any">
        <link rel="apple-touch-icon" href="{{ asset(config('seo.apple_touch_icon')) }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
