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
            .hover-bg-light:hover {
                background-color: rgba(255, 255, 255, 0.05) !important;
            }
            .transition-all {
                transition: all 0.2s ease-in-out;
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
                    <div id="budget-grid-container">
                        @include('partials.dashboard-grid')
                    </div>

                    <!-- Asset Variance Section -->
                    <div class="vstack gap-4 mt-2">
                        <div class="d-flex align-items-center gap-3">
                            <h3 class="h5 fw-bold text-light mb-0 text-uppercase tracking-widest">Asset Performance</h3>
                            <div class="flex-grow-1 border-top border-secondary border-opacity-25"></div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 text-uppercase fw-bold tracking-widest" style="font-size: 0.6rem;">
                                Live Variance
                            </span>
                        </div>
                        
                        <div id="asset-grid-container">
                            @include('partials.asset-grid')
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="mt-5 pt-5 text-center text-secondary small opacity-50">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Fuel Monitoring System.
            </footer>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            function renderDashboardCharts(chartData) {
                chartData.forEach((data, index) => {
                    const canvas = document.getElementById('chart-' + index);
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    
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
            }

            async function updateDashboard() {
                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        document.getElementById('budget-grid-container').innerHTML = data.budget_html;
                        document.getElementById('asset-grid-container').innerHTML = data.asset_html;
                        renderDashboardCharts(data.chart_data);
                        console.log('Public dashboard auto-updated at ' + new Date().toLocaleTimeString());
                    }
                } catch (error) {
                    console.error('Dashboard update failed:', error);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                renderDashboardCharts(@json($chartData));
                setInterval(updateDashboard, 300000);
            });
        </script>
    </body>
</html>
