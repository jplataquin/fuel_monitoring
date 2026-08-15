<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 text-uppercase small fw-bold tracking-widest">
                        <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.index') }}" class="text-info text-decoration-none">Accounts</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="text-info text-decoration-none">{{ $subAccount->chargeableAccount->name }}</a></li>
                        <li class="breadcrumb-item active text-secondary" aria-current="page">{{ $subAccount->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 1000px;">
            <!-- Sub-Account Info -->
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-2">Sub-Account Name</h3>
                        <span class="h4 text-white fw-bold">{{ $subAccount->name }}</span>
                    </div>
               
                    <div class="col-md-4 text-md-end">
                        <h3 class="text-info small fw-bold text-uppercase tracking-widest mb-2">Total Approved Budget</h3>
                        <p class="display-6 fw-bold text-white mb-0 font-monospace">
                            {{ number_format($subAccount->budgets()->where('status', 'Approved')->sum('budget_quantity'), 2) }} <span class="h6 text-secondary uppercase">L</span>
                        </p>
                    </div>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                <!-- Allocate Budget Form Card -->
                <div class="card bg-dark border-secondary border-start border-4 border-info shadow-lg rounded-4 p-4 mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="h5 fw-bold text-white mb-0">Allocate New Budget</h4>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2 fw-bold text-uppercase small">Pending Approval</span>
                    </div>
                    <form action="{{ route('account-budgets.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="sub_account_id" value="{{ $subAccount->id }}">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="budget_quantity" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Budget Quantity (Liters)</label>
                                <input type="number" name="budget_quantity" id="budget_quantity" step="0.01" required placeholder="0.00" class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-info">
                                @error('budget_quantity')
                                    <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="remarks" class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Remarks</label>
                                <input type="text" name="remarks" id="remarks" placeholder="Optional notes..." class="form-control bg-dark border-secondary text-white rounded-3 p-3 focus-ring focus-ring-info">
                                @error('remarks')
                                    <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end pt-1 pb-3 border-bottom border-secondary border-opacity-25">
                            <button type="submit" class="btn btn-info px-4 py-2 rounded-pill fw-bold small text-uppercase tracking-wider shadow">
                                Submit for Approval
                            </button>
                        </div>
                    </form>


                     <!-- Budget History -->
                    <div class="mt-3 card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden">
                        <div class="card-header bg-secondary bg-opacity-10 border-secondary border-opacity-25 p-4">
                            <h3 class="h5 fw-bold text-white mb-0">Budget Allocation History</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0">
                                    <thead>
                                        <tr class="bg-secondary bg-opacity-5">
                                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Quantity</th>
                                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-center">Status</th>
                                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Remarks</th>
                                            <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subAccount->budgets()->orderBy('created_at', 'desc')->get() as $budget)
                                            <tr>
                                                <td class="px-4 py-3 align-middle">
                                                    <span class="text-white small">{{ $budget->created_at->format('M d, Y') }}</span>
                                                    <span class="d-block text-secondary smaller fw-bold text-uppercase">{{ $budget->created_at->format('h:i A') }}</span>
                                                </td>
                                                <td class="px-4 py-3 align-middle text-end font-monospace fw-bold text-info">
                                                    {{ number_format($budget->budget_quantity, 2) }} L
                                                </td>
                                                <td class="px-4 py-3 align-middle text-center">
                                                    <span class="badge rounded-pill fw-bold text-uppercase smaller 
                                                        {{ $budget->status === 'Approved' ? 'bg-success' : 
                                                        ($budget->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                        {{ $budget->status }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 align-middle small text-secondary">
                                                    {{ Str::limit($budget->remarks, 50) ?: '—' }}
                                                </td>
                                                <td class="px-4 py-3 align-middle text-end">
                                                    <a href="{{ route('account-budgets.show', $budget) }}" class="btn btn-link text-info p-2 rounded-circle" title="View Budget Details">
                                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-5 text-center text-secondary">
                                                    <svg class="mb-2" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <h3 class="h6 text-white">No budget history found</h3>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Utilization Entries History -->
            <div class="card bg-dark border-secondary shadow-lg rounded-4 overflow-hidden mt-5">
                <div class="card-header bg-secondary bg-opacity-10 border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h3 class="h5 fw-bold text-white mb-0">Utilization History</h3>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 fw-bold text-uppercase small">
                        {{ $subAccount->utilizationEntries()->count() }} Entries
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="bg-secondary bg-opacity-5">
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Date / Reference</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Order ID</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Asset / Equipment</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Operating Interval / Hours</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Odometer Interval / KM</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider text-end">Est. Fuel Consumed</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Driver / Operator</th>
                                    <th class="px-4 py-3 text-uppercase small fw-bold text-secondary tracking-wider">Particulars</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subAccount->utilizationEntries()->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get() as $entry)
                                    @php
                                        $calcType = strtolower($entry->calculation_type ?? '');
                                        $estFuel = 0;

                                        $hasHours = $entry->start_hour_reading !== null && $entry->end_hour_reading !== null;
                                        $hasKm = $entry->start_kilometer_reading !== null && $entry->end_kilometer_reading !== null;
                                        $hoursOperated = $hasHours ? ($entry->end_hour_reading - $entry->start_hour_reading) : 0;
                                        $kmOperated = $hasKm ? ($entry->end_kilometer_reading - $entry->start_kilometer_reading) : 0;

                                        $calcHours = 0;

                                        if (str_contains($calcType, 'kilometer')) {
                                            $estFuel = $entry->fuel_factor_km > 0 ? $kmOperated / $entry->fuel_factor_km : 0;
                                        } elseif (str_contains($calcType, 'timeframe')) {
                                            if ($entry->end_time && $entry->start_time) {
                                                $start = \Illuminate\Support\Carbon::parse($entry->start_time);
                                                $end = \Illuminate\Support\Carbon::parse($entry->end_time);
                                                $calcHours = max(0, $start->diffInMinutes($end) / 60);
                                                $estFuel = $calcHours * $entry->fuel_factor_hr;
                                            }
                                        } elseif (str_contains($calcType, 'actual')) {
                                            $estFuel = ($entry->actual_hours ?? 0) * $entry->fuel_factor_hr;
                                        } elseif (str_contains($calcType, 'hour')) {
                                            $estFuel = $hoursOperated * $entry->fuel_factor_hr;
                                        }
                                    @endphp
                                    <tr onclick="window.location='{{ route('utilization-entries.show', $entry) }}'" style="cursor: pointer;">
                                        <td class="px-4 py-3">
                                            <span class="text-white small fw-bold d-block">{{ $entry->date ? $entry->date->format('M d, Y') : 'N/A' }}</span>
                                            <span class="text-secondary smaller fw-bold font-monospace text-uppercase">{{ $entry->reference ?: '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($entry->fuel_order_id)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1 font-monospace fw-bold text-uppercase small">
                                                    #{{ $entry->fuel_order_id }}
                                                </span>
                                            @else
                                                <span class="text-secondary small">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-white font-monospace small fw-bold d-block">{{ $entry->asset->fleet_no ?? '—' }}</span>
                                            <span class="text-secondary smaller">{{ $entry->asset->assetType->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            @if($hasHours)
                                                <span class="text-white small d-block font-monospace">{{ number_format($entry->start_hour_reading, 1) }} - {{ number_format($entry->end_hour_reading, 1) }}</span>
                                                <span class="text-secondary smaller fw-bold font-monospace text-uppercase">Hours: {{ number_format($hoursOperated, 1) }}</span>
                                            @elseif(str_contains($calcType, 'timeframe'))
                                                <span class="text-white small d-block font-monospace">
                                                    {{ $entry->start_time ? \Illuminate\Support\Carbon::parse($entry->start_time)->format('H:i') : '00:00' }} - 
                                                    {{ $entry->end_time ? \Illuminate\Support\Carbon::parse($entry->end_time)->format('H:i') : '00:00' }}
                                                </span>
                                                <span class="text-secondary smaller fw-bold font-monospace text-uppercase">Interval: {{ number_format($calcHours, 1) }} hrs</span>
                                            @elseif(str_contains($calcType, 'actual'))
                                                <span class="text-white small d-block font-monospace">—</span>
                                                <span class="text-secondary smaller fw-bold font-monospace text-uppercase">Actual: {{ number_format($entry->actual_hours ?? 0, 1) }} hrs</span>
                                            @else
                                                <span class="text-secondary small">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            @if($hasKm)
                                                <span class="text-white small d-block font-monospace">{{ number_format($entry->start_kilometer_reading, 0) }} - {{ number_format($entry->end_kilometer_reading, 0) }}</span>
                                                <span class="text-secondary smaller fw-bold font-monospace text-uppercase">KM: {{ number_format($kmOperated, 0) }}</span>
                                            @else
                                                <span class="text-secondary small">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end align-middle font-monospace fw-bold text-success">
                                            {{ number_format($estFuel, 2) }} L
                                        </td>
                                        <td class="px-4 py-3 text-white small">
                                            {{ $entry->driver_operator_name ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-secondary small">
                                            {{ Str::limit($entry->particulars, 50) ?: '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-5 text-center text-secondary">
                                            <svg class="mb-2" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            <h3 class="h6 text-white">No utilization history found</h3>
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
</x-app-layout>
