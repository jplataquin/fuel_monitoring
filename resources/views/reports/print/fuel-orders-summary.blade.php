@php
    $title = __('Fuel Orders Summary Report');
@endphp

<x-print-layout :title="$title">
    <div class="card border-0">
        <!-- Report Content -->
        <div class="card-body p-0 text-dark">
            <div class="mb-4">
                @if($dateFrom || $dateTo)
                    <span class="badge bg-light text-dark border">Period: {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}</span>
                @endif
            </div>
            
            <div class="table-responsive d-print-overflow-visible">
                <table class="table table-bordered mb-0 d-print-table d-print-text-dark border-secondary">
                    <thead class="table-light">
                        <tr class="text-uppercase small fw-bold tracking-widest">
                            <th class="px-4 py-3 border-secondary d-print-p-1">ID</th>
                            <th class="px-4 py-3 border-secondary d-print-p-1">Asset</th>
                            <th class="px-4 py-3 border-secondary d-print-p-1">Period</th>
                            <th class="px-4 py-3 border-secondary text-end d-print-p-1">Say Qty</th>
                            <th class="px-4 py-3 border-secondary text-end d-print-p-1">Actual Qty</th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        @php
                            $totalSay = 0;
                            $totalActual = 0;
                        @endphp
                        @forelse($fuelOrders as $order)
                            @php
                                $totalSay += $order->say_quantity;
                                $totalActual += $order->actual_quantity;
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-5 text-center border-secondary">
                                    No records found for the selected parameters.
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
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>