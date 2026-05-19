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
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 2rem;
            }
            .print-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #333;
                padding-bottom: 1rem;
                margin-bottom: 2rem;
            }
            .print-footer {
                margin-top: 3rem;
                border-top: 1px solid #dee2e6;
                padding-top: 1rem;
                font-size: 0.85rem;
                color: #6c757d;
                display: flex;
                justify-content: space-between;
            }
            @media print {
                @page {
                    size: auto;
                    margin: 15mm;
                }
                body {
                    padding: 0;
                    margin: 0;
                }
                .print-container {
                    padding: 0;
                }
                .no-print {
                    display: none !important;
                }
            }
            
            /* Ensure tables look good in print */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                color: black !important;
            }
            .table th, .table td {
                border: 1px solid #dee2e6 !important;
                padding: 0.5rem !important;
            }
            .table-dark {
                background-color: transparent !important;
                color: black !important;
            }
            .bg-dark {
                background-color: #f8f9fa !important;
                color: black !important;
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
