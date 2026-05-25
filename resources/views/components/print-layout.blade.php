@props(['title'])

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
        
        <style>
            body {
                background-color: white !important;
                color: black !important;
                font-family: 'Figtree', sans-serif;
                font-size: 10px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 1rem;
            }
            .print-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #333;
                padding-bottom: 0.5rem;
                margin-bottom: 1rem;
            }
            .print-footer {
                margin-top: 2rem;
                border-top: 1px solid #dee2e6;
                padding-top: 0.5rem;
                font-size: 0.75rem;
                color: #6c757d;
                display: flex;
                justify-content: space-between;
            }
            @media print {
                @page {
                    size: auto;
                    margin: 10mm;
                }
                body {
                    padding: 0;
                    margin: 0;
                    background-color: white !important;
                    color: black !important;
                }
                .print-container {
                    padding: 0;
                    max-width: 100%;
                }
                .no-print, .d-print-none {
                    display: none !important;
                }
                
                /* Aggressively override dark mode */
                .table-dark {
                    --bs-table-bg: transparent !important;
                    --bs-table-color: black !important;
                    --bs-table-border-color: #dee2e6 !important;
                    --bs-table-striped-bg: #f8f9fa !important;
                    --bs-table-active-bg: #f2f2f2 !important;
                    background-color: transparent !important;
                    color: black !important;
                    border-color: #dee2e6 !important;
                }
                .table-dark th, .table-dark td {
                    background-color: transparent !important;
                    color: black !important;
                    border-color: #dee2e6 !important;
                }
                .table-secondary {
                    --bs-table-bg: #e9ecef !important;
                    --bs-table-color: black !important;
                }
                .table-active {
                    --bs-table-bg: #f8f9fa !important;
                    --bs-table-color: black !important;
                    background-color: #f8f9fa !important;
                }
                .bg-dark {
                    background-color: transparent !important;
                    color: black !important;
                    border: 1px solid #dee2e6 !important;
                }
                .text-light, .text-white {
                    color: black !important;
                }
                .border-secondary {
                    border-color: #dee2e6 !important;
                }
                .card {
                    border: 1px solid #dee2e6 !important;
                    background-color: transparent !important;
                }
                .badge {
                    border: 1px solid #dee2e6 !important;
                    background-color: transparent !important;
                    color: black !important;
                }
                .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger {
                    background-color: transparent !important;
                    border: 1px solid #dee2e6 !important;
                    color: black !important;
                }
                .text-primary, .text-success, .text-info, .text-warning, .text-danger {
                    color: black !important;
                    font-weight: bold !important;
                }
                
                /* Custom d-print utility classes */
                .d-print-bg-light {
                    background-color: #f8f9fa !important;
                }
                .d-print-bg-white {
                    background-color: white !important;
                }
                .d-print-text-dark {
                    color: black !important;
                }
                .d-print-text-success {
                    color: #198754 !important;
                }
                .d-print-overflow-visible {
                    overflow: visible !important;
                }
                .d-print-table {
                    display: table !important;
                }
                .d-print-p-1 {
                    padding: 0.25rem !important;
                }
                .d-print-block {
                    display: block !important;
                }
                
                /* Remove transparency/opacity in print */
                .bg-opacity-10, .bg-opacity-25, .bg-opacity-50, .bg-opacity-75,
                .opacity-10, .opacity-25, .opacity-50, .opacity-75 {
                    opacity: 1 !important;
                    background-color: transparent !important;
                }
            }
            
            /* Ensure tables look good in print */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                color: black !important;
                font-size: 10px !important;
            }
            .table th, .table td {
                border: 1px solid #dee2e6 !important;
                padding: 4px 8px !important;
            }
        </style>
    </head>
    <body class="bg-white">
        <div class="print-container">
            <!-- Print Header -->
            <header class="print-header">
                <div class="d-flex align-items-center gap-3">
                    <x-application-logo style="width: 64px; height: 64px;" class="text-primary" />
                    <div>
                        <h1 class="h3 fw-black text-uppercase tracking-widest mb-0">{{ config('app.name', 'Fuel Monitoring') }}</h1>
                        <p class="small text-secondary mb-0">System Generated Report</p>
                    </div>
                </div>
                <div class="text-end">
                    <h2 class="h4 fw-bold text-uppercase mb-0">{{ $title }}</h2>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Print Footer -->
            <footer class="print-footer">
                <div>
                    Printed on: {{ now()->format('F d, Y h:i A') }}
                </div>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        <i class="bi bi-printer me-1"></i> Print Again
                    </button>
                </div>
                <div>
                    Page <span class="page-number"></span>
                </div>
            </footer>
        </div>

        <script>
            window.onload = function() {
                // Auto-trigger print
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        </script>
    </body>
</html>
