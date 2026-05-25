@php
    $title = __('Asset Data Report');
@endphp

<x-print-layout :title="$title">
    <style>
        /* Extreme compression for print */
        .print-container { padding: 0.25rem !important; }
        .table th, .table td { 
            padding: 2px 4px !important; 
            font-size: 8.5px !important;
            line-height: 1.1 !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }
        .card-body { padding: 0 !important; }
        .mb-4 { margin-bottom: 0.5rem !important; }
        .mt-4 { margin-top: 0.5rem !important; }
        .p-4 { padding: 0.5rem !important; }
        .py-4 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
        .h2, .h3, .h5, .h6 { margin-bottom: 0.25rem !important; font-size: 10px !important; }
        .badge { font-size: 8px !important; padding: 2px 4px !important; }
        .row.g-4 { --bs-gutter-x: 0.5rem; --bs-gutter-y: 0.25rem; }
    </style>

    <div class="card border-0">
        <!-- Report Content -->
        <div class="card-body text-dark">
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    @if($assetId)
                        <span class="badge border me-1">Asset: {{ $assets->firstWhere('id', $assetId)?->fleet_no }}</span>
                    @endif
                    @if($dateFrom || $dateTo)
                        <span class="badge border">Period: {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}</span>
                    @endif
                </div>
            </div>
            
            @if($assetId && $selectedAsset = $assets->firstWhere('id', $assetId))
                <div class="p-2 border border-secondary mb-2">
                    <h3 class="small fw-bold text-uppercase tracking-widest mb-1">Technical Specs</h3>
                    <div class="row">
                        <div class="col-3">
                            <span class="text-secondary small" style="font-size: 7px;">Type:</span> {{ $selectedAsset->assetType->name ?? 'N/A' }}
                        </div>
                        <div class="col-3">
                            <span class="text-secondary small" style="font-size: 7px;">Fleet:</span> {{ $selectedAsset->fleet_no }}
                        </div>
                        <div class="col-3">
                            <span class="text-secondary small" style="font-size: 7px;">Fuel/Cap:</span> {{ $selectedAsset->fuel_type }} ({{ $selectedAsset->tank_capacity ? number_format($selectedAsset->tank_capacity, 1) . 'L' : 'N/A' }})
                        </div>
                        <div class="col-3 text-end">
                            <span class="text-secondary small" style="font-size: 7px;">Factors:</span> {{ number_format($selectedAsset->fuel_factor_km, 2) }} / {{ number_format($selectedAsset->fuel_factor_hr, 2) }}
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-bordered mb-1">
                    <thead class="table-light">
                        <tr class="text-uppercase fw-bold">
                            <th style="width: 10%;">Date</th>
                            <th>Particulars</th>
                            <th>Account / Sub</th>
                            <th style="width: 8%;">Type</th>
                            <th class="text-end" style="width: 8%;">KM</th>
                            <th class="text-end" style="width: 8%;">HR</th>
                            <th class="text-end" style="width: 9%;">Comp. Qty</th>
                            <th class="text-end" style="width: 9%;">Say Qty</th>
                            <th class="text-end" style="width: 9%;">Actual Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotalKm = 0;
                            $grandTotalHours = 0;
                            $grandTotalActual = 0;
                            $grandTotalSay = 0;
                        @endphp
                        @forelse($entries as $fuelOrderId => $group)
                            @php
                                $fuelOrder = $group->first()->fuelOrder;
                                $groupTotalKm = 0;
                                $groupTotalHours = 0;
                                $groupTotalQty = 0;
                                $grandTotalActual += $fuelOrder->actual_quantity;
                                $grandTotalSay += $fuelOrder->say_quantity;
                            @endphp
                            <tr class="table-active fw-bold">
                                <td colspan="9" class="py-1">
                                    FUEL ORDER #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }} 
                                    <span class="fw-normal small ms-2">({{ $fuelOrder->created_at->format('M d, Y') }})</span>
                                </td>
                            </tr>

                            @foreach($group as $entry)
                                @php
                                    $qty = 0; $calcKm = 0; $calcHours = 0;
                                    $calcType = strtolower($entry->calculation_type ?? '');
                                    
                                    if (str_contains($calcType, 'kilometer')) {
                                        $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                                        $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                                    } elseif (str_contains($calcType, 'actual')) {
                                        if ($entry->end_time && $entry->start_time) {
                                            $start = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                                            $end = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                                            $calcHours = max(0, $start->diffInMinutes($end) / 60);
                                            $qty = $calcHours * $entry->fuel_factor_hr;
                                        }
                                    } elseif (str_contains($calcType, 'hour')) {
                                        $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                                        $qty = $calcHours * $entry->fuel_factor_hr;
                                    }
                                    
                                    $groupTotalKm += $calcKm; $groupTotalHours += $calcHours; $groupTotalQty += $qty;
                                    $grandTotalKm += $calcKm; $grandTotalHours += $calcHours;
                                @endphp
                                <tr>
                                    <td>{{ $entry->date->format('M d, Y') }}</td>
                                    <td>{{ $entry->particulars }}</td>
                                    <td>{{ $entry->chargeableAccount->name ?? '—' }}</td>
                                    <td class="text-center small">{{ $entry->calculation_type }}</td>
                                    <td class="text-end">{{ $calcKm > 0 ? number_format($calcKm, 1) : '-' }}</td>
                                    <td class="text-end">{{ $calcHours > 0 ? number_format($calcHours, 1) : '-' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($qty, 1) }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-end">Sub-Total:</td>
                                <td class="text-end">{{ $groupTotalKm > 0 ? number_format($groupTotalKm, 1) : '-' }}</td>
                                <td class="text-end">{{ $groupTotalHours > 0 ? number_format($groupTotalHours, 1) : '-' }}</td>
                                <td class="text-end">{{ number_format($groupTotalQty, 1) }}</td>
                                <td class="text-end">{{ number_format($fuelOrder->say_quantity, 1) }}</td>
                                <td class="text-end">{{ number_format($fuelOrder->actual_quantity, 1) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4">No records found.</td></tr>
                        @endforelse
                        
                        @if($entries->count() > 0)
                            <tr class="table-primary fw-bold" style="background-color: #f0f7ff !important;">
                                <td colspan="4" class="text-end">GRAND TOTAL:</td>
                                <td class="text-end">{{ $grandTotalKm > 0 ? number_format($grandTotalKm, 1) : '-' }}</td>
                                <td class="text-end">{{ $grandTotalHours > 0 ? number_format($grandTotalHours, 1) : '-' }}</td>
                                <td class="text-end">{{ number_format($grandTotalKm > 0 || $grandTotalHours > 0 ? $grandTotalKm / max(1, $selectedAsset->fuel_factor_km ?? 1) + ($grandTotalHours * ($selectedAsset->fuel_factor_hr ?? 0)) : 0, 1) }} L</td>
                                <td class="text-end">{{ number_format($grandTotalSay, 1) }} L</td>
                                <td class="text-end">{{ number_format($grandTotalActual, 1) }} L</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            @if($entries->count() > 0)
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <div class="border p-1">
                            <span class="small text-secondary text-uppercase" style="font-size: 7px;">Actual KM Factor</span>
                            <p class="h6 mb-0">@if($grandTotalKm > 0) {{ number_format($grandTotalActual / $grandTotalKm, 4) }} @else - @endif</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border p-1">
                            <span class="small text-secondary text-uppercase" style="font-size: 7px;">Actual HR Factor</span>
                            <p class="h6 mb-0">@if($grandTotalHours > 0) {{ number_format($grandTotalActual / $grandTotalHours, 4) }} @else - @endif</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-print-layout>