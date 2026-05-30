<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h2 fw-bold text-light mb-0">
                {{ __('Dashboard') }}
            </h2>
            @if(Auth::user()->role === 'administrator')
                <a href="{{ route('public-dashboard-links.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold text-uppercase small shadow-sm">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                    Share Dashboard
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="vstack gap-5">
                <!-- Welcome Section -->
                <div class="position-relative overflow-hidden rounded-4 p-4 p-md-5 shadow-lg" style="background-color: #D0BCFF;">
                    <div class="position-relative z-1">
                        <h3 class="display-5 fw-bold text-dark mb-2 tracking-tight">
                            Good day, {{ Auth::user()->name }}
                        </h3>
                        <p class="text-dark text-opacity-75 fs-5 fw-medium mb-4 mb-md-5" style="max-width: 600px;">
                            Monitor and manage your fleet utilization with precision and ease.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-bold text-uppercase tracking-widest shadow-sm" style="font-size: 0.65rem; color: #D0BCFF; background-color: #381E72;">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                    </div>
                    <!-- Decorative element -->
                    <div class="position-absolute top-0 end-0 mt-n5 me-n5 bg-white bg-opacity-25 rounded-circle" style="width: 320px; height: 320px; filter: blur(80px);"></div>
                    <div class="position-absolute bottom-0 end-0 mb-4 me-5 d-none d-lg-block opacity-25">
                        <x-application-logo class="fill-current text-dark" style="width: 200px; height: 200px;" />
                    </div>
                </div>

                <!-- Budget Dashboard Section -->
                <div class="vstack gap-4">
                    
                    <!-- Filter Form -->
                    <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Chargeable Account</label>
                                    <select name="account_id" id="account_id" class="form-select bg-dark text-light border-secondary">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
                                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control bg-dark text-light border-secondary" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
                                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control bg-dark text-light border-secondary" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small shadow-sm py-2">
                                        Filter Dashboard
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Charts Grid -->
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
                                <p class="text-secondary mb-0">There are no budgets or consumption records for the selected parameters.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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
                    
                    // Cap budgeted for visualization if it exceeds budget
                    let displayBudgeted = Math.min(totalBudget, budgeted);
                    let remaining = Math.max(0, totalBudget - budgeted);
                    let overage = Math.max(0, budgeted - totalBudget);

                    let datasetsData = [];
                    let backgroundColor = [];
                    let labels = [];

                    const utilPercent = totalBudget > 0 ? (budgeted / totalBudget) * 100 : (budgeted > 0 ? 100 : 0);
                    
                    let budgetedColor = '#34d399'; // Emerald 400 (Good)
                    if (utilPercent >= 90) {
                        budgetedColor = '#ef4444'; // Red 500 (Critical)
                    } else if (utilPercent >= 75) {
                        budgetedColor = '#f59e0b'; // Amber 500 (Warning)
                    }

                    if (totalBudget === 0 && (budgeted > 0 || unbudgeted > 0)) {
                        // Entirely unbudgeted or budgeted without a set budget
                        if (budgeted > 0) {
                            datasetsData.push(budgeted);
                            backgroundColor.push(budgetedColor);
                            labels.push('Budgeted Consumed (No Limit)');
                        }
                        if (unbudgeted > 0) {
                            datasetsData.push(unbudgeted);
                            backgroundColor.push('#8b5cf6'); // Purple for unbudgeted
                            labels.push('Unbudgeted Consumed');
                        }
                    } else {
                        // Consumed within budget
                        datasetsData.push(displayBudgeted);
                        backgroundColor.push(budgetedColor);
                        labels.push('Budgeted Consumed');

                        // Remaining budget
                        if (remaining > 0) {
                            datasetsData.push(remaining);
                            backgroundColor.push('rgba(73, 69, 79, 0.3)');
                            labels.push('Remaining Budget');
                        }

                        // Overage
                        if (overage > 0) {
                            datasetsData.push(overage);
                            backgroundColor.push('#7f1d1d'); // Dark red for overage
                            labels.push('Overage');
                        }

                        // Unbudgeted
                        if (unbudgeted > 0) {
                            datasetsData.push(unbudgeted);
                            backgroundColor.push('#8b5cf6'); // Purple for unbudgeted
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
                                legend: {
                                    display: false // Hide legend to save space
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
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
            </div>
        </div>
    </div>

    <style>
        .hover-opacity:hover {
            opacity: 0.85;
        }
    </style>
</x-app-layout>
