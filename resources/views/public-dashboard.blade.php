<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Fuel Budget Dashboard') }} - {{ config('app.name', 'Laravel') }}</title>

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
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 pb-5">
            
            <!-- Minimalist Header -->
            <header class="public-header py-4 mb-5 shadow-sm">
                <div class="container-xl">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="h3 fw-bold mb-0 tracking-tight">Fuel Budget Monitoring</h1>
                            <p class="small fw-medium mb-0 opacity-75">Live Public Dashboard: {{ $link->name ?? 'Shared Overview' }}</p>
                        </div>
                        <div class="d-none d-sm-block">
                            <x-application-logo style="width: 40px; height: 40px;" class="fill-current text-dark" />
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-xl">
                <div class="vstack gap-5">
                    
                    <!-- Info Alert -->
                    <div class="alert bg-primary bg-opacity-10 border-primary border-opacity-25 text-primary small d-flex align-items-center gap-3 rounded-4 px-4 py-3">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            Showing data for the period of <strong>{{ Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}</strong> to <strong>{{ Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</strong>.
                        </div>
                    </div>

                    <!-- Budget Cards Grid -->
                    @if(count($chartData) > 0)
                        <div class="row g-4">
                            @foreach($chartData as $index => $data)
                                @php
                                    $totalBudget = $data['total_budget'];
                                    $budgeted = $data['budgeted_fuel'];
                                    $unbudgeted = $data['unbudgeted_fuel'];
                                    $consumed = $data['total_calculated_fuel'];
                                    
                                    $remaining = max(0, $totalBudget - $budgeted);
                                    $overage = max(0, $budgeted - $totalBudget);
                                    $utilizationPercent = $totalBudget > 0 ? min(100, ($budgeted / $totalBudget) * 100) : ($budgeted > 0 ? 100 : 0);
                                    
                                    // Colors based on utilization
                                    $statusColor = '#34d399'; // Green
                                    if ($utilizationPercent >= 90) {
                                        $statusColor = '#ef4444'; // Red
                                    } elseif ($utilizationPercent >= 75) {
                                        $statusColor = '#f59e0b'; // Orange
                                    }
                                @endphp
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card h-100 bg-dark border-secondary border-opacity-50 rounded-4 p-4 shadow-sm">
                                        <h3 class="h5 fw-bold text-light mb-3 text-center text-truncate" title="{{ $data['name'] }}">
                                            {{ $data['name'] }}
                                        </h3>
                                        
                                        <div class="position-relative d-flex justify-content-center align-items-center mb-4" style="height: 200px;">
                                            <canvas id="chart-{{ $index }}"></canvas>
                                            <div class="position-absolute d-flex flex-column justify-content-center align-items-center" style="pointer-events: none;">
                                                <span class="fs-4 fw-bold" style="color: {{ $statusColor }};">
                                                    {{ number_format($utilizationPercent, 0) }}%
                                                </span>
                                                <span class="small text-secondary text-uppercase tracking-widest" style="font-size: 0.65rem;">Used</span>
                                            </div>
                                        </div>

                                        <div class="vstack gap-2">
                                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                                                <span class="text-secondary small fw-medium text-uppercase tracking-wider">Total Budget</span>
                                                <span class="text-light font-monospace fw-bold">{{ number_format($totalBudget, 2) }} L</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                                                <span class="text-secondary small fw-medium text-uppercase tracking-wider">Budgeted Consumed</span>
                                                <span class="font-monospace fw-bold" style="color: {{ $statusColor }};">{{ number_format($budgeted, 2) }} L</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-secondary border-opacity-25">
                                                <span class="text-secondary small fw-medium text-uppercase tracking-wider">Unbudgeted Consumed</span>
                                                <span class="font-monospace fw-bold" style="color: #8b5cf6;">{{ number_format($unbudgeted, 2) }} L</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pb-2">
                                                <span class="text-secondary small fw-medium text-uppercase tracking-wider">Remaining</span>
                                                <span class="font-monospace fw-bold" style="color: rgba(150, 150, 150, 0.8);">{{ number_format($remaining, 2) }} L</span>
                                            </div>
                                            @if($overage > 0)
                                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-danger border-opacity-25">
                                                    <span class="text-danger small fw-bold text-uppercase tracking-wider">Overage</span>
                                                    <span class="font-monospace fw-bold" style="color: #7f1d1d;">{{ number_format($overage, 2) }} L</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card bg-dark border-secondary shadow-sm rounded-4">
                            <div class="card-body text-center py-5">
                                <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                    <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h3 class="h5 fw-bold text-light mb-1">No Data Available</h3>
                                <p class="text-secondary mb-0">There are no records for the current period.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <footer class="mt-5 pt-5 text-center text-secondary small opacity-50">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Fuel Monitoring System.
            </footer>
        </div>

        @if(count($chartData) > 0)
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const chartData = @json($chartData);
                    
                    chartData.forEach((data, index) => {
                        const ctx = document.getElementById('chart-' + index).getContext('2d');
                        
                        const totalBudget = data.total_budget;
                        const budgeted = data.budgeted_fuel;
                        const unbudgeted = data.unbudgeted_fuel;
                        
                        let displayBudgeted = Math.min(totalBudget, budgeted);
                        let remaining = Math.max(0, totalBudget - budgeted);
                        let overage = Math.max(0, budgeted - totalBudget);

                        let datasetsData = [];
                        let backgroundColor = [];
                        let labels = [];

                        const utilPercent = totalBudget > 0 ? (budgeted / totalBudget) * 100 : (budgeted > 0 ? 100 : 0);
                        
                        let budgetedColor = '#34d399';
                        if (utilPercent >= 90) {
                            budgetedColor = '#ef4444';
                        } else if (utilPercent >= 75) {
                            budgetedColor = '#f59e0b';
                        }

                        if (totalBudget === 0 && (budgeted > 0 || unbudgeted > 0)) {
                            if (budgeted > 0) {
                                datasetsData.push(budgeted);
                                backgroundColor.push(budgetedColor);
                                labels.push('Budgeted Consumed (No Limit)');
                            }
                            if (unbudgeted > 0) {
                                datasetsData.push(unbudgeted);
                                backgroundColor.push('#8b5cf6');
                                labels.push('Unbudgeted Consumed');
                            }
                        } else {
                            datasetsData.push(displayBudgeted);
                            backgroundColor.push(budgetedColor);
                            labels.push('Budgeted Consumed');

                            if (remaining > 0) {
                                datasetsData.push(remaining);
                                backgroundColor.push('rgba(73, 69, 79, 0.3)');
                                labels.push('Remaining Budget');
                            }

                            if (overage > 0) {
                                datasetsData.push(overage);
                                backgroundColor.push('#7f1d1d');
                                labels.push('Overage');
                            }

                            if (unbudgeted > 0) {
                                datasetsData.push(unbudgeted);
                                backgroundColor.push('#8b5cf6');
                                labels.push('Unbudgeted Consumed');
                            }
                        }

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: datasetsData,
                                    backgroundColor: backgroundColor,
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '75%',
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.label || '';
                                                if (label) label += ': ';
                                                if (context.parsed !== null) {
                                                    label += context.parsed.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' L';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    });
                });
            </script>
        @endif
    </body>
</html>
