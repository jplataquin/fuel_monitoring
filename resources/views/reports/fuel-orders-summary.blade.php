@php
    $title = __('Fuel Orders Summary Report');
@endphp

<x-app-layout :title="$title">
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
            <h2 class="h4 font-weight-bold text-light mb-0">
                {{ $title }}
            </h2>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-light rounded-pill px-4 shadow-sm fw-bold text-uppercase small">
                    <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container-xl" style="max-width: 1280px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                
                <!-- Report Filter Form -->
                <div class="card-header bg-dark border-secondary p-4 d-print-none">
                    <form action="{{ route('reports.fuel-orders') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small shadow-sm py-2">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="card-body p-0 text-light">
                    @if($fuelOrders->isNotEmpty())
                        <div class="p-4 border-bottom border-secondary d-print-none">
                            <h3 class="h5 fw-bold text-light mb-4">Fuel Consumption Trend</h3>
                            <div class="w-100" style="height: 320px;">
                                <canvas id="fuelConsumptionChart"></canvas>
                            </div>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-striped mb-0 border-secondary">
                            <thead class="table-secondary">
                                <tr class="text-uppercase small fw-bold tracking-widest">
                                    <th class="px-4 py-3 border-secondary">ID</th>
                                    <th class="px-4 py-3 border-secondary">Asset</th>
                                    <th class="px-4 py-3 border-secondary">Period</th>
                                    <th class="px-4 py-3 border-secondary text-end">Say Qty</th>
                                    <th class="px-4 py-3 border-secondary text-end">Actual Qty</th>
                                    <th class="px-4 py-3 border-secondary text-end">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="border-secondary">
                                @php
                                    $totalSay = 0;
                                    $totalActual = 0;
                                    $totalVariance = 0;
                                @endphp
                                @forelse($fuelOrders as $order)
                                    @php
                                        $totalSay += $order->say_quantity;
                                        $totalActual += $order->actual_quantity;
                                        $variance = $order->actual_quantity - $order->say_quantity;
                                        $variancePercent = $order->say_quantity > 0 ? ($variance / $order->say_quantity) * 100 : 0;
                                        $totalVariance += $variance;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-monospace fw-bold text-primary border-secondary">
                                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-4 py-3 small border-secondary">
                                            {{ $order->asset->fleet_no ?? 'N/A' }} 
                                            <span class="text-secondary small">({{ $order->asset->plate_no ?? 'N/A' }})</span>
                                        </td>
                                        <td class="px-4 py-3 small border-secondary">
                                            {{ Carbon\Carbon::parse($order->date_from)->format('M d') }} - {{ Carbon\Carbon::parse($order->date_to)->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-secondary border-secondary">
                                            {{ number_format($order->say_quantity, 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold text-success border-secondary">
                                            {{ number_format($order->actual_quantity, 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-end font-monospace fw-bold border-secondary {{ $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-primary' : 'text-secondary') }}" style="{{ $variance < 0 ? 'color: #0d6efd !important;' : '' }}">
                                            {{ ($variance > 0 ? '+' : '') . number_format($variancePercent, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-5 text-center border-secondary">
                                            <div class="py-5">
                                                <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                                    <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="fw-bold mb-1">No report data to display.</p>
                                                <p class="small text-secondary mb-0">Please select a date range to generate the report.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if($fuelOrders->count() > 0)
                                    <tr class="table-primary border-top border-secondary">
                                        <td colspan="3" class="px-4 py-4 text-end h6 fw-bold text-uppercase tracking-widest mb-0 border-secondary">
                                            Grand Total:
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace fw-bold text-secondary border-secondary">
                                            {{ number_format($totalSay, 2) }} L
                                        </td>
                                        <td class="px-4 py-4 text-end font-monospace h5 fw-bold text-success border-secondary mb-0">
                                            {{ number_format($totalActual, 2) }} L
                                        </td>
                                        @php $totalVariancePercent = $totalSay > 0 ? ($totalVariance / $totalSay) * 100 : 0; @endphp
                                        <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary {{ $totalVariance > 0 ? 'text-danger' : ($totalVariance < 0 ? 'text-primary' : 'text-secondary') }}" style="{{ $totalVariance < 0 ? 'color: #0d6efd !important;' : '' }}">
                                            {{ ($totalVariance > 0 ? '+' : '') . number_format($totalVariancePercent, 2) }}%
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($fuelOrders->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('fuelConsumptionChart').getContext('2d');
                const chartData = @json($chartData);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Say Quantity (L)',
                                data: chartData.say,
                                borderColor: '#60a5fa', // Blue 400
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                pointBackgroundColor: '#3b82f6', // Blue 500
                                tension: 0.3,
                                fill: false,
                                spanGaps: true
                            },
                            {
                                label: 'Actual Consumption (L)',
                                data: chartData.actual,
                                borderColor: '#34d399', // Emerald 400
                                backgroundColor: 'rgba(52, 211, 153, 0.1)',
                                borderWidth: 3,
                                pointBackgroundColor: '#10b981',
                                tension: 0.3,
                                fill: true,
                                spanGaps: true
                            },
                            {
                                label: 'Projection Trend',
                                data: chartData.trend,
                                borderColor: '#D0BCFF', // Primary Purple
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                tension: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#CAC4D0'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(73, 69, 79, 0.2)',
                                },
                                ticks: {
                                    color: '#CAC4D0'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(73, 69, 79, 0.2)',
                                },
                                ticks: {
                                    color: '#CAC4D0'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>