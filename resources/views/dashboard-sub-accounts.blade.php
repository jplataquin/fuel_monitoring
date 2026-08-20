@php
    $title = __('Sub-Account Balances - ') . $chargeableAccount->name;
    $isPrint = request()->query('print') == 1;
    $layout = $isPrint ? 'print-layout' : 'app-layout';
@endphp

<x-dynamic-component :component="$layout" :title="$title">
    @if(!$isPrint)
        <x-slot name="header">
            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
                <div>
                    <h2 class="h4 font-weight-bold text-light mb-1">
                        {{ $title }}
                    </h2>
                    <p class="text-secondary small mb-0 text-uppercase tracking-wider fw-bold">
                        Account Type: {{ $chargeableAccount->classification }}
                        @if($chargeableAccount->classification === 'Scoped')
                            ({{ $chargeableAccount->start_date ? $chargeableAccount->start_date->format('M d, Y') : 'N/A' }} - {{ $chargeableAccount->end_date ? $chargeableAccount->end_date->format('M d, Y') : 'N/A' }})
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2 d-print-none">
                    <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-outline-info rounded-pill px-4 shadow-sm fw-bold text-uppercase small d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Print Dashboard
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold text-uppercase small d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </x-slot>
    @endif

    <div class="{{ $isPrint ? 'py-1' : 'py-5' }}">
        <div class="container-fluid px-md-5">
            <!-- Print-Only Header -->
            <div class="{{ $isPrint ? 'mb-4 pb-3 border-bottom border-dark border-opacity-25' : 'd-none d-print-block mb-5 pb-3 border-bottom border-dark border-opacity-25' }}">
                <h1 class="h2 fw-bold text-dark mb-1">
                    {{ $title }}
                </h1>
                <p class="text-secondary small mb-0 text-uppercase tracking-wider fw-bold">
                    Account Type: {{ $chargeableAccount->classification }}
                    @if($chargeableAccount->classification === 'Scoped')
                        ({{ $chargeableAccount->start_date ? $chargeableAccount->start_date->format('M d, Y') : 'N/A' }} - {{ $chargeableAccount->end_date ? $chargeableAccount->end_date->format('M d, Y') : 'N/A' }})
                    @endif
                </p>
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
                                <div class="text-center py-5">
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
                                $totalRemaining = $totalBudgets - ($totalActualQuantity ?? 0);
                                $utilizationPercent = $totalBudgets > 0 ? ($totalConsumed / $totalBudgets) * 100 : 0;
                            @endphp

                            <div class="vstack gap-3">
                                <div class="p-3 bg-secondary bg-opacity-10 rounded-3 text-center">
                                    <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 0.65rem;">Total Allocated Budget</div>
                                    <div class="h3 font-monospace fw-bold text-light mb-0">{{ number_format($totalBudgets, 2) }} L</div>
                                </div>
                                <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                                    <div class="text-success small fw-bold text-uppercase tracking-wider mb-1" style="font-size: 0.65rem; color: #34d399 !important;">Total Calculated Quantity</div>
                                    <div class="h3 font-monospace fw-bold text-success mb-0" style="color: #34d399 !important;">{{ number_format($totalConsumed, 2) }} L</div>
                                </div>
                                <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-center position-relative">
                                    <div class="text-warning small fw-bold text-uppercase tracking-wider mb-1 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.65rem; color: #fbbf24 !important;">
                                        Total Actual Quantity
                                        <a href="{{ route('fuel-orders.index', ['chargeable_account_id' => $chargeableAccount->id, 'status' => 'DONE']) }}" class="text-warning hover-opacity-75 transition-all d-print-none" title="View associated fuel orders">
                                            <i class="bi bi-box-arrow-up-right" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </div>
                                    <div class="h3 font-monospace fw-bold text-warning mb-0" style="color: #fbbf24 !important;">{{ number_format($totalActualQuantity ?? 0, 2) }} L</div>
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
                                <table class="table table-dark table-hover mb-0 border-secondary align-middle">
                                    <thead class="table-secondary">
                                        <tr class="text-uppercase small fw-bold tracking-widest text-nowrap">
                                            <th class="px-4 py-3 border-secondary">Sub-Account Name</th>
                                            <th class="px-4 py-3 border-secondary text-end">Total Budget (L)</th>
                                            <th class="px-4 py-3 border-secondary text-end">Consumed (L)</th>
                                            <th class="px-4 py-3 border-secondary text-end">Remaining (L)</th>
                                            <th class="px-4 py-3 border-secondary text-end">Fuel Used (%)</th>
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
                                            <tr onclick="window.location='{{ route('sub-accounts.show', $sa['id']) }}'" style="cursor: pointer;">
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
                                                <td class="px-4 py-3 text-center border-secondary">
                                                    <span class="badge rounded-pill fw-bold text-uppercase small px-3 py-2 {{ $saStatusBg }}">
                                                        {{ $saStatus }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-5 text-center text-secondary border-secondary">
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
</x-dynamic-component>