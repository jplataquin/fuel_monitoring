<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 fw-bold text-light mb-0">
                    {{ __('Fuel Order #') }}{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}
                </h2>
                <a href="{{ route('fuel-orders.index') }}" class="btn btn-secondary rounded-pill px-4 d-inline-flex align-items-center fw-bold small text-uppercase tracking-widest shadow-sm d-print-none">
                    <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Orders
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 d-print-none flex-wrap">
                @if(Auth::user()->role === 'administrator' && $fuelOrder->status !== 'VOID')
                    <form action="{{ route('fuel-orders.void', $fuelOrder) }}" method="POST" onsubmit="return confirm('Are you sure you want to void this fuel order? This will release all associated utilization entries and mark this order as VOID.')" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow-sm">
                            <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Void Order
                        </button>
                    </form>
                @endif
                @if(Auth::user()->role === 'administrator')
                    <a href="{{ route('fuel-orders.edit', $fuelOrder) }}" class="btn btn-primary rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow-sm">
                        <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Order
                    </a>
                @endif
                @if(in_array(Auth::user()->role, ['fuel_man', 'administrator', 'data_logger', 'data logger']) && $fuelOrder->status === 'PEND')
                    <a href="{{ route('fuel-orders.actualize', $fuelOrder) }}" class="btn btn-info rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow-sm">
                        <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Actualize
                    </a>
                @endif
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold small text-uppercase tracking-widest shadow-sm" style="background-color: #6366f1; border-color: #6366f1;">
                    <svg width="16" height="16" class="me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .card { background-color: white !important; color: black !important; border: none !important; box-shadow: none !important; }
            .bg-dark { background-color: white !important; color: black !important; }
            .text-light, .text-secondary, .text-primary, .text-indigo-600, .text-success, .text-warning, .text-danger { color: black !important; }
            .bg-secondary, .bg-primary, .bg-success, .bg-warning, .bg-danger { background-color: transparent !important; }
            .border-secondary { border-color: #dee2e6 !important; }
            .table-dark { --bs-table-bg: white !important; --bs-table-color: black !important; border-color: #dee2e6 !important; }
            .table-primary, .table-secondary, .table-success, .table-info, .table-warning, .table-danger { --bs-table-bg: #f8f9fa !important; --bs-table-color: black !important; }
            .badge { border: 1px solid black !important; color: black !important; background: transparent !important; }
            .font-monospace { font-family: monospace !important; }
            .print-bg-black { background-color: black !important; color: white !important; -webkit-print-color-adjust: exact; }
            .container-xl { max-width: 100% !important; padding: 0 !important; }
            .rounded-4, .rounded-3, .rounded-pill { border-radius: 0 !important; }
            .bg-opacity-10, .bg-opacity-20 { background-color: transparent !important; }
        }
    </style>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8 w-100-print">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Print Header -->
                        <div class="text-center mb-5 pb-5 border-bottom border-secondary border-opacity-25">
                            <h1 class="h3 fw-black text-light tracking-tight text-uppercase mb-2">Fuel Order Form</h1>
                            <p class="text-secondary fw-medium mb-1">Issue Date: {{ $fuelOrder->created_at->format('F d, Y') }}</p>
                            <p class="text-secondary fw-medium mb-0">Order Number: #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-6">
                                <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Asset Details</h4>
                                <p class="h5 fw-bold text-light mb-0">{{ $fuelOrder->asset->fleet_no }}</p>
                                <p class="small text-secondary mb-0">{{ $fuelOrder->asset->assetType->name ?? 'N/A' }} | {{ $fuelOrder->asset->plate_no ?? 'No Plate' }}</p>
                            </div>
                            <div class="col-6">
                                <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Date Range</h4>
                                <p class="h5 fw-bold text-light mb-0">
                                    {{ \Carbon\Carbon::parse($fuelOrder->date_from)->format('M d, Y') }} 
                                    - 
                                    {{ \Carbon\Carbon::parse($fuelOrder->date_to)->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-12 d-flex flex-wrap gap-4 align-items-center">
                                <div>
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">Status</h4>
                                    @php
                                        $statusClass = match($fuelOrder->status) {
                                            'DONE' => 'bg-success text-success bg-opacity-10 border border-success border-opacity-20',
                                            'VOID' => 'bg-danger text-danger bg-opacity-10 border border-danger border-opacity-20',
                                            default => 'bg-warning text-warning bg-opacity-10 border border-warning border-opacity-20',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill px-4 py-2 fw-bold text-uppercase tracking-widest" style="font-size: 11px;">
                                        {{ $fuelOrder->status }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">KM Factor</h4>
                                    <p class="h5 fw-bold text-primary mb-0">{{ number_format($fuelOrder->fuel_factor_km, 2) }} KM/L</p>
                                </div>
                                <div>
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-1">HR Factor</h4>
                                    <p class="h5 fw-bold text-primary mb-0">{{ number_format($fuelOrder->fuel_factor_hr, 2) }} L/HR</p>
                                </div>
                            </div>
                        </div>

                        @php
                            $groupedTotals = [];
                            foreach ($fuelOrder->utilizationEntries as $entry) {
                                $accountName = $entry->chargeableAccount->name ?? 'Unassigned';
                                if ($entry->subAccount) {
                                    $accountName .= ' - ' . $entry->subAccount->name;
                                }
                                if (!isset($groupedTotals[$accountName])) {
                                    $groupedTotals[$accountName] = ['km' => 0, 'hr' => 0, 'qty' => 0];
                                }
                                
                                $calcType = strtolower($entry->calculation_type ?? '');
                                if (str_contains($calcType, 'kilometer')) {
                                    $diff = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                                    $groupedTotals[$accountName]['km'] += $diff;
                                    $groupedTotals[$accountName]['qty'] += $fuelOrder->fuel_factor_km > 0 ? $diff / $fuelOrder->fuel_factor_km : 0;
                                } elseif (str_contains($calcType, 'actual')) {
                                    if ($entry->end_time && $entry->start_time) {
                                        $start = \Illuminate\Support\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                                        $end = \Illuminate\Support\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                                        $diffInHours = max(0, $start->diffInMinutes($end) / 60);
                                        $groupedTotals[$accountName]['hr'] += $diffInHours;
                                        $groupedTotals[$accountName]['qty'] += $diffInHours * $fuelOrder->fuel_factor_hr;
                                    }
                                } elseif (str_contains($calcType, 'hour')) {
                                    $diff = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                                    $groupedTotals[$accountName]['hr'] += $diff;
                                    $groupedTotals[$accountName]['qty'] += $diff * $fuelOrder->fuel_factor_hr;
                                }
                            }
                        @endphp

                        @if(count($groupedTotals) > 0)
                            <div class="mb-5">
                                <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-3">Breakdown by Charged To</h4>
                                <div class="table-responsive rounded-3 border border-secondary border-opacity-25 shadow-sm">
                                    <table class="table table-dark table-hover mb-0 align-middle">
                                        <thead>
                                            <tr class="bg-secondary bg-opacity-10">
                                                <th class="ps-4 py-3 small fw-bold text-secondary text-uppercase">Account</th>
                                                <th class="px-4 py-3 small fw-bold text-secondary text-uppercase text-end">Total KM</th>
                                                <th class="px-4 py-3 small fw-bold text-secondary text-uppercase text-end">Total Hours</th>
                                                <th class="pe-4 py-3 small fw-bold text-secondary text-uppercase text-end">Fuel (L)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupedTotals as $account => $totals)
                                                <tr>
                                                    <td class="ps-4 py-3 small fw-bold text-light">{{ $account }}</td>
                                                    <td class="px-4 py-3 small text-secondary text-end font-monospace">{{ number_format($totals['km'], 2) }}</td>
                                                    <td class="px-4 py-3 small text-secondary text-end font-monospace">{{ number_format($totals['hr'], 2) }}</td>
                                                    <td class="pe-4 py-3 small text-primary fw-bold text-end font-monospace">{{ number_format($totals['qty'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-5">
                            <div class="col-md-4">
                                <div class="bg-secondary bg-opacity-10 rounded-3 p-4 border border-secondary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total KM</h4>
                                    <p class="h3 fw-black text-light mb-0 font-monospace">{{ number_format($fuelOrder->calculated_kilometers, 2) }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-secondary bg-opacity-10 rounded-3 p-4 border border-secondary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total Hours</h4>
                                    <p class="h3 fw-black text-light mb-0 font-monospace">{{ number_format($fuelOrder->calculated_hours, 2) }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-4 border border-primary border-opacity-10 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
                                    <h4 class="small fw-bold text-primary text-uppercase tracking-wider mb-2">Calculated (L)</h4>
                                    <p class="h3 fw-black text-primary mb-0 font-monospace">{{ number_format($fuelOrder->calculated_quantity, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-dark bg-opacity-50 rounded-4 p-4 p-md-5 mb-5 shadow-inner border border-secondary border-opacity-25 print-border-light">
                            <div class="d-flex justify-content-between align-items-center {{ $fuelOrder->status === 'DONE' ? 'mb-4 pb-4 border-bottom border-secondary border-opacity-25' : '' }}">
                                <span class="h5 fw-bold text-secondary mb-0">Say Fuel Quantity:</span>
                                <span class="h1 fw-black text-primary mb-0 font-monospace">{{ number_format($fuelOrder->say_quantity, 2) }} L</span>
                            </div>
                            @if($fuelOrder->status === 'DONE')
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 fw-bold text-secondary mb-0">Actual Quantity:</span>
                                    <span class="h1 fw-black text-success mb-0 font-monospace">{{ number_format($fuelOrder->actual_quantity, 2) }} L</span>
                                </div>
                            @endif
                        </div>

                        @if($fuelOrder->status === 'PEND')
                        <div class="row g-5 pt-5 border-top border-secondary border-opacity-25 mt-5">
                            <div class="col-6 text-center">
                                <div class="border-bottom border-secondary pb-2 mb-2 px-3 d-flex align-items-end justify-content-center" style="min-height: 3rem;">
                                    <span class="fw-bold text-light">{{ $fuelOrder->creator->name ?? 'Unknown' }}</span>
                                </div>
                                <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-0">Prepared By (Data Logger)</p>
                            </div>
                             <div class="col-6 text-center">
                                <div class="border-bottom border-secondary pb-2 mb-2 px-3 d-flex align-items-end justify-content-center" style="min-height: 3rem;">
                                    <span class="text-secondary font-monospace">________________ L</span>
                                </div>
                                <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-0">Actual Quantity</p>
                            </div>
                        </div>

                        <div class="row g-5 pt-5 mt-2">
                            <div class="col-6 text-center">
                                <div class="border-bottom border-secondary pb-2 mb-2 px-3 d-flex align-items-end justify-content-center" style="min-height: 3rem;"></div>
                                <p class="small fw-bold text-secondary text-uppercase tracking-wider mb-0">Fuel Man (Signature)</p>
                            </div>
                        </div>
                        @endif

                        <!-- Audit Logs Section -->
                        <div class="mt-5 pt-5 border-top border-secondary border-opacity-25 d-print-none">
                            <h4 class="small fw-bold text-secondary text-uppercase tracking-widest mb-4">Audit Logs</h4>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="bg-secondary bg-opacity-10 rounded-3 p-3 border border-secondary border-opacity-10 h-100 shadow-sm">
                                        <p class="fw-bold text-secondary text-uppercase tracking-wider mb-2" style="font-size: 10px;">Created By</p>
                                        <p class="small fw-bold text-light mb-1">{{ $fuelOrder->creator->name ?? 'System' }}</p>
                                        <p class="text-secondary font-monospace mb-0" style="font-size: 10px;">{{ $fuelOrder->created_at->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>

                                @if($fuelOrder->updater && ($fuelOrder->updated_at != $fuelOrder->actualized_at) && ($fuelOrder->void_at != $fuelOrder->updated_at) )
                                    <div class="col-md-6 col-lg-3">
                                        <div class="bg-secondary bg-opacity-10 rounded-3 p-3 border border-secondary border-opacity-10 h-100 shadow-sm">
                                            <p class="fw-bold text-secondary text-uppercase tracking-wider mb-2" style="font-size: 10px;">Last Updated</p>
                                            <p class="small fw-bold text-light mb-1">{{ $fuelOrder->updater->name }}</p>
                                            <p class="text-secondary font-monospace mb-0" style="font-size: 10px;">{{ $fuelOrder->updated_at->format('M d, Y H:i:s') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($fuelOrder->actualizer)
                                    <div class="col-md-6 col-lg-3">
                                        <div class="bg-success bg-opacity-10 rounded-3 p-3 border border-success border-opacity-10 h-100 shadow-sm">
                                            <p class="fw-bold text-success text-uppercase tracking-wider mb-2" style="font-size: 10px;">Actualized By</p>
                                            <p class="small fw-bold text-success mb-1">{{ $fuelOrder->actualizer->name }}</p>
                                            <p class="text-success font-monospace mb-0" style="font-size: 10px; opacity: 0.7;">{{ $fuelOrder->actualized_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($fuelOrder->status === 'VOID' && $fuelOrder->voider)
                                    <div class="col-md-6 col-lg-3">
                                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 border border-danger border-opacity-10 h-100 shadow-sm">
                                            <p class="fw-bold text-danger text-uppercase tracking-wider mb-2" style="font-size: 10px;">Voided By</p>
                                            <p class="small fw-bold text-danger mb-1">{{ $fuelOrder->voider->name }}</p>
                                            <p class="text-danger font-monospace mb-0" style="font-size: 10px; opacity: 0.7;">{{ $fuelOrder->void_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
