<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        @livewireStyles

        <style>
            body {
                background-color: white !important;
                color: black !important;
                font-family: 'Figtree', sans-serif;
                font-size: 0.75rem !important;
                line-height: 1.25 !important;
            }
            h1, .h1, h2, .h2 {
                font-size: 1.15rem !important;
                margin-bottom: 0.5rem !important;
                font-weight: bold;
                color: black !important;
            }
            h3, .h3, h3.text-light {
                font-size: 0.95rem !important;
                margin-bottom: 0.25rem !important;
                font-weight: bold;
                color: #000000 !important;
            }
            h4, .h4, h5, .h5, h6, .h6 {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
                font-weight: bold;
                color: #000000 !important;
            }
            .container, .container-fluid, .container-xl {
                max-width: 100% !important;
                padding: 10px !important;
                margin: 0 !important;
            }
            .py-5 {
                padding-top: 10px !important;
                padding-bottom: 10px !important;
            }
            .mb-5 {
                margin-bottom: 15px !important;
            }
            .g-4, .row {
                --bs-gutter-x: 10px !important;
                --bs-gutter-y: 10px !important;
            }
            .card {
                padding: 12px !important;
                border-radius: 8px !important;
                background-color: white !important;
                color: black !important;
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
            .card-header {
                margin-bottom: 8px !important;
            }
            canvas {
                max-height: 220px !important;
            }
            .table {
                --bs-table-bg: transparent !important;
                --bs-table-color: black !important;
                --bs-table-border-color: #dee2e6 !important;
                --bs-table-striped-bg: rgba(0, 0, 0, 0.03) !important;
                color: black !important;
                border-color: #dee2e6 !important;
                margin-bottom: 0 !important;
            }
            .table th, .table td {
                padding: 3px 6px !important;
                font-size: 0.7rem !important;
            }
            .table, .table tr, .table th, .table td, .table * {
                color: #000000 !important;
            }
            .text-light, .text-white, .text-secondary, .text-info {
                color: black !important;
            }
            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background-color: transparent !important;
            }
            .d-print-none, .print-none {
                display: none !important;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="py-3">
            {{ $slot }}
        </div>

        @livewireScripts
        <script>
            window.addEventListener('load', () => {
                // Wait a brief moment for Chart.js and Livewire to render fully
                setTimeout(() => {
                    window.print();
                }, 1200);
            });
        </script>
    </body>
</html>
