@php
    $title = __('Fuel Orders Summary Report');
@endphp

<x-print-layout :title="$title">
    <style>
        .print-container { padding: 0.25rem !important; }
        .table th, .table td { 
            padding: 2px 4px !important; 
            font-size: 8.5px !important;
            line-height: 1.1 !important;
        }
        .mb-4 { margin-bottom: 0.5rem !important; }
        .badge { font-size: 8px !important; padding: 2px 4px !important; }
    </style>

    <div class="card border-0">
        <!-- Report Content -->
        <div class="card-body p-0 text-dark">
            <div class="mb-2">
                @if($dateFrom || $dateTo)
                    <span class="badge border text-dark">Period: {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}</span>
                @endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered mb-0 border-secondary">
                    <thead class="table-light">
                        <tr class="text-uppercase small fw-bold">
                            <th style="width: 10%;">ID</th>
                            <th>Asset</th>
                            <th>Period</th>
                            <th class="text-end" style="width: 12%;">Say Qty</th>
                            <th class="text-end" style="width: 12%;">Actual Qty</th>
                            <th class="text-end" style="width: 12%;">Variance</th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        @php
                            $totalSay = 0; $totalActual = 0; $totalVariance = 0;
                        @endphp
                        @forelse($fuelOrders as $order)
                            @php
                                $totalSay += $order->say_quantity;
                                $totalActual += $order->actual_quantity;
                                $variance = $order->actual_quantity - $order->say_quantity;
                                $totalVariance += $variance;
                            @endphp
                            <tr>
                                <td class="font-monospace fw-bold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $order->asset->fleet_no ?? 'N/A' }} <span class="text-secondary small" style="font-size: 7px;">({{ $order->asset->plate_no ?? 'N/A' }})</span></td>
                                <td>{{ Carbon\Carbon::parse($order->date_from)->format('M d') }} - {{ Carbon\Carbon::parse($order->date_to)->format('M d, Y') }}</td>
                                <td class="text-end">{{ number_format($order->say_quantity, 2) }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($order->actual_quantity, 2) }}</td>
                                <td class="text-end font-monospace {{ $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-info' : 'text-secondary') }}">
                                    {{ ($variance > 0 ? '+' : '') . number_format($variance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No data found.</td></tr>
                        @endforelse
                        
                        @if($fuelOrders->count() > 0)
                            <tr class="table-primary fw-bold">
                                <td colspan="3" class="text-end">GRAND TOTAL:</td>
                                <td class="text-end">{{ number_format($totalSay, 2) }} L</td>
                                <td class="text-end text-success">{{ number_format($totalActual, 2) }} L</td>
                                <td class="text-end {{ $totalVariance > 0 ? 'text-danger' : ($totalVariance < 0 ? 'text-info' : 'text-secondary') }}">
                                    {{ ($totalVariance > 0 ? '+' : '') . number_format($totalVariance, 2) }} L
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-print-layout>