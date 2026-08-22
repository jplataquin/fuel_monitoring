<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Utilization Entries') }}
            </h2>
            <div class="d-flex align-items-center gap-3">
                <form action="{{ route('utilization-entries.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select name="chargeable_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Accounts</option>
                        @foreach($chargeableAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('chargeable_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="sub_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Sub-Accounts</option>
                        @foreach($subAccounts as $sub)
                            @if(!request('chargeable_account_id') || $sub->chargeable_account_id == request('chargeable_account_id'))
                                <option value="{{ $sub->id }}" {{ request('sub_account_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->display_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <select name="asset_id" class="form-select bg-dark text-light border-secondary border-opacity-50 rounded-pill px-3 py-2 text-sm" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Assets</option>
                        @foreach($assets as $ast)
                            <option value="{{ $ast->id }}" {{ request('asset_id') == $ast->id ? 'selected' : '' }}>
                                {{ $ast->fleet_no }}
                            </option>
                        @endforeach
                    </select>

                    @if(request('chargeable_account_id') || request('sub_account_id') || request('asset_id'))
                        <a href="{{ route('utilization-entries.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 text-sm text-decoration-none">
                            Clear
                        </a>
                    @endif

                    <a href="{{ route('utilization-entries.print', request()->query()) }}" target="_blank" class="btn btn-outline-info rounded-pill px-3 py-2 text-sm text-decoration-none d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Print List
                    </a>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <!-- Grand Total Card -->
        <div class="row mb-4">
            <div class="col-12 col-md-4">
                <div class="card bg-dark border-secondary border-opacity-50 rounded-4 p-4 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="h6 fw-bold text-secondary text-uppercase tracking-wider mb-2" style="font-size: 0.75rem;">Grand Total Calculated Fuel</h3>
                            <div class="h2 font-monospace fw-bold text-info mb-0" style="color: #38bdf8 !important;">
                                {{ number_format($totalCalculatedFuel, 2) }} L
                            </div>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-4" style="color: #38bdf8 !important;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm overflow-hidden">
            <div class="card-body p-4">
                @if (session('status'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-20 text-success d-flex align-items-center mb-4 rounded-3" role="alert">
                        <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="fw-bold small">{{ session('status') }}</div>
                    </div>
                @endif

                <div class="table-responsive rounded-3 border border-secondary border-opacity-25">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr class="bg-secondary bg-opacity-10">
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Date</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Asset</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Driver/Operator</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Calculation Type</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end">Readings / Hours</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end">Calculated Fuel</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($utilizationEntries as $entry)
                                <tr onclick="window.location='{{ route('utilization-entries.show', $entry) }}'" style="cursor: pointer;">
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-light small">{{ $entry->date->format('M d, Y') }}</div>
                                        <div class="text-secondary small" style="font-size: 11px;">
                                            @if($entry->start_time && $entry->end_time)
                                                {{ $entry->start_time->format('H:i') }} - {{ $entry->end_time->format('H:i') }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($entry->asset)
                                            <div class="fw-bold text-light small tracking-tight">{{ $entry->asset->fleet_no }}</div>
                                            <div class="text-secondary small text-uppercase tracking-widest" style="font-size: 10px;">{{ $entry->asset->plate_no ?? 'No Plate' }}</div>
                                        @else
                                            <span class="text-secondary small">Direct</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-light small fw-medium">
                                        {{ $entry->driver_operator_name }}
                                    </td>
                                    <td class="px-4 py-3 text-secondary small">
                                        {{ $entry->calculation_type }}
                                    </td>
                                    <td class="px-4 py-3 text-end font-monospace small text-light">
                                        @if($entry->calculation_type === 'Kilometer Reading')
                                            {{ number_format($entry->start_kilometer_reading, 1) }} - {{ number_format($entry->end_kilometer_reading, 1) }} km
                                        @elseif($entry->calculation_type === 'Hour Reading')
                                            {{ number_format($entry->start_hour_reading, 1) }} - {{ number_format($entry->end_hour_reading, 1) }} hrs
                                        @elseif($entry->calculation_type === 'Actual Hours')
                                            {{ number_format($entry->actual_hours, 1) }} hrs (Actual)
                                        @else
                                            Timeframe
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end font-monospace small fw-bold text-info">
                                        {{ number_format($entry->calculated_quantity, 2) }} L
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-1" onclick="event.stopPropagation()">
                                            <a href="{{ route('utilization-entries.show', $entry) }}" class="btn btn-link text-primary p-2 rounded-circle hover-bg-light hover-bg-opacity-10" title="View Entry">
                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-5 text-center border-0">
                                        <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                            <div class="bg-secondary bg-opacity-20 rounded-4 d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                                <svg width="32" height="32" class="text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="fw-bold text-light mb-1">No utilization entries found.</p>
                                            @if(request('chargeable_account_id') || request('sub_account_id') || request('asset_id'))
                                                <p class="text-secondary small mb-3">Try adjusting your search filter.</p>
                                                <a href="{{ route('utilization-entries.index') }}" class="btn btn-link text-primary fw-bold text-decoration-none small text-uppercase tracking-widest">Clear Filter</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($utilizationEntries->hasPages())
                    <div class="mt-4">
                        {{ $utilizationEntries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
