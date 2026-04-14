<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-[#1C1B1F] text-[#E6E1E5] dark print:bg-white print:text-black">
        <!-- Global Livewire Loading Progress Bar -->
        <div wire:loading.block class="fixed top-0 left-0 w-full z-[2147483647] pointer-events-none print:hidden" style="display: none;">
            <div class="flex w-full h-2 bg-black/50 backdrop-blur-md justify-center items-center">
                <div class="relative w-1/3 h-1 bg-[#49454F] rounded-full overflow-hidden">
                    <div class="absolute top-0 h-full w-1/3 bg-[#D0BCFF] shadow-[0_0_20px_#D0BCFF] animate-progress-center-bounce rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Global Manual Loading Progress Bar -->
        <div id="manual-global-loader" class="fixed top-0 left-0 w-full z-[2147483647] pointer-events-none hidden print:hidden">
            <div class="flex w-full h-2 bg-black/50 backdrop-blur-md justify-center items-center">
                <div class="relative w-1/3 h-1 bg-[#49454F] rounded-full overflow-hidden">
                    <div class="absolute top-0 h-full w-1/3 bg-[#D0BCFF] shadow-[0_0_20px_#D0BCFF] animate-progress-center-bounce rounded-full"></div>
                </div>
            </div>
        </div>

        <script>
            // Global functions to manually trigger the loading animation
            window.showLoadingIndicator = function() {
                document.getElementById('manual-global-loader').classList.remove('hidden');
                document.getElementById('manual-global-loader').style.display = 'block';
            };
            
            window.hideLoadingIndicator = function() {
                document.getElementById('manual-global-loader').classList.add('hidden');
                document.getElementById('manual-global-loader').style.display = 'none';
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
            @media print {
                body { background-color: white !important; color: black !important; -webkit-print-color-adjust: exact; }
                .min-h-screen { min-height: auto !important; }
            }
        </style>

        <div class="min-h-screen flex flex-col print:min-h-0 print:block">
            <div class="print:hidden">
                @include('layouts.navigation')
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-[#1C1B1F] border-b border-[#49454F]/30 z-10 relative print:hidden">
                    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-grow">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
