<div x-data="{ 
    showEntriesModal: false,
    showWaiverModal: false,
    hasNegative: @entangle('has_negative_balance'),
    modal: null,
    waiverModal: null,
    init() {
        this.modal = new bootstrap.Modal(document.getElementById('entriesModal'));
        this.waiverModal = new bootstrap.Modal(document.getElementById('waiverModal'));
        this.$watch('showEntriesModal', value => {
            if (value) {
                this.modal.show();
            } else {
                this.modal.hide();
            }
        });
        this.$watch('showWaiverModal', value => {
            if (value) {
                this.waiverModal.show();
            } else {
                this.waiverModal.hide();
            }
        });
        document.getElementById('entriesModal').addEventListener('hidden.bs.modal', () => {
            this.showEntriesModal = false;
        });
        document.getElementById('waiverModal').addEventListener('hidden.bs.modal', () => {
            this.showWaiverModal = false;
        });
    },
    handleSubmit() {
        if (this.hasNegative) {
            this.showWaiverModal = true;
        } else {
            $wire.submit();
        }
    }
}">
    @if(!$creation_method)
        <!-- Step 1: Choose Creation Method -->
        <div class="py-4">
            <h3 class="h5 fw-bold text-light mb-4 text-center text-uppercase tracking-wider">Choose Fuel Order Creation Method</h3>
            <div class="row g-4 justify-content-center">
                <!-- Option 1: Create for Asset -->
                <div class="col-md-5">
                    <div wire:click="setCreationMethod('with_asset')" class="card bg-dark border-secondary border-opacity-50 h-100 p-4 transition duration-150 rounded-2 text-center" style="cursor: pointer; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.borderColor='#0d6efd'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='rgba(108,117,125,0.5)'; this.style.transform='none';">
                        <div class="d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3 text-primary d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" /></svg>
                            </div>
                            <h4 class="h5 fw-black text-light text-uppercase tracking-wide mb-2">Create for Asset</h4>
                            <p class="text-secondary small mb-0">Select an asset and a date range to automatically compute estimated fuel consumption based on logged utilization entries.</p>
                        </div>
                    </div>
                </div>

                <!-- Option 2: Create Direct -->
                <div class="col-md-5">
                    <div wire:click="setCreationMethod('direct')" class="card bg-dark border-secondary border-opacity-50 h-100 p-4 transition duration-150 rounded-2 text-center" style="cursor: pointer; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.borderColor='#0dcaf0'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='rgba(108,117,125,0.5)'; this.style.transform='none';">
                        <div class="d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 mb-3 text-info d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h4 class="h5 fw-black text-light text-uppercase tracking-wide mb-2">Create Direct (No Asset)</h4>
                            <p class="text-secondary small mb-0">Manually issue a fuel order for a specific chargeable account and sub-account budget without referencing an asset.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Step 2: Form Header -->
        <div class="col-12 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-3 mb-4">
            <div>
                <span class="badge bg-secondary bg-opacity-25 text-secondary text-uppercase tracking-wider px-3 py-2 fw-bold" style="font-size: 0.8rem;">
                    @if($creation_method === 'with_asset')
                        Asset-Based Fuel Order
                    @else
                        Direct Fuel Order
                    @endif
                </span>
            </div>
            <button type="button" wire:click="resetCreationMethod" class="btn btn-outline-secondary rounded-pill px-4 py-2 text-sm d-inline-flex align-items-center gap-2" style="cursor: pointer;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Go Back / Change Method
            </button>
        </div>

        <form @submit.prevent="handleSubmit()" class="row g-4">
            @if($creation_method === 'with_asset')
                <div class="col-12">
                    <label for="asset_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Select Asset</label>
                    <select wire:model.live="asset_id" id="asset_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1">
                        <option value="">-- Choose an Asset --</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->fleet_no }} - {{ $asset->assetType->name ?? 'Unknown Type' }}</option>
                        @endforeach
                    </select>
                    @error('asset_id') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                @if($asset_id)
                    <div class="col-md-6">
                        <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
                        <input type="date" wire:model.live="date_from" id="date_from" class="form-control bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1">
                        @error('date_from') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
                        <input type="date" wire:model.live="date_to" id="date_to" class="form-control bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1">
                        @error('date_to') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($asset_id && $date_from && $date_to)
                    <div class="col-12">
                        <div class="p-4 p-md-5 bg-secondary bg-opacity-10 rounded-2 border border-secondary border-opacity-25">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3 border-bottom border-secondary border-opacity-25 pb-4">
                                <div class="mb-0 text-secondary small fw-medium tracking-wide">
                                    Entries in Range: 
                                    @if($unprocessed_entries_count > 0)
                                        <button type="button" @click="showEntriesModal = true" class="btn btn-link text-primary text-decoration-none fw-bold p-0 ms-1 d-inline-flex align-items-center gap-1" style="font-size: 1rem; cursor: pointer;" title="Click to view entries">
                                            {{ number_format($unprocessed_entries_count, 0) }} 
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                                        </button>
                                    @else
                                       <strong class="text-primary h5 mb-0 ms-1">{{ $unprocessed_entries_count }}</strong>
                                    @endif
                                </div>
                                <div class="d-flex gap-4">
                                    <p class="mb-0 text-secondary small fw-medium tracking-wide">KM Factor: <strong class="text-primary ms-1">{{ number_format($fuel_factor_km, 2) }} KM/L</strong></p>
                                    <p class="mb-0 text-secondary small fw-medium tracking-wide">HR Factor: <strong class="text-primary ms-1">{{ number_format($fuel_factor_hr, 2) }} L/HR</strong></p>
                                </div>
                            </div>

                            @if($unprocessed_entries_count === 0)
                                <!-- Informative Terminal Caution Alert Box (P0 Dead-End Avoidance) -->
                                <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-20 text-warning rounded-2 p-4 mb-4" role="alert">
                                    <div class="d-flex align-items-start gap-3">
                                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="flex-shrink-0 mt-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <div>
                                            <h4 class="h6 fw-bold mb-2 text-uppercase tracking-wider">Zero Utilization Entries Found</h4>
                                            <p class="mb-0 small">No unprocessed utilization entries exist for this asset within the selected date range (<span class="text-white font-monospace">{{ $date_from }}</span> to <span class="text-white font-monospace">{{ $date_to }}</span>).</p>
                                            <p class="mb-0 small mt-2">A fuel order requires at least one utilization entry to calculate consumption. Please adjust your dates or log asset utilization first.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if(count($grouped_totals) > 0)
                                <div class="mt-4 mb-5 border-top border-secondary border-opacity-10 pt-4">
                                    <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-3">Breakdown by Charged To</h4>
                                    <div class="table-responsive rounded-2 border border-secondary border-opacity-25">
                                        <table class="table table-dark table-hover mb-0 align-middle">
                                            <thead>
                                                <tr class="bg-secondary bg-opacity-10">
                                                    <th class="ps-4 py-2 small fw-bold text-secondary text-uppercase">Account</th>
                                                    <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Total KM</th>
                                                    <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Total Hours</th>
                                                    <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Fuel (L)</th>
                                                    <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Remaining (L)</th>
                                                    <th class="pe-4 py-2 small fw-bold text-secondary text-uppercase text-end">Balance (L)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($grouped_totals as $account => $totals)
                                                    <tr>
                                                        <td class="ps-4 py-3 small fw-bold text-light">{{ $account }}</td>
                                                        <td class="px-4 py-3 small text-primary text-end font-monospace">{{ number_format($totals['kilometers'], 2) }}</td>
                                                        <td class="px-4 py-3 small text-primary text-end font-monospace">{{ number_format($totals['hours'], 2) }}</td>
                                                        <td class="px-4 py-3 small text-success text-end font-monospace fw-bold">{{ number_format($totals['quantity'], 2) }}</td>
                                                        <td class="px-4 py-3 small text-secondary text-end font-monospace fw-bold">{{ isset($totals['remaining']) ? number_format($totals['remaining'], 2) : '—' }}</td>
                                                        <td class="pe-4 py-3 small {{ $totals['balance'] < 0 ? 'text-danger' : 'text-info' }} text-end font-monospace fw-bold">{{ isset($totals['balance']) ? number_format($totals['balance'], 2) : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total Calculated KM</label>
                                    <input type="text" readonly value="{{ number_format($calculated_kilometers, 2) }}" class="form-control-plaintext bg-dark text-light border border-secondary border-opacity-25 py-3 px-4 rounded-1 h4 mb-0 fw-black text-center">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total Calculated Hours</label>
                                    <input type="text" readonly value="{{ number_format($calculated_hours, 2) }}" class="form-control-plaintext bg-dark text-light border border-secondary border-opacity-25 py-3 px-4 rounded-1 h4 mb-0 fw-black text-center">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">Calculated Fuel (Liters)</label>
                                    <input type="text" readonly value="{{ number_format($calculated_quantity, 2) }}" class="form-control-plaintext bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-3 px-4 rounded-1 h4 mb-0 fw-black text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Always visible but contextually disabled fields (P0 Avoidance) -->
                    <div class="col-12 mt-5">
                        <label for="say_quantity" class="form-label h6 fw-bold text-secondary text-uppercase tracking-wider mb-3">Replenishment Fuel Quantity (Liters)</label>
                        <div class="input-group input-group-lg border border-secondary border-opacity-25 rounded-1 overflow-hidden">
                            <input type="number" step="0.01" wire:model="say_quantity" id="say_quantity" class="form-control bg-dark text-light border-0 py-3 px-4 h4 mb-0 fw-black" placeholder="0.00" {{ $unprocessed_entries_count === 0 ? 'disabled' : '' }}>
                            <span class="input-group-text bg-dark text-secondary border-0 fw-bold px-4">L</span>
                        </div>
                        @error('say_quantity') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="col-12 mt-5 pt-3">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-1 fw-black text-uppercase tracking-widest transition duration-150" {{ $unprocessed_entries_count === 0 ? 'disabled' : '' }}>
                                Create Fuel Order
                            </button>
                        </div>
                    </div>
                @endif
            @elseif($creation_method === 'direct')
                <div class="col-md-6">
                    <label for="chargeable_account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Chargeable Account <span class="text-danger">*</span></label>
                    <select wire:model.live="chargeable_account_id" id="chargeable_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1">
                        <option value="">-- Choose an Account --</option>
                        @foreach($chargeable_accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('chargeable_account_id') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="sub_account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Sub-Account <span class="text-danger">*</span></label>
                    <select wire:model="sub_account_id" id="sub_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1" {{ count($sub_accounts) === 0 ? 'disabled' : '' }}>
                        <option value="">-- Choose a Sub-Account --</option>
                        @foreach($sub_accounts as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                        @endforeach
                    </select>
                    @error('sub_account_id') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch py-2">
                        <input class="form-check-input bg-dark border-secondary cursor-pointer" type="checkbox" wire:model="unbudgeted" id="unbudgeted">
                        <label class="form-check-label small fw-bold text-secondary text-uppercase tracking-wider ms-2 cursor-pointer" for="unbudgeted">
                            Unbudgeted Direct Expense
                        </label>
                    </div>
                    @error('unbudgeted') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label for="remarks" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Remarks / Justification <span class="text-danger">*</span></label>
                    <textarea wire:model="remarks" id="remarks" rows="3" class="form-control bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-1" placeholder="Provide the reason or justification for this direct fuel order..."></textarea>
                    @error('remarks') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 mt-5">
                    <label for="say_quantity" class="form-label h6 fw-bold text-secondary text-uppercase tracking-wider mb-3">Fuel Quantity (Liters) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg border border-secondary border-opacity-25 rounded-1 overflow-hidden">
                        <input type="number" step="0.01" wire:model="say_quantity" id="say_quantity" class="form-control bg-dark text-light border-0 py-3 px-4 h4 mb-0 fw-black" placeholder="0.00">
                        <span class="input-group-text bg-dark text-secondary border-0 fw-bold px-4">L</span>
                    </div>
                    @error('say_quantity') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 mt-5 pt-3">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-1 fw-black text-uppercase tracking-widest transition duration-150">
                            Create Fuel Order
                        </button>
                    </div>
                </div>
            @endif
        </form>
    @endif

    <!-- Modal for Unprocessed Entries -->
    <div class="modal fade" id="entriesModal" tabindex="-1" aria-labelledby="entriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary rounded-2 shadow-none overflow-hidden">
                <!-- Header -->
                <div class="modal-header bg-dark border-bottom border-secondary border-opacity-25 px-4 px-md-5 py-4">
                    <div>
                        <h3 class="modal-title h5 fw-black text-light" id="entriesModalLabel">Unprocessed Utilization Entries</h3>
                        <p class="text-secondary small mb-0 fw-medium">These logs will be covered by the new fuel order.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="showEntriesModal = false" aria-label="Close"></button>
                </div>

                <!-- Scrollable Content -->
                <div class="modal-body p-4 p-md-5 custom-scrollbar overflow-auto" style="max-height: 70vh;">
                    @if(count($unprocessed_entries) > 0)
                        <div class="table-responsive rounded-2 border border-secondary border-opacity-25">
                            <table class="table table-dark table-hover align-middle mb-0" style="min-width: 1200px;">
                                <thead class="bg-secondary bg-opacity-10">
                                    <tr>
                                        <th class="ps-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider">Date & Time</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider">Unbudgeted</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider">Particulars</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider">Charged To</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider">Calc Type</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Start KM</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">End KM</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Start HR</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">End HR</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Calc KM</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Calc HR</th>
                                        <th class="px-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Calc Qty</th>
                                        <th class="pe-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unprocessed_entries as $entry)
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="small fw-bold text-light">{{ $entry['date'] }}</div>
                                                <div class="text-secondary" style="font-size: 0.75rem;">{{ $entry['start_time'] }} - {{ $entry['end_time'] }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($entry['unbudgeted'])
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 rounded-1 px-2 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Yes</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-1 px-2 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">No</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="small text-light text-truncate" style="max-width: 150px;" title="{{ $entry['particulars'] }}">{{ $entry['particulars'] }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="small text-primary fw-bold text-truncate" style="max-width: 120px;" title="{{ $entry['charged_to'] }}">{{ $entry['charged_to'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 small text-light">{{ $entry['calculation_type'] }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['start_kilometer_reading'], 2) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['end_kilometer_reading'], 2) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['start_hour_reading'], 2) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['end_hour_reading'], 2) }}</td>
                                            <td class="px-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_kilometers'] ?? 0, 2) }}</td>
                                            <td class="px-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_hours'] ?? 0, 2) }}</td>
                                            <td class="px-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_quantity'] ?? 0, 2) }}</td>
                                            <td class="pe-4 py-3 text-end align-middle">
                                                <a href="{{ route('utilization-entries.show', $entry['id']) }}" target="_blank" class="btn btn-link text-info p-1 text-decoration-none d-inline-flex align-items-center gap-1 small" title="View Entry in New Tab" style="cursor: pointer;">
                                                    View <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                <svg width="32" height="32" class="text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="h6 fw-bold text-light">No Entries</h3>
                            <p class="text-secondary small">No unprocessed utilization entries found for this asset.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Footer -->
                <div class="modal-footer bg-dark border-top border-secondary border-opacity-25 px-4 px-md-5 py-3">
                    <button type="button" class="btn btn-secondary rounded-1 px-4 fw-bold text-uppercase small tracking-widest transition duration-150" @click="showEntriesModal = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Waiver Confirmation (with Dynamic Exceeded Details - P1 Detail Upgrade) -->
    <div class="modal fade" id="waiverModal" tabindex="-1" aria-labelledby="waiverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-danger border-start border-4 rounded-2 shadow-none overflow-hidden">
                <!-- Header -->
                <div class="modal-header bg-dark border-bottom border-secondary border-opacity-25 px-4 py-3">
                    <h3 class="modal-title h5 fw-black text-danger d-flex align-items-center gap-2" id="waiverModalLabel">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Waiver Required
                    </h3>
                    <button type="button" class="btn-close btn-close-white" @click="showWaiverModal = false" aria-label="Close"></button>
                </div>
                <!-- Content -->
                <div class="modal-body p-4 text-light">
                    <p class="mb-3">The requested fuel quantity exceeds the remaining allocated budget for the following accounts:</p>
                    
                    <!-- Dynamic Deficits List -->
                    <ul class="list-unstyled mb-4 p-3 bg-secondary bg-opacity-5 rounded-1 border border-secondary border-opacity-10">
                        @if($asset_id)
                            @foreach($grouped_totals as $account => $totals)
                                @if($totals['balance'] < 0)
                                    <li class="small text-danger font-monospace fw-bold mb-2 last-mb-0 d-flex justify-content-between">
                                        <span>{{ $account }}</span>
                                        <span>Exceeded by {{ number_format(abs($totals['balance']), 2) }} L</span>
                                    </li>
                                @endif
                            @endforeach
                        @elseif(!$asset_id && $sub_account_id && $say_quantity > 0)
                            @php
                                $subAccModel = \App\Models\SubAccount::find($sub_account_id);
                                $remainingBal = $subAccModel ? $subAccModel->remainingBudget() : 0;
                                $balanceDeficit = $say_quantity - $remainingBal;
                            @endphp
                            @if($balanceDeficit > 0 && $subAccModel && $subAccModel->type !== 'Uncontrolled')
                                <li class="small text-danger font-monospace fw-bold mb-2 last-mb-0 d-flex justify-content-between">
                                    <span>{{ $subAccModel->chargeableAccount->name }} - {{ $subAccModel->name }}</span>
                                    <span>Exceeded by {{ number_format($balanceDeficit, 2) }} L</span>
                                </li>
                            @endif
                        @endif
                    </ul>

                    <p class="mb-0 text-secondary small">This fuel order will be created in a <strong class="text-warning">PENDING WAIVER</strong> status and will require administrative approval before it can be processed or printed.</p>
                </div>
                <!-- Footer -->
                <div class="modal-footer bg-dark border-top border-secondary border-opacity-25 px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-1 px-4 fw-bold text-uppercase small tracking-widest transition duration-150" @click="showWaiverModal = false">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger rounded-1 px-4 fw-bold text-uppercase small tracking-widest transition duration-150" @click="showWaiverModal = false; $wire.submit();">
                        Confirm & Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
        if (component.id === $wire.$id) {
            if (window.showLoadingIndicator) window.showLoadingIndicator();
            
            succeed(() => {
                if (window.hideLoadingIndicator) window.hideLoadingIndicator();
            });
            
            fail(() => {
                if (window.hideLoadingIndicator) window.hideLoadingIndicator();
            });
        }
    });
</script>
@endscript
