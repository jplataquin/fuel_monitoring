@php
    $layout = $isPrint ? 'print-layout' : 'app-layout';
    $title = __('Asset Data Report');
@endphp

<x-dynamic-component :component="$layout" :title="$title">
    @if(!$isPrint)
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
    @endif

    <div class="{{ $isPrint ? '' : 'py-5' }}">
        <div class="container-xl" style="max-width: 1280px;">
            <div class="card {{ $isPrint ? 'border-0' : 'bg-dark border-secondary shadow-lg rounded-4 overflow-hidden' }}">
                
                @if(!$isPrint)
                    <!-- Report Filter Form -->
                    <div class="card-header bg-dark border-secondary p-4 d-print-none">
                        <form action="{{ route('reports.asset-utilization') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="asset_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Asset</label>
                                <select name="asset_id" id="asset_id" class="form-select bg-dark text-light border-secondary" required>
                                    <option value="">Select Asset...</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ $assetId == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->fleet_no }} ({{ $asset->assetType->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
                                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control bg-dark text-light border-secondary">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
                                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control bg-dark text-light border-secondary">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small shadow-sm py-2">
                                    Generate
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Report Content -->
                <div class="card-body p-0 {{ $isPrint ? 'text-dark' : 'text-light' }}">
                    @if($isPrint)
                        <div class="mb-4">
                            @if($assetId)
                                <span class="badge bg-light text-dark border me-2">Asset: {{ $assets->firstWhere('id', $assetId)?->fleet_no }}</span>
                            @endif
                            @if($dateFrom || $dateTo)
                                <span class="badge bg-light text-dark border">Period: {{ $dateFrom ?? 'Start' }} to {{ $dateTo ?? 'End' }}</span>
                            @endif
                        </div>
                    @endif
                    
                    @if($assetId && $selectedAsset = $assets->firstWhere('id', $assetId))
                        <div class="p-4 border-bottom border-secondary bg-dark">
                            <h3 class="small fw-bold text-primary text-uppercase tracking-widest mb-4 d-flex align-items-center">
                                <span class="bg-primary opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                Technical Specifications
                            </h3>
                            <div class="row g-4">
                                <div class="col-sm-6 col-md-4 col-lg">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded bg-primary bg-opacity-10 d-print-bg-light">
                                            <svg width="16" height="16" class="text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        </div>
                                        <div>
                                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Equipment Type</p>
                                            <p class="fw-bold mb-0">{{ $selectedAsset->assetType->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 col-lg">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded bg-primary bg-opacity-10 d-print-bg-light">
                                            <svg width="16" height="16" class="text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                                        </div>
                                        <div>
                                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Fleet / Plate No.</p>
                                            <p class="fw-bold mb-0">{{ $selectedAsset->fleet_no }} <span class="small fw-normal text-secondary">({{ $selectedAsset->plate_no ?? 'N/A' }})</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 col-lg">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded bg-success bg-opacity-10 d-print-bg-light">
                                            <svg width="16" height="16" class="text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                        </div>
                                        <div>
                                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Fuel Type & Cap.</p>
                                            <p class="fw-bold mb-0 text-success">{{ $selectedAsset->fuel_type ?? 'N/A' }} <span class="small fw-normal">({{ $selectedAsset->tank_capacity ? number_format($selectedAsset->tank_capacity, 2) . ' L' : 'N/A' }})</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 col-lg">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded bg-info bg-opacity-10 d-print-bg-light">
                                            <svg width="16" height="16" class="text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </div>
                                        <div>
                                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Factor (KM)</p>
                                            <p class="fw-bold mb-0 text-info font-monospace">{{ number_format($selectedAsset->fuel_factor_km, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4 col-lg">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="p-2 rounded bg-info bg-opacity-10 d-print-bg-light">
                                            <svg width="16" height="16" class="text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1" style="font-size: 0.65rem;">Factor (HR)</p>
                                            <p class="fw-bold mb-0 text-info font-monospace">{{ number_format($selectedAsset->fuel_factor_hr, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="table-responsive d-print-overflow-visible">
                        <table class="table table-dark table-hover table-striped mb-0 d-print-table d-print-text-dark border-secondary">
                            <thead class="table-secondary">
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
                                                        <span class="fw-bold text-light font-monospace">{{ number_format($fuelOrder->say_quantity, 2) }} L</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="small fw-bold text-success opacity-75 text-uppercase tracking-widest d-block" style="font-size: 0.6rem;">Actual Dispensed</span>
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
                                            <td class="px-4 py-3 small border-secondary text-truncate" style="max-width: 200px;" title="{{ $entry->particulars }}">{{ $entry->particulars ?? '—' }}</td>
                                            <td class="px-4 py-3 small border-secondary fw-bold text-primary">{{ $entry->chargeableAccount->name ?? 'Unassigned' }} - {{ $entry->subAccount->name ?? '—' }}</td>
                                            <td class="px-4 py-3 border-secondary">
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill text-uppercase tracking-widest" style="font-size: 0.6rem;">{{ $entry->calculation_type ?? 'N/A' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-end font-monospace border-secondary">{{ $calcKm > 0 ? number_format($calcKm, 2) : '-' }}</td>
                                            <td class="px-4 py-3 text-end font-monospace border-secondary">{{ $calcHours > 0 ? number_format($calcHours, 2) : '-' }}</td>
                                            <td class="px-4 py-3 text-end font-monospace fw-bold text-secondary border-secondary">{{ number_format($qty, 2) }} L</td>
                                        </tr>
                                    @endforeach
                                    <!-- Group Footer -->
                                    <tr class="bg-dark bg-opacity-50 border-secondary">
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
                                            <div class="py-5 d-print-none">
                                                <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-4 mb-3">
                                                    <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="fw-bold mb-1">No report data to display.</p>
                                                <p class="small text-secondary mb-0">Please select an asset and date range to generate the report.</p>
                                            </div>
                                            <div class="d-none d-print-block text-dark">
                                                No records found for the selected parameters.
                                            </div>
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
                        <!-- Card Footer / Final Summary -->
                        <div class="p-4 border-top border-secondary bg-dark d-print-bg-white rounded-bottom-4">
                            <h3 class="small fw-bold text-success d-print-text-success text-uppercase tracking-widest mb-4 d-flex align-items-center">
                                <span class="bg-success opacity-25 me-3" style="width: 32px; height: 1px;"></span>
                                Performance Metrics (Actualized)
                            </h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card bg-dark border-secondary p-4 d-print-bg-light">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Actual KM Factor</p>
                                                <p class="small text-secondary opacity-75 mb-3">(Total Dispensed / Total Calc. KM)</p>
                                                <p class="h2 font-monospace fw-bold text-success mb-0">
                                                    @if($grandTotalKm > 0)
                                                        {{ number_format($grandTotalActual / $grandTotalKm, 4) }}
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                                @if($grandTotalKm > 0 && $selectedAsset->fuel_factor_km > 0)
                                                    @php
                                                        $targetKmFactor = $selectedAsset->fuel_factor_km > 0 ? 1 / $selectedAsset->fuel_factor_km : 0;
                                                        $actualKmFactor = $grandTotalKm > 0 ? $grandTotalActual / $grandTotalKm : 0;
                                                        $kmVariance = $targetKmFactor > 0 ? (($actualKmFactor - $targetKmFactor) / $targetKmFactor) * 100 : 0;
                                                    @endphp
                                                    <div class="mt-2 d-flex align-items-center {{ $kmVariance > 0 ? 'text-danger' : 'text-info' }}">
                                                        @if($kmVariance > 0)
                                                            <svg width="12" height="12" class="me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                                        @else
                                                            <svg width="12" height="12" class="me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
                                                        @endif
                                                        <span class="small fw-bold">{{ $kmVariance > 0 ? '+' : '' }}{{ number_format($kmVariance, 2) }}%</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="p-3 bg-success bg-opacity-10 rounded-3 align-self-start">
                                                <svg width="24" height="24" class="text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-dark border-secondary p-4 d-print-bg-light">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="small fw-bold text-secondary text-uppercase tracking-widest mb-1">Actual Hour Factor</p>
                                                <p class="small text-secondary opacity-75 mb-3">(Total Dispensed / Total Calc. Hours)</p>
                                                <p class="h2 font-monospace fw-bold text-success mb-0">
                                                    @if($grandTotalHours > 0)
                                                        {{ number_format($grandTotalActual / $grandTotalHours, 4) }}
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                                @if($grandTotalHours > 0 && $selectedAsset->fuel_factor_hr > 0)
                                                    @php
                                                        $actualHrFactor = $grandTotalActual / $grandTotalHours;
                                                        $hrVariance = (($actualHrFactor - $selectedAsset->fuel_factor_hr) / $selectedAsset->fuel_factor_hr) * 100;
                                                    @endphp
                                                    <div class="mt-2 d-flex align-items-center {{ $hrVariance > 0 ? 'text-danger' : 'text-info' }}">
                                                        @if($hrVariance > 0)
                                                            <svg width="12" height="12" class="me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                                        @else
                                                            <svg width="12" height="12" class="me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
                                                        @endif
                                                        <span class="small fw-bold">{{ $hrVariance > 0 ? '+' : '' }}{{ number_format($hrVariance, 2) }}%</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="p-3 bg-success bg-opacity-10 rounded-3 align-self-start">
                                                <svg width="24" height="24" class="text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
