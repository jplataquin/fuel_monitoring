<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-dark text-light print:bg-white print:text-dark">
        <!-- Global Livewire Loading Progress Bar -->
        <div wire:loading.block class="position-fixed top-0 start-0 w-100 z-3 pointer-events-none print-none" style="display: none;">
            <div class="d-flex w-100 bg-black bg-opacity-50 backdrop-blur-md justify-content-center align-items-center" style="height: 8px;">
                <div class="position-relative w-25 bg-secondary rounded-pill overflow-hidden" style="height: 4px;">
                    <div class="position-absolute top-0 h-100 bg-primary shadow-sm animate-progress-center-bounce rounded-pill" style="width: 33%;"></div>
                </div>
            </div>
        </div>

        <!-- Global Manual Loading Progress Bar -->
        <div id="manual-global-loader" class="position-fixed top-0 start-0 w-100 z-3 pointer-events-none d-none print-none">
            <div class="d-flex w-100 bg-black bg-opacity-50 backdrop-blur-md justify-content-center align-items-center" style="height: 8px;">
                <div class="position-relative w-25 bg-secondary rounded-pill overflow-hidden" style="height: 4px;">
                    <div class="position-absolute top-0 h-100 bg-primary shadow-sm animate-progress-center-bounce rounded-pill" style="width: 33%;"></div>
                </div>
            </div>
        </div>

        <script>
            // Global functions to manually trigger the loading animation
            window.showLoadingIndicator = function() {
                document.getElementById('manual-global-loader').classList.remove('d-none');
            };
            
            window.hideLoadingIndicator = function() {
                document.getElementById('manual-global-loader').classList.add('d-none');
            };
        </script>

        <style>
            .animate-progress-center-bounce {
                animation: progress-center-bounce 0.8s infinite ease-in-out alternate;
            }
            @keyframes progress-center-bounce {
                0% { transform: translateX(-50%); }
                100% { transform: translateX(250%); }
            }
            .backdrop-blur-md {
                backdrop-filter: blur(12px);
            }
            @media print {
                body { background-color: white !important; color: black !important; }
                .print-none { display: none !important; }
                .min-vh-100 { min-height: auto !important; }
            }
        </style>

        <div class="min-vh-100 d-flex flex-column print-block">
            <div class="print-none">
                @include('layouts.navigation')
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-dark border-bottom border-secondary border-opacity-25 z-1 relative print-none">
                    <div class="container-xl py-4 py-md-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-grow-1">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
