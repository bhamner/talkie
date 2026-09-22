<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @if (filled($ga4Id = config('services.ga4.id')))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4Id) }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', @json($ga4Id));
            </script>
        @endif
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="color-scheme" content="light">
        <meta name="theme-color" content="#0369a1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Talkie') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        <title inertia>{{ config('app.name', 'Talkie') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:500,600,700,800" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
