<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>{{ __('Sub-Account Balances') }} - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        
        <style>
            body {
                background-color: #1c1b1f;
                color: #e6e1e5;
            }
            .public-header {
                background-color: #D0BCFF;
                color: #381E72;
            }
            .hover-bg-light:hover {
                background-color: rgba(255, 255, 255, 0.05) !important;
            }
            .transition-all {
                transition: all 0.2s ease-in-out;
            }
            
            /* Compact Print View styles (works both on screen preview and on paper) */
            .compact-print-view {
                background-color: white !important;
                color: black !important;
                font-size: 0.75rem !important;
                line-height: 1.25 !important;
            }
            .compact-print-view .public-header {
                display: none !important;
            }
            .compact-print-view h1, .compact-print-view .h1,
            .compact-print-view h2, .compact-print-view .h2 {
                font-size: 1.15rem !important;
                margin-bottom: 0.5rem !important;
            }
            .compact-print-view h3, .compact-print-view .h3, .compact-print-view h3.text-light {
                font-size: 0.95rem !important;
                margin-bottom: 0.25rem !important;
                color: #000000 !important;
            }
            .compact-print-view h4, .compact-print-view .h4,
            .compact-print-view h5, .compact-print-view .h5,
            .compact-print-view h6, .compact-print-view .h6 {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
            }
            .compact-print-view .container, .compact-print-view .container-xl {
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
            .compact-print-view .table, .compact-print-view .table tr, .compact-print-view .table th, .compact-print-view .table td, .compact-print-view .table * {
                color: #000000 !important;
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
                }
                .public-header {
                    background-color: transparent !important;
                    color: black !important;
                    border-bottom: 1px solid #dee2e6 !important;
                    box-shadow: none !important;
                    padding-top: 10px !important;
                    padding-bottom: 10px !important;
                    margin-bottom: 20px !important;
                }
                .public-header h1, .public-header p {
                    color: black !important;
                }
                .card {
                    background-color: white !important;
                    color: black !important;
                    border: 1px solid #dee2e6 !important;
                }
                .text-light, .text-white, .text-secondary, .text-info {
                    color: black !important;
                }
                .d-print-none {
                    display: none !important;
                }
                .shadow-sm, .shadow-lg {
                    box-shadow: none !important;
                }
                .container-xl {
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased {{ request()->query('print') == 1 ? 'compact-print-view bg-white text-dark' : '' }}">
        <div class="min-vh-100 pb-5">
            
            <!-- Minimalist Header -->
            <header class="public-header py-4 mb-5 shadow-sm">
                <div class="container-xl">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <h1 class="h3 fw-bold mb-0 tracking-tight">Fuel Budget Monitoring</h1>
                                <p class="small fw-medium mb-0 opacity-75">Live Public Dashboard: {{ $link->name ?? 'Shared Overview' }}</p>
                            </div>
                        </div>
                        <div class="d-none d-sm-block">
                            <x-application-logo style="width: 40px; height: 40px;" class="fill-current text-dark" />
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-xl">
                <!-- Back Button & Page Header -->
                <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3 mb-5">
                    <div>
                        <h2 class="h4 font-weight-bold text-light mb-1">
                            Sub-Account Balances - {{ $chargeableAccount->name }}
                        </h2>
                        <p class="text-secondary small mb-0 text-uppercase tracking-wider fw-bold">
                            Account Type: {{ $chargeableAccount->classification }}
                            @if($chargeableAccount->classification === 'Scoped')
                                ({{ $chargeableAccount->start_date ? $chargeableAccount->start_date->format('M d, Y') : 'N/A' }} - {{ $chargeableAccount->end_date ? $chargeableAccount->end_date->format('M d, Y') : 'N/A' }})
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2 d-print-none">
                        <button onclick="window.print()" class="btn btn-outline-info rounded-pill px-4 shadow-sm fw-bold text-uppercase small d-flex align-items-center gap-2">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <a href="{{ route('public.dashboard', $link->slug) }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold text-uppercase small d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Chart Card -->
                    <div class="col-12 col-xl-8">
                        <div class="card h-100 bg-dark border-secondary border-opacity-50 rounded-4 p-4 shadow-sm">
                            <div class="card-header bg-transparent border-0 p-0 mb-4">
                                <h3 class="h5 fw-bold text-light mb-1">Sub-Account Remaining Balances</h3>
                                <p class="text-secondary small mb-0">Visual comparison of remaining fuel budgets in Liters (L).</p>
                            </div>
                            <div class="card-body p-0 d-flex align-items-center justify-content-center" style="min-height: 400px; position: relative;">
                                @if(count($chartLabels) > 0)
                                    <canvas id="subAccountChart" style="max-height: 500px;"></canvas>
                                @else
                                    <div class="text-center py-5 w-100">
                                        <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                            <i class="bi bi-bar-chart text-secondary fs-3"></i>
                                        </div>
                                        <h4 class="h6 fw-bold text-light">No Sub-Accounts Found</h4>
                                        <p class="text-secondary small mb-0">There are no sub-accounts allocated to this chargeable account.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Summary Card -->
                    <div class="col-12 col-xl-4">
                        <div class="card h-100 bg-dark border-secondary border-opacity-50 rounded-4 p-4 shadow-sm">
                            <div class="card-header bg-transparent border-0 p-0 mb-4">
                                <h3 class="h5 fw-bold text-light mb-1">Summary Statistics</h3>
                                <p class="text-secondary small mb-0">Overview of account totals.</p>
                            </div>
                            <div class="card-body p-0">
                                @php
                                    $totalBudgets = collect($subAccountData)->sum('total_budget');
                                    $totalConsumed = collect($subAccountData)->sum('consumed');
                                    $totalRemaining = collect($subAccountData)->sum('remaining');
                                    $utilizationPercent = $totalBudgets > 0 ? ($totalConsumed / $totalBudgets) * 100 : 0;
                                @endphp

                                <div class="vstack gap-3">
                                    <div class="p-3 bg-secondary bg-opacity-10 rounded-3 text-center">
                                        <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 0.65rem;">Total Allocated Budget</div>
                                        <div class="h3 font-monospace fw-bold text-light mb-0">{{ number_format($totalBudgets, 2) }} L</div>
                                    </div>
                                    <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                                        <div class="text-success small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 0.65rem; color: #34d399 !important;">Total Consumed</div>
                                        <div class="h3 font-monospace fw-bold text-success mb-0" style="color: #34d399 !important;">{{ number_format($totalConsumed, 2) }} L</div>
                                    </div>
                                    <div class="p-3 bg-info bg-opacity-10 rounded-3 text-center">
                                        <div class="text-info small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 0.65rem; color: #38bdf8 !important;">Total Remaining Balance</div>
                                        <div class="h3 font-monospace fw-bold text-info mb-0" style="color: #38bdf8 !important;">{{ number_format($totalRemaining, 2) }} L</div>
                                    </div>

                                    <div class="mt-2 border-top border-secondary border-opacity-25 pt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-secondary small fw-medium text-uppercase tracking-wider" style="font-size: 0.7rem;">Overall Budget Utilization</span>
                                            <span class="font-monospace fw-bold" style="color: {{ $utilizationPercent >= 90 ? '#ef4444' : ($utilizationPercent >= 75 ? '#f59e0b' : '#34d399') }};">
                                                {{ number_format($utilizationPercent, 1) }}%
                                            </span>
                                        </div>
                                        <div class="progress bg-secondary bg-opacity-25" style="height: 10px; border-radius: 5px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ min(100, $utilizationPercent) }}%; border-radius: 5px; background-color: {{ $utilizationPercent >= 90 ? '#ef4444' : ($utilizationPercent >= 75 ? '#f59e0b' : '#34d399') }};" aria-valuenow="{{ $utilizationPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Tabular List Card -->
                    <div class="col-12">
                        <div class="card bg-dark border-secondary border-opacity-50 rounded-4 shadow-sm overflow-hidden">
                            <div class="card-header bg-dark border-secondary border-opacity-50 p-4">
                                <h3 class="h5 fw-bold text-light mb-1">Sub-Account Breakdowns</h3>
                                <p class="text-secondary small mb-0">Detailed breakdown of budgets and consumption per sub-account.</p>
                            </div>
                            <div class="card-body p-0 text-light">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover mb-0 border-secondary align-middle" style="min-width: 1100px;">
                                        <thead class="table-secondary">
                                            <tr class="text-uppercase small fw-bold tracking-widest text-nowrap">
                                                <th class="px-4 py-3 border-secondary">Sub-Account Name</th>
                                                <th class="px-4 py-3 border-secondary text-end">Total Budget (L)</th>
                                                <th class="px-4 py-3 border-secondary text-end">Consumed (L)</th>
                                                <th class="px-4 py-3 border-secondary text-end">Remaining (L)</th>
                                                <th class="px-4 py-3 border-secondary text-end">Fuel Used (%)</th>
                                                <th class="px-4 py-3 border-secondary text-end">Accomplishment (%)</th>
                                                <th class="px-4 py-3 border-secondary text-center" style="width: 15%">Utilization Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-secondary">
                                            @forelse($subAccountData as $sa)
                                                @php
                                                    $saPercent = $sa['total_budget'] > 0 ? ($sa['consumed'] / $sa['total_budget']) * 100 : ($sa['consumed'] > 0 ? 100 : 0);
                                                    $saStatus = 'Healthy';
                                                    $saStatusBg = 'bg-success bg-opacity-10 text-success';
                                                    if ($saPercent >= 100) {
                                                        $saStatus = 'Exhausted';
                                                        $saStatusBg = 'bg-danger bg-opacity-10 text-danger';
                                                    } elseif ($saPercent >= 90) {
                                                        $saStatus = 'Critical';
                                                        $saStatusBg = 'bg-danger bg-opacity-10 text-danger';
                                                    } elseif ($saPercent >= 75) {
                                                        $saStatus = 'Warning';
                                                        $saStatusBg = 'bg-warning bg-opacity-10 text-warning';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3 fw-bold text-white border-secondary">
                                                        {{ $sa['name'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary text-secondary">
                                                        {{ number_format($sa['total_budget'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary text-warning">
                                                        {{ number_format($sa['consumed'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary text-info">
                                                        {{ number_format($sa['remaining'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary text-light">
                                                        {{ number_format($saPercent, 1) }}%
                                                    </td>
                                                    <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary text-info">
                                                        {{ number_format($sa['accomplishment'] ?? 0.0, 2) }}%
                                                    </td>
                                                    <td class="px-4 py-3 text-center border-secondary">
                                                        <span class="badge rounded-pill fw-bold text-uppercase small px-3 py-2 {{ $saStatusBg }}">
                                                            {{ $saStatus }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-5 text-center text-secondary border-secondary">
                                                        No sub-accounts allocated to this chargeable account.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="mt-5 pt-5 text-center text-secondary small opacity-50">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Fuel Monitoring System.
            </footer>
        </div>

        @if(count($chartLabels) > 0)
            <!-- Load Chart.js from CDN -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('subAccountChart').getContext('2d');
                    const labels = @json(collect($subAccountData)->pluck('name'));
                    const budgetValues = @json(collect($subAccountData)->pluck('total_budget'));
                    const consumedValues = @json(collect($subAccountData)->pluck('consumed'));
                    const remainingValues = @json(collect($subAccountData)->pluck('remaining'));
                    const accomplishmentValues = @json(collect($subAccountData)->pluck('accomplishment'));

                    const percentages = budgetValues.map((budget, index) => {
                        const consumed = consumedValues[index];
                        return budget > 0 ? Math.round((consumed / budget) * 100) : (consumed > 0 ? 100 : 0);
                    });

                    const fuelBackgroundColors = percentages.map(percent => {
                        if (percent >= 100) return 'rgba(239, 68, 68, 0.75)'; // Red for Exhausted
                        if (percent >= 75) return 'rgba(245, 158, 11, 0.75)';  // Orange for Warning
                        return 'rgba(52, 211, 153, 0.75)';                    // Green for Healthy
                    });

                    const fuelBorderColors = percentages.map(percent => {
                        if (percent >= 100) return 'rgb(239, 68, 68)';
                        if (percent >= 75) return 'rgb(245, 158, 11)';
                        return 'rgb(52, 211, 153)';
                    });

                    const accomplishmentBackgroundColors = accomplishmentValues.map(() => 'rgba(56, 189, 248, 0.75)'); // Material Blue
                    const accomplishmentBorderColors = accomplishmentValues.map(() => 'rgb(56, 189, 248)');

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Fuel Consumption (%)',
                                    data: percentages,
                                    backgroundColor: fuelBackgroundColors,
                                    borderColor: fuelBorderColors,
                                    borderWidth: 1,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Accomplishment (%)',
                                    data: accomplishmentValues,
                                    backgroundColor: accomplishmentBackgroundColors,
                                    borderColor: accomplishmentBorderColors,
                                    borderWidth: 1,
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y', // Makes the bar chart horizontal!
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        color: '#f1f5f9'
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            const idx = context.dataIndex;
                                            if (context.datasetIndex === 0) {
                                                const percent = percentages[idx];
                                                const budget = budgetValues[idx].toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                const consumed = consumedValues[idx].toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                const remaining = remainingValues[idx].toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                
                                                return [
                                                    `Fuel Consumption: ${percent}%`,
                                                    `  Budget: ${budget} L`,
                                                    `  Consumed: ${consumed} L`,
                                                    `  Remaining: ${remaining} L`
                                                ];
                                            } else {
                                                const accomplishment = accomplishmentValues[idx] || 0;
                                                return `Accomplishment: ${accomplishment.toFixed(2)}%`;
                                            }
                                        }
                                    },
                                    backgroundColor: '#1e293b',
                                    titleColor: '#f8fafc',
                                    bodyColor: '#f1f5f9',
                                    borderColor: '#475569',
                                    borderWidth: 1
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.1)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#94a3b8',
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Percentage (%)',
                                        color: '#94a3b8'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#f1f5f9',
                                        font: {
                                            weight: 'bold'
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endif

        @if(request()->query('print') == 1)
            <script>
                window.addEventListener('load', () => {
                    // Wait a brief moment for Chart.js to render fully
                    setTimeout(() => {
                        window.print();
                    }, 1200);
                });
            </script>
        @endif
    </body>
</html>