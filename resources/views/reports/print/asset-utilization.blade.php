@php
    $title = __('Asset Data Report');
@endphp

<x-print-layout :title="$title">
    <div class="card border-0">
        <!-- Report Content -->
        <div class="card-body p-0 text-dark">
            <div class="mb-4">
                @if($assetId)
                    <span class="badge bg-light text-dark border me-2">Asset: {{ $assets->firstWhere('id', $assetId)?->fleet_no }}</span>
                @endif
                @if($dateFrom || $dateTo)
                    <span class="badge bg-light text-dark border">Period: {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}</span>
                @endif
            </div>
            
            @if($assetId && $selectedAsset = $assets->firstWhere('id', $assetId))
                <div class="p-4 border-bottom border-secondary mb-4">
                    <h3 class="small fw-bold text-primary text-uppercase tracking-widest mb-4 d-flex align-items-center">
                        Technical Specifications
                    </h3>
                    <div class="row g-4">
                        <div class="col-3">
                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Equipment Type</p>
                            <p class="fw-bold mb-0">{{ $selectedAsset->assetType->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-3">
                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Fleet / Plate No.</p>
                            <p class="fw-bold mb-0">{{ $selectedAsset->fleet_no }} <span class="small fw-normal text-secondary">({{ $selectedAsset->plate_no ?? 'N/A' }})</span></p>
                        </div>
                        <div class="col-3">
                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Fuel Type & Cap.</p>
                            <p class="fw-bold mb-0 text-success">{{ $selectedAsset->fuel_type ?? 'N/A' }} <span class="small fw-normal">({{ $selectedAsset->tank_capacity ? number_format($selectedAsset->tank_capacity, 2) . ' L' : 'N/A' }})</span></p>
                        </div>
                        <div class="col-3">
                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Factor (KM/HR)</p>
                            <p class="fw-bold mb-0 text-info font-monospace">{{ number_format($selectedAsset->fuel_factor_km, 2) }} / {{ number_format($selectedAsset->fuel_factor_hr, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="table-responsive d-print-overflow-visible">
                <table class="table table-striped mb-0 d-print-table d-print-text-dark border-secondary">
                    <thead class="table-light">
                        <tr class="text-uppercase small fw-bold tracking-widest">
                            <th class="px-4 py-3 border-secondary d-print-p-1">Date</th>
                            <th class="px-4 py-3 border-secondary d-print-p-1">Particulars</th>
                            <th class="px-4 py-3 border-secondary d-print-p-1">Account / Sub Account</th>
                            <th class="px-4 py-3 border-secondary d-print-p-1">Calc. Type</th>
                            <th class="px-4 py-3 border-secondary text-end d-print-p-1">Calc. KM</th>
                            <th class="px-4 py-3 border-secondary text-end d-print-p-1">Calc. HR</th>
                            <th class="px-4 py-3 border-secondary text-end d-print-p-1">Computed Qty</th>
                        </tr>
                    </thead>
                    <tbody class="border-secondary">
                        @php
                            $grandTotalKm = 0;
                            $grandTotalHours = 0;
                            $grandTotalActual = 0;
                        @endphp
                        @forelse($entries as $fuelOrderId => $group)
                            @php
                                $fuelOrder = $group->first()->fuelOrder;
                                $groupTotalKm = 0;
                                $groupTotalHours = 0;
                                $groupTotalQty = 0;
                                $grandTotalActual += $fuelOrder->actual_quantity;
                            @endphp
                            <!-- Group Header -->
                            <tr class="table-active">
                                <td colspan="7" class="px-4 py-3 border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="fw-bold text-primary font-monospace">FUEL ORDER #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            <span class="small fw-bold text-secondary text-uppercase tracking-widest">Released: {{ $fuelOrder->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="text-end">
                                                <span class="small fw-bold text-secondary text-uppercase tracking-widest d-block" style="font-size: 0.6rem;">Approved (Say)</span>
                                                <span class="fw-bold text-dark font-monospace">{{ number_format($fuelOrder->say_quantity, 2) }} L</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="small fw-bold text-success text-uppercase tracking-widest d-block" style="font-size: 0.6rem;">Actual Dispensed</span>
                                                <span class="fw-bold text-success font-monospace">{{ number_format($fuelOrder->actual_quantity, 2) }} L</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            @foreach($group as $entry)
                                @php
                                    $qty = 0;
                                    $calcKm = 0;
                                    $calcHours = 0;
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
                                    
                                    $groupTotalKm += $calcKm;
                                    $groupTotalHours += $calcHours;
                                    $groupTotalQty += $qty;
                                    $grandTotalKm += $calcKm;
                                    $grandTotalHours += $calcHours;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 small border-secondary">{{ $entry->date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 small border-secondary">{{ $entry->particulars ?? '—' }}</td>
                                    <td class="px-4 py-3 small border-secondary fw-bold text-primary">{{ $entry->chargeableAccount->name ?? 'Unassigned' }} - {{ $entry->subAccount->name ?? '—' }}</td>
                                    <td class="px-4 py-3 border-secondary">
                                        <span class="badge border border-secondary rounded-pill text-uppercase tracking-widest" style="font-size: 0.6rem;">{{ $entry->calculation_type ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-end font-monospace border-secondary">{{ $calcKm > 0 ? number_format($calcKm, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-end font-monospace border-secondary">{{ $calcHours > 0 ? number_format($calcHours, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-end font-monospace fw-bold text-secondary border-secondary">{{ number_format($qty, 2) }} L</td>
                                </tr>
                            @endforeach
                            <!-- Group Footer -->
                            <tr class="bg-light border-secondary">
                                <td colspan="4" class="px-4 py-2 text-end small fw-bold text-secondary text-uppercase tracking-widest border-secondary">
                                    Sub-Total (Order #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}):
                                </td>
                                <td class="px-4 py-2 text-end font-monospace fw-bold text-info border-secondary">
                                    {{ $groupTotalKm > 0 ? number_format($groupTotalKm, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-end font-monospace fw-bold text-info border-secondary">
                                    {{ $groupTotalHours > 0 ? number_format($groupTotalHours, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-end small fw-bold text-success border-secondary">
                                    {{ number_format($groupTotalQty, 2) }} L
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-5 text-center border-secondary">
                                    No records found for the selected parameters.
                                </td>
                            </tr>
                        @endforelse
                        
                        @if($entries->count() > 0)
                            <!-- Grand Total Row -->
                            <tr class="table-primary border-top border-secondary">
                                <td colspan="4" class="px-4 py-4 text-end h6 fw-bold text-uppercase tracking-widest mb-0 border-secondary">
                                    Grand Total (Actual Dispensed):
                                </td>
                                <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary text-info">
                                    {{ $grandTotalKm > 0 ? number_format($grandTotalKm, 2) : '-' }}
                                </td>
                                <td class="px-4 py-4 text-end font-monospace fw-bold border-secondary text-info">
                                    {{ $grandTotalHours > 0 ? number_format($grandTotalHours, 2) : '-' }}
                                </td>
                                <td class="px-4 py-4 text-end font-monospace h5 fw-bold text-success border-secondary mb-0">
                                    {{ number_format($grandTotalActual, 2) }} L
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            @if($entries->count() > 0)
                <!-- Final Summary -->
                <div class="p-4 border-top border-secondary mt-4">
                    <h3 class="small fw-bold text-success text-uppercase tracking-widest mb-4 d-flex align-items-center">
                        Performance Metrics (Actualized)
                    </h3>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="card border-secondary p-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Actual KM Factor</p>
                                <p class="small text-secondary opacity-75 mb-3">(Total Dispensed / Total Calc. KM)</p>
                                <p class="h2 font-monospace fw-bold text-success mb-0">
                                    @if($grandTotalKm > 0)
                                        {{ number_format($grandTotalActual / $grandTotalKm, 4) }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-secondary p-4">
                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Actual Hour Factor</p>
                                <p class="small text-secondary opacity-75 mb-3">(Total Dispensed / Total Calc. Hours)</p>
                                <p class="h2 font-monospace fw-bold text-success mb-0">
                                    @if($grandTotalHours > 0)
                                        {{ number_format($grandTotalActual / $grandTotalHours, 4) }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-print-layout>