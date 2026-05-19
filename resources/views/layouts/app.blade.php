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
    <body class="bg-dark text-light">
        <!-- Global Livewire Loading Progress Bar -->
        <div wire:loading.block class="position-fixed top-0 start-0 w-100 z-3 pointer-events-none d-print-none" style="display: none;">
            <div class="d-flex w-100 bg-black bg-opacity-50 backdrop-blur-md justify-content-center align-items-center" style="height: 8px;">
                <div class="position-relative w-25 bg-secondary rounded-pill overflow-hidden" style="height: 4px;">
                    <div class="position-absolute top-0 h-100 bg-primary shadow-sm animate-progress-center-bounce rounded-pill" style="width: 33%;"></div>
                </div>
            </div>
        </div>

        <!-- Global Manual Loading Progress Bar -->
        <div id="manual-global-loader" class="position-fixed top-0 start-0 w-100 z-3 pointer-events-none d-none d-print-none">
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
                html, [data-bs-theme="dark"] {
                    color-scheme: light !important;
                }
                
                body { 
                    background-color: white !important; 
                    color: black !important;
                    --bs-body-bg: white !important;
                    --bs-body-color: black !important;
                    --bs-tertiary-bg: white !important;
                    --bs-secondary-bg: #f8f9fa !important;
                    --bs-emphasis-color: black !important;
                    --bs-secondary-color: rgba(0, 0, 0, 0.7) !important;
                    --bs-tertiary-color: rgba(0, 0, 0, 0.5) !important;
                    --bs-border-color: #dee2e6 !important;
                }
                
                .d-print-none { display: none !important; }
                .print-none { display: none !important; }
                .min-vh-100 { min-height: auto !important; }
                
                /* Force light theme for printing */
                .bg-dark, .table-dark, .card, .modal-content, .bg-secondary, .card-header {
                    background-color: white !important;
                    color: black !important;
                    border-color: #dee2e6 !important;
                }
                
                /* Fix for Bootstrap 5 tables */
                .table {
                    --bs-table-bg: transparent !important;
                    --bs-table-color: black !important;
                    --bs-table-border-color: #dee2e6 !important;
                    --bs-table-striped-bg: rgba(0, 0, 0, 0.05) !important;
                    --bs-table-active-bg: rgba(0, 0, 0, 0.1) !important;
                    color: black !important;
                    border-color: #dee2e6 !important;
                }
                
                .table-dark, .table-primary, .table-secondary, .table-success, .table-info, .table-warning, .table-danger {
                    --bs-table-bg: #f8f9fa !important;
                    --bs-table-color: black !important;
                    --bs-table-border-color: #dee2e6 !important;
                    color: black !important;
                }
                
                /* Ensure all text colors are readable on white */
                .text-light, .text-white, .text-secondary, .text-primary, .text-info, .text-success, .text-warning, .text-danger {
                    color: black !important;
                }
                
                /* Force background colors if specifically needed (e.g. badges) */
                .badge {
                    border: 1px solid #000 !important;
                    color: #000 !important;
                    background-color: transparent !important;
                }

                .container, .container-fluid, .container-xl {
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .shadow, .shadow-sm, .shadow-lg {
                    box-shadow: none !important;
                }
            }
        </style>

        <div class="min-vh-100 d-flex flex-column print-block">
            <div class="d-print-none">
                @include('layouts.navigation')
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-dark border-bottom border-secondary border-opacity-25 z-1 relative d-print-none">
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
