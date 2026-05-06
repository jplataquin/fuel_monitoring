<div x-data="{ 
    showEntriesModal: false,
    modal: null,
    init() {
        this.modal = new bootstrap.Modal(document.getElementById('entriesModal'));
        this.$watch('showEntriesModal', value => {
            if (value) {
                this.modal.show();
            } else {
                this.modal.hide();
            }
        });
        document.getElementById('entriesModal').addEventListener('hidden.bs.modal', () => {
            this.showEntriesModal = false;
        });
    }
}">
    <form wire:submit="submit" class="row g-4">
        <div class="col-12">
            <label for="asset_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Select Asset</label>
            <select wire:model.live="asset_id" id="asset_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-3">
                <option value="">-- Choose an Asset --</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->fleet_no }} - {{ $asset->assetType->name ?? 'Unknown Type' }}</option>
                @endforeach
            </select>
            @error('asset_id') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-6">
            <label for="date_from" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date From</label>
            <input type="date" wire:model.live="date_from" id="date_from" class="form-control bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-3">
            @error('date_from') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
            <label for="date_to" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Date To</label>
            <input type="date" wire:model.live="date_to" id="date_to" class="form-control bg-dark text-light border-secondary border-opacity-50 py-3 px-4 rounded-3">
            @error('date_to') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
        </div>

        @if($asset_id && $date_from && $date_to)
            <div class="col-12">
                <div class="p-4 p-md-5 bg-secondary bg-opacity-10 rounded-4 border border-secondary border-opacity-25 shadow-inner">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3 border-bottom border-secondary border-opacity-25 pb-4">
                        <p class="mb-0 text-secondary small fw-medium tracking-wide">
                            Entries in Range: 
                            @if($unprocessed_entries_count > 0)
                                <button type="button" @click="showEntriesModal = true" class="btn btn-link text-primary text-decoration-none fw-bold p-0 ms-1" style="font-size: 1.1rem;" title="Click to view entries">
                                    {{ number_format($unprocessed_entries_count, 2) }} 📁
                                </button>
                            @else
                               <strong class="text-primary h5 mb-0 ms-1">{{ $unprocessed_entries_count }}</strong>
                            @endif
                        </p>
                        <div class="d-flex gap-4">
                            <p class="mb-0 text-secondary small fw-medium tracking-wide">KM Factor: <strong class="text-primary ms-1">{{ number_format($fuel_factor_km, 2) }} KM/L</strong></p>
                            <p class="mb-0 text-secondary small fw-medium tracking-wide">HR Factor: <strong class="text-primary ms-1">{{ number_format($fuel_factor_hr, 2) }} L/HR</strong></p>
                        </div>
                    </div>
                    
                    @if(count($grouped_totals) > 0)
                        <div class="mt-4 mb-5 border-top border-secondary border-opacity-10 pt-4">
                            <h4 class="small fw-bold text-secondary text-uppercase tracking-wider mb-3">Breakdown by Charged To</h4>
                            <div class="table-responsive rounded-3 border border-secondary border-opacity-25 shadow-sm">
                                <table class="table table-dark table-hover mb-0 align-middle">
                                    <thead>
                                        <tr class="bg-secondary bg-opacity-10">
                                            <th class="ps-4 py-2 small fw-bold text-secondary text-uppercase">Account</th>
                                            <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Total KM</th>
                                            <th class="px-4 py-2 small fw-bold text-secondary text-uppercase text-end">Total Hours</th>
                                            <th class="pe-4 py-2 small fw-bold text-secondary text-uppercase text-end">Fuel (L)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($grouped_totals as $account => $totals)
                                            <tr>
                                                <td class="ps-4 py-3 small fw-bold text-light">{{ $account }}</td>
                                                <td class="px-4 py-3 small text-primary text-end font-monospace">{{ number_format($totals['kilometers'], 2) }}</td>
                                                <td class="px-4 py-3 small text-primary text-end font-monospace">{{ number_format($totals['hours'], 2) }}</td>
                                                <td class="pe-4 py-3 small text-success text-end font-monospace fw-bold">{{ number_format($totals['quantity'], 2) }}</td>
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
                            <input type="text" readonly value="{{ number_format($calculated_kilometers, 2) }}" class="form-control-plaintext bg-dark text-light border border-secondary border-opacity-25 py-3 px-4 rounded-3 h4 mb-0 fw-black text-center shadow-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">Total Calculated Hours</label>
                            <input type="text" readonly value="{{ number_format($calculated_hours, 2) }}" class="form-control-plaintext bg-dark text-light border border-secondary border-opacity-25 py-3 px-4 rounded-3 h4 mb-0 fw-black text-center shadow-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">Calculated Fuel (Liters)</label>
                            <input type="text" readonly value="{{ number_format($calculated_quantity, 2) }}" class="form-control-plaintext bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-3 px-4 rounded-3 h4 mb-0 fw-black text-center shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            @if($unprocessed_entries_count > 0)
                <div class="col-12 mt-5">
                    <label for="say_quantity" class="form-label h6 fw-bold text-secondary text-uppercase tracking-wider mb-3">Say Fuel Quantity (Liters)</label>
                    <div class="input-group input-group-lg shadow-sm border border-secondary border-opacity-25 rounded-3 overflow-hidden">
                        <input type="number" step="0.01" wire:model="say_quantity" id="say_quantity" class="form-control bg-dark text-light border-0 py-3 px-4 h4 mb-0 fw-black" placeholder="0.00">
                        <span class="input-group-text bg-dark text-secondary border-0 fw-bold px-4">L</span>
                    </div>
                    @error('say_quantity') <span class="text-danger small fw-semibold mt-1 d-block">{{ $message }}</span> @enderror
                </div>
                
                <div class="col-12 mt-5 pt-3">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-black text-uppercase tracking-widest shadow-lg hover-translate-y">
                            Create Fuel Order
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </form>

    <!-- Modal for Unprocessed Entries -->
    <div class="modal fade" id="entriesModal" tabindex="-1" aria-labelledby="entriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary rounded-4 shadow-lg overflow-hidden">
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
                        <div class="table-responsive rounded-3 border border-secondary border-opacity-25">
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
                                        <th class="pe-4 py-3 small fw-bold text-secondary text-uppercase tracking-wider text-end">Calc Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unprocessed_entries as $entry)
                                        <tr class="cursor-pointer" onclick="window.open('{{ route('utilization-entries.show', $entry['id']) }}', '_blank')">
                                            <td class="ps-4 py-3">
                                                <div class="small fw-bold text-light">{{ $entry['date'] }}</div>
                                                <div class="text-secondary" style="font-size: 10px;">{{ $entry['start_time'] }} - {{ $entry['end_time'] }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($entry['unbudgeted'])
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 rounded-pill px-2 py-1 text-uppercase fw-bold" style="font-size: 9px;">Yes</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-2 py-1 text-uppercase fw-bold" style="font-size: 9px;">No</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="small text-light text-truncate" style="max-width: 150px;" title="{{ $entry['particulars'] }}">{{ $entry['particulars'] }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="small text-primary fw-bold text-truncate" style="max-width: 120px;" title="{{ $entry['charged_to'] }}">{{ $entry['charged_to'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 small text-light">{{ $entry['calculation_type'] }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['start_kilometer_reading'], 1) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['end_kilometer_reading'], 1) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['start_hour_reading'], 1) }}</td>
                                            <td class="px-4 py-3 small text-primary font-monospace text-end fw-bold">{{ number_format($entry['end_hour_reading'], 1) }}</td>
                                            <td class="px-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_kilometers'] ?? 0, 1) }}</td>
                                            <td class="px-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_hours'] ?? 0, 1) }}</td>
                                            <td class="pe-4 py-3 small text-success font-monospace text-end fw-bold">{{ number_format($entry['calculated_quantity'] ?? 0, 2) }}</td>
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
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold text-uppercase small tracking-widest" @click="showEntriesModal = false">
                        Close
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
