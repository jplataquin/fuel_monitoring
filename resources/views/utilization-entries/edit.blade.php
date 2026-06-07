<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-light mb-0">
            {{ __('Refine Log Entry') }}
        </h2>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('utilization-entries.update', $utilizationEntry) }}">
                            @csrf
                            @method('PATCH')

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="date" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Utilization Date</label>
                                    <input type="date" id="date" name="date" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('date', $utilizationEntry->date->format('Y-m-d')) }}" required>
                                    <div id="date-scope-error" class="text-danger small fw-bold mt-1 d-none"></div>
                                    @error('date') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label for="start_time" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Start Time</label>
                                            <input type="time" id="start_time" name="start_time" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('start_time', $utilizationEntry->start_time ? $utilizationEntry->start_time->format('H:i') : '') }}" required>
                                            @error('start_time') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-6">
                                            <label for="end_time" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">End Time</label>
                                            <input type="time" id="end_time" name="end_time" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('end_time', $utilizationEntry->end_time ? $utilizationEntry->end_time->format('H:i') : '') }}" required>
                                            @error('end_time') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="reference" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Reference</label>
                                    <input type="text" id="reference" name="reference" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('reference', $utilizationEntry->reference) }}" required>
                                    @error('reference') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="calculation_type" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Calculation Type</label>
                                    <select id="calculation_type" name="calculation_type" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required>
                                        <option value="">-- Select Calculation Type --</option>
                                        <option value="Kilometer Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Kilometer Reading' ? 'selected' : '' }}>Kilometer Reading</option>
                                        <option value="Hour Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Hour Reading' ? 'selected' : '' }}>Hour Reading</option>
                                        <option value="Actual Operation Hours" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Actual Operation Hours' ? 'selected' : '' }}>Actual Operation Hours</option>
                                    </select>
                                    @error('calculation_type') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-10 ps-5">
                                        <input type="checkbox" id="unbudgeted" name="unbudgeted" value="1" {{ old('unbudgeted', $utilizationEntry->unbudgeted) ? 'checked' : '' }} class="form-check-input" onchange="toggleSubAccount()">
                                        <label class="form-check-label small fw-bold text-light ms-2" for="unbudgeted">{{ __('Unbudgeted Utilization') }}</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="particulars" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Particulars / Mission</label>
                                    <input type="text" id="particulars" name="particulars" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('particulars', $utilizationEntry->particulars) }}" required>
                                    @error('particulars') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="start_kilometer_reading" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Start Odometer (KM)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="start_kilometer_reading" name="start_kilometer_reading" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-start-3 font-monospace" value="{{ old('start_kilometer_reading', $utilizationEntry->start_kilometer_reading) }}" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                        <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-50 fw-bold small">KM</span>
                                    </div>
                                    @error('start_kilometer_reading') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="end_kilometer_reading" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">End Odometer (KM)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="end_kilometer_reading" name="end_kilometer_reading" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-start-3 font-monospace" value="{{ old('end_kilometer_reading', $utilizationEntry->end_kilometer_reading) }}" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                        <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-50 fw-bold small">KM</span>
                                    </div>
                                    @error('end_kilometer_reading') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="start_hour_reading" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Start Engine Hours</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="start_hour_reading" name="start_hour_reading" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-start-3 font-monospace" value="{{ old('start_hour_reading', $utilizationEntry->start_hour_reading) }}" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                        <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-50 fw-bold small">HR</span>
                                    </div>
                                    @error('start_hour_reading') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="end_hour_reading" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">End Engine Hours</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="end_hour_reading" name="end_hour_reading" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-start-3 font-monospace" value="{{ old('end_hour_reading', $utilizationEntry->end_hour_reading) }}" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                        <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-50 fw-bold small">HR</span>
                                    </div>
                                    @error('end_hour_reading') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="driver_operator_name" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Personnel In-Charge</label>
                                    <input type="text" id="driver_operator_name" name="driver_operator_name" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('driver_operator_name', $utilizationEntry->driver_operator_name) }}" required>
                                    @error('driver_operator_name') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="chargeable_account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Charged To</label>
                                    <select id="chargeable_account_id" name="chargeable_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required onchange="fetchSubAccounts(this.value)">
                                        <option value="">-- Select Account --</option>
                                        @foreach($chargeableAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                    data-classification="{{ $account->classification }}"
                                                    data-start-date="{{ $account->start_date ? $account->start_date->format('Y-m-d') : '' }}"
                                                    data-end-date="{{ $account->end_date ? $account->end_date->format('Y-m-d') : '' }}"
                                                    {{ old('chargeable_account_id', $utilizationEntry->chargeable_account_id) == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('chargeable_account_id') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="sub_account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Sub Account</label>
                                    <select id="sub_account_id" name="sub_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required>
                                        <option value="">-- Select Sub Account --</option>
                                    </select>
                                    @error('sub_account_id') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Fuel Order Ref #</label>
                                    <div class="form-control bg-dark bg-opacity-50 text-light border-secondary border-opacity-25 py-2 px-3 rounded-3 small fw-bold tracking-widest h-auto">
                                        @if($utilizationEntry->fuel_order_id)
                                            #{{ $utilizationEntry->fuel_order_id }}
                                        @else
                                            <span class="text-secondary italic">SYSTEM DEFINED</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="remarks" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Additional Remarks</label>
                                    <textarea id="remarks" name="remarks" rows="3" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3">{{ old('remarks', $utilizationEntry->remarks) }}</textarea>
                                    @error('remarks') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center gap-3 pt-4 border-top border-secondary border-opacity-25">
                                <a href="{{ route('assets.show', $utilizationEntry->asset_id) }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest">Cancel</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-black small text-uppercase tracking-widest shadow-lg hover-translate-y">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSubAccount() {
            const unbudgetedCheckbox = document.getElementById('unbudgeted');
            const subAccountSelect = document.getElementById('sub_account_id');
            
            if (unbudgetedCheckbox.checked) {
                subAccountSelect.value = '';
                subAccountSelect.disabled = true;
                subAccountSelect.required = false;
            } else {
                const accountId = document.getElementById('chargeable_account_id').value;
                if (accountId) {
                    subAccountSelect.disabled = false;
                }
                subAccountSelect.required = true;
            }
        }

        async function fetchSubAccounts(accountId, selectedId = null) {
            const subAccountSelect = document.getElementById('sub_account_id');
            const unbudgetedCheckbox = document.getElementById('unbudgeted');
            
            subAccountSelect.innerHTML = '<option value="">-- Select Sub Account --</option>';
            
            if (!accountId) {
                subAccountSelect.disabled = true;
                return;
            }

            subAccountSelect.disabled = true;
            subAccountSelect.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`/chargeable-accounts/${accountId}/sub-accounts/json`);
                const subAccounts = await response.json();

                subAccountSelect.innerHTML = '<option value="">-- Select Sub Account --</option>';
                subAccounts.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    if (selectedId && sub.id == selectedId) {
                        option.selected = true;
                    }
                    subAccountSelect.appendChild(option);
                });
                
                if (!unbudgetedCheckbox.checked) {
                    subAccountSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error fetching sub-accounts:', error);
                subAccountSelect.innerHTML = '<option value="">Error loading sub-accounts</option>';
            }
        }

        function validateDateScope() {
            const dateInput = document.getElementById('date');
            const accountSelect = document.getElementById('chargeable_account_id');
            const errorDiv = document.getElementById('date-scope-error');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (!dateInput.value || !accountSelect.value) {
                errorDiv.classList.add('d-none');
                errorDiv.textContent = '';
                submitBtn.disabled = false;
                return;
            }

            const selectedOption = accountSelect.options[accountSelect.selectedIndex];
            const classification = selectedOption.getAttribute('data-classification');
            const startDateStr = selectedOption.getAttribute('data-start-date');
            const endDateStr = selectedOption.getAttribute('data-end-date');

            if (classification === 'Scoped') {
                const selectedDate = new Date(dateInput.value);
                selectedDate.setHours(0,0,0,0);
                
                let isInvalid = false;
                let message = '';

                if (startDateStr) {
                    const startDate = new Date(startDateStr);
                    startDate.setHours(0,0,0,0);
                    if (selectedDate < startDate) {
                        isInvalid = true;
                        message = `The date must be on or after ${formatDate(startDateStr)}.`;
                    }
                }

                if (endDateStr && !isInvalid) {
                    const endDate = new Date(endDateStr);
                    endDate.setHours(0,0,0,0);
                    if (selectedDate > endDate) {
                        isInvalid = true;
                        message = `The date must be on or before ${formatDate(endDateStr)}.`;
                    }
                }

                if (isInvalid) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('d-none');
                    submitBtn.disabled = true;
                } else {
                    errorDiv.classList.add('d-none');
                    errorDiv.textContent = '';
                    submitBtn.disabled = false;
                }
            } else {
                errorDiv.classList.add('d-none');
                errorDiv.textContent = '';
                submitBtn.disabled = false;
            }
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Initialize sub-accounts on load
        document.addEventListener('DOMContentLoaded', function() {
            const accountId = document.getElementById('chargeable_account_id').value;
            const selectedSubId = "{{ old('sub_account_id', $utilizationEntry->sub_account_id) }}";
            
            toggleSubAccount();
            
            if (accountId) {
                fetchSubAccounts(accountId, selectedSubId);
            }

            const dateInput = document.getElementById('date');
            const accountSelect = document.getElementById('chargeable_account_id');
            
            dateInput.addEventListener('change', validateDateScope);
            accountSelect.addEventListener('change', validateDateScope);

            validateDateScope();
        });
    </script>
</x-app-layout>
