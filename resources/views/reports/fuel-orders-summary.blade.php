<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Fuel Orders Summary Report') }}
            </h2>
            <div class="flex items-center space-x-3 print:hidden">
                <button onclick="window.print()" class="inline-flex items-center px-6 py-2.5 bg-[#CCC2DC] border border-transparent rounded-full font-bold text-xs text-black uppercase tracking-widest hover:bg-[#E6E1E5] focus:outline-none focus:ring-2 focus:ring-[#CCC2DC] transition shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 print:max-w-full print:px-0">
            
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl print:bg-white print:border-none print:shadow-none print:rounded-none">
                
                <!-- Report Filter Form -->
                <div class="p-8 border-b border-[#49454F]/50 bg-[#2D2930] print:hidden">
                    <form action="{{ route('reports.fuel-orders') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="w-full md:w-1/3">
                            <label for="date_from" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]" required>
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="date_to" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]" required>
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-[#D0BCFF] border border-transparent rounded-full font-bold text-xs text-black uppercase tracking-widest hover:bg-[#EADDFF] transition ease-in-out duration-150 shadow-md">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="p-0 text-gray-100 print:text-black">
                    <div class="hidden print:block p-8 pb-4 text-center">
                        <h2 class="text-2xl font-black uppercase tracking-widest">Fuel Orders Summary Report</h2>
                        @if($dateFrom || $dateTo)
                            <p class="text-sm font-bold mt-2">Date: {{ $dateFrom ?? 'Any' }} - {{ $dateTo ?? 'Any' }}</p>
                        @endif
                    </div>

                    @if($fuelOrders->isNotEmpty())
                    <div class="p-8 border-b border-[#49454F]/50 print:border-none">
                        <h3 class="text-lg font-bold text-[#E6E1E5] mb-4 print:text-black">Fuel Consumption Trend</h3>
                        <div class="w-full h-80">
                            <canvas id="fuelConsumptionChart"></canvas>
                        </div>
                    </div>
                    @endif
                    
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="min-w-full divide-y divide-[#49454F]/50 print:divide-black print:border-collapse print:border print:border-black">
                            <thead class="bg-[#49454F]/10 print:bg-gray-100 print:text-black">
                                <tr>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">ID</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Asset</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Period</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Say Qty</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Actual Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F] print:bg-white print:divide-black">
                                @php
                                    $totalSay = 0;
                                    $totalActual = 0;
                                @endphp
                                @forelse($fuelOrders as $order)
                                    @php
                                        $totalSay += $order->say_quantity;
                                        $totalActual += $order->actual_quantity;
                                    @endphp
                                    <tr class="hover:bg-[#49454F]/10 transition-colors print:hover:bg-transparent">
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs font-mono font-bold text-[#D0BCFF] print:text-black">
                                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs text-[#E6E1E5] print:text-black">
                                            {{ $order->asset->fleet_no ?? 'N/A' }} 
                                            <span class="text-[#CAC4D0] print:text-gray-600 text-[10px]">({{ $order->asset->plate_no ?? 'N/A' }})</span>
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs text-[#E6E1E5] print:text-black">
                                            {{ Carbon\Carbon::parse($order->date_from)->format('M d') }} - {{ Carbon\Carbon::parse($order->date_to)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#CAC4D0] print:text-black">
                                            {{ number_format($order->say_quantity, 2) }} L
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-emerald-400 print:text-emerald-800">
                                            {{ number_format($order->actual_quantity, 2) }} L
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center print:px-2 print:py-2 print:border print:border-black">
                                            <div class="flex flex-col items-center justify-center print:hidden">
                                                <div class="bg-[#49454F]/20 w-16 h-16 rounded-2xl flex items-center justify-center mb-4">
                                                    <svg class="w-8 h-8 text-[#CAC4D0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="text-sm font-bold text-[#E6E1E5]">No report data to display.</p>
                                                <p class="text-xs text-[#CAC4D0] mt-1">Please select a date range to generate the report.</p>
                                            </div>
                                            <div class="hidden print:block text-black">
                                                No records found for the selected parameters.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if($fuelOrders->count() > 0)
                                    <tr class="bg-[#D0BCFF]/10 print:bg-gray-100 border-t-2 border-[#D0BCFF]/30 print:border-black">
                                        <td colspan="3" class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-black text-[#E6E1E5] print:text-black uppercase tracking-widest">
                                            Grand Total:
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#CAC4D0] print:text-black">
                                            {{ number_format($totalSay, 2) }} L
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-emerald-400 print:text-emerald-800">
                                            {{ number_format($totalActual, 2) }} L
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
                                backgroundColor: 'rgba(52, 211, 153, 0.2)',
                                borderWidth: 3,
                                pointBackgroundColor: '#10b981',
                                tension: 0.3,
                                fill: true,
                                spanGaps: true // Connect the line even if there's a null value
                            },
                            {
                                label: 'Projection Trend',
                                data: chartData.trend,
                                borderColor: '#D0BCFF', // Primary Purple
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [5, 5], // Dashed line
                                pointRadius: 0, // Hide points for the trendline
                                tension: 0, // Straight line
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
                                    color: '#E6E1E5'
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
                                    color: 'rgba(73, 69, 79, 0.3)', // #49454F
                                },
                                ticks: {
                                    color: '#CAC4D0'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(73, 69, 79, 0.3)', // #49454F
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