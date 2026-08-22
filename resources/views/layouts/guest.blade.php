<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-dark text-light">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-4 bg-dark">
            <div class="mb-4">
                <a href="/">
                    <x-application-logo class="text-primary" style="width: 80px; height: 80px;" />
                </a>
            </div>

            <div class="w-100 shadow-lg border border-secondary border-opacity-25 overflow-hidden rounded-4 bg-secondary bg-opacity-10" style="max-width: 400px; padding: 2rem;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
