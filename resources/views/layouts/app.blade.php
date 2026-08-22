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
        @livewireStyles
    </head>
    <body class="{{ request()->query('print') == 1 ? 'compact-print-view bg-white text-dark' : 'bg-dark text-light' }}">
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
            
            /* Compact Print View styles (works both on screen preview and on paper) */
            .compact-print-view {
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
                font-size: 0.75rem !important;
                line-height: 1.25 !important;
            }
            .compact-print-view h1, .compact-print-view .h1,
            .compact-print-view h2, .compact-print-view .h2 {
                font-size: 1.15rem !important;
                margin-bottom: 0.5rem !important;
            }
            .compact-print-view h3, .compact-print-view .h3 {
                font-size: 0.95rem !important;
                margin-bottom: 0.25rem !important;
            }
            .compact-print-view h4, .compact-print-view .h4,
            .compact-print-view h5, .compact-print-view .h5,
            .compact-print-view h6, .compact-print-view .h6 {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
            }
            .compact-print-view .container, .compact-print-view .container-fluid, .compact-print-view .container-xl {
                max-width: 100% !important;
                padding: 10px !important;
                margin: 0 !important;
            }
            .compact-print-view .py-5 {
                padding-top: 10px !important;
                padding-bottom: 10px !important;
            }
            .compact-print-view .mb-5 {
                margin-bottom: 15px !important;
            }
            .compact-print-view .g-4, .compact-print-view .row {
                --bs-gutter-x: 10px !important;
                --bs-gutter-y: 10px !important;
            }
            .compact-print-view .card {
                padding: 12px !important;
                border-radius: 8px !important;
                background-color: white !important;
                color: black !important;
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
            .compact-print-view .card-header {
                margin-bottom: 8px !important;
            }
            .compact-print-view canvas {
                max-height: 220px !important;
            }
            .compact-print-view .table {
                --bs-table-bg: transparent !important;
                --bs-table-color: black !important;
                --bs-table-border-color: #dee2e6 !important;
                --bs-table-striped-bg: rgba(0, 0, 0, 0.03) !important;
                color: black !important;
                border-color: #dee2e6 !important;
                margin-bottom: 0 !important;
            }
            .compact-print-view .table th, .compact-print-view .table td {
                padding: 3px 6px !important;
                font-size: 0.7rem !important;
            }
            .compact-print-view .bg-dark, .compact-print-view .table-dark, .compact-print-view .modal-content, .compact-print-view .bg-secondary {
                background-color: white !important;
                color: black !important;
                border-color: #dee2e6 !important;
            }
            .compact-print-view .text-light, .compact-print-view .text-white, .compact-print-view .text-secondary, .compact-print-view .text-info {
                color: black !important;
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
        @if(request()->query('print') == 1)
            <script>
                window.addEventListener('load', () => {
                    // Wait a brief moment for Chart.js and Livewire to render fully
                    setTimeout(() => {
                        window.print();
                    }, 1200);
                });
            </script>
        @endif
    </body>
</html>
