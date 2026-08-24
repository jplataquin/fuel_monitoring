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
                        <form id="utilization-entry-form" method="POST" action="{{ route('utilization-entries.update', $utilizationEntry) }}">
                            @csrf
                            @method('PATCH')

                            <div class="row g-4 mb-4">
                                <div class="col-md-12">
                                    <label for="calculation_type" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Calculation Type</label>
                                    <select id="calculation_type" name="calculation_type" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required>
                                        <option value="">-- Select Calculation Type --</option>
                                        <option value="Kilometer Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Kilometer Reading' ? 'selected' : '' }}>Kilometer Reading</option>
                                        <option value="Hour Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Hour Reading' ? 'selected' : '' }}>Hour Reading</option>
                                        <option value="Timeframe" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Timeframe' ? 'selected' : '' }}>Timeframe</option>
                                        <option value="Actual Hours" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Actual Hours' ? 'selected' : '' }}>Actual Hours</option>
                                    </select>
                                    @error('calculation_type') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

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
                                    <label for="actual_hours" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Hours</label>
                                    <input type="number" step="0.01" id="actual_hours" name="actual_hours" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('actual_hours', $utilizationEntry->actual_hours) }}" placeholder="0.00" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46">
                                    @error('actual_hours') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="reference" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Reference</label>
                                    <input type="text" id="reference" name="reference" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" value="{{ old('reference', $utilizationEntry->reference) }}" required>
                                    @error('reference') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                          

                                <div class="col-12">
                                    <label for="unbudgeted" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Unbudgeted Utilization</label>
                                    <select id="unbudgeted" name="unbudgeted" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required onchange="toggleSubAccount()">
                                        <option value="0" {{ old('unbudgeted', $utilizationEntry->unbudgeted) ? '' : 'selected' }}>No</option>
                                        <option value="1" {{ old('unbudgeted', $utilizationEntry->unbudgeted) ? 'selected' : '' }}>Yes</option>
                                    </select>
                                    @error('unbudgeted') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label for="particulars" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Particulars / Mission</label>
                                    <textarea id="particulars" name="particulars" class="form-control bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required>{{ old('particulars', $utilizationEntry->particulars) }}</textarea>
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
                                
                                        {{$utilizationEntry->chargeableAccount->status}} {{$utilizationEntry->chargeableAccount->deleted_at}}

                                    <select id="chargeable_account_id" name="chargeable_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required onchange="fetchSubAccounts(this.value)">
                                        <option value="">-- Select Account --</option>
                                        
                                    
                                        @if($utilizationEntry->chargeableAccount->status != 'ACTIVE' || $utilizationEntry->chargeableAccount->deleted_at != null)
                                        <!--
                                             <option value="{{ $utilizationEntry->chargeableAccount->id }}" 

                                                    data-classification="{{ $utilizationEntry->chargeableAccount->classification }}"
                                                    data-start-date="{{ $utilizationEntry->chargeableAccount->start_date ? $utilizationEntry->chargeableAccount->start_date->format('Y-m-d') : '' }}"
                                                    data-end-date="{{ $utilizationEntry->chargeableAccount->end_date ? $utilizationEntry->chargeableAccount->end_date->format('Y-m-d') : '' }}"
                                                    {{ old('chargeable_account_id', $utilizationEntry->chargeable_account_id) == $utilizationEntry->chargeableAccount->id ? 'selected' : '' }}
                                                    
                                                >
                                                {{ $utilizationEntry->chargeableAccount->name }}
                                                @if($utilizationEntry->chargeableAccount->classification === 'Scoped')
                                                    ({{ $utilizationEntry->chargeableAccount->start_date ? $utilizationEntry->chargeableAccount->start_date->format('M d, Y') : 'N/A' }} - {{ $utilizationEntry->chargeableAccount->end_date ? $utilizationEntry->chargeableAccount->end_date->format('M d, Y') : 'N/A' }})
                                                @endif
                                            </option>
                                        -->
                                         @endif

                                        @foreach($chargeableAccounts as $account)
                                            <option value="{{ $account->id }}" 

                                                    data-classification="{{ $account->classification }}"
                                                    data-start-date="{{ $account->start_date ? $account->start_date->format('Y-m-d') : '' }}"
                                                    data-end-date="{{ $account->end_date ? $account->end_date->format('Y-m-d') : '' }}"
                                                    {{ old('chargeable_account_id', $utilizationEntry->chargeable_account_id) == $account->id ? 'selected' : '' }}
                                                    
                                                    >
                                                {{ $account->name }}
                                                @if($account->classification === 'Scoped')
                                                    ({{ $account->start_date ? $account->start_date->format('M d, Y') : 'N/A' }} - {{ $account->end_date ? $account->end_date->format('M d, Y') : 'N/A' }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('chargeable_account_id') <div class="text-danger small fw-bold mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="sub_account_id" class="form-label small fw-bold text-secondary text-uppercase tracking-wider">Sub Account</label>
                                    <select id="sub_account_id" name="sub_account_id" class="form-select bg-dark text-light border-secondary border-opacity-50 py-2 px-3 rounded-3" required>
                                        <option value="">-- Select Sub Account --</option>
                                        @if($utilizationEntry->chargeableAccount)
                                            @foreach($utilizationEntry->chargeableAccount->subAccounts as $sub)
                                                <option value="{{ $sub->id }}" {{ old('sub_account_id', $utilizationEntry->sub_account_id) == $sub->id ? 'selected' : '' }}>
                                                    {{ $sub->display_name }}
                                                </option>
                                            @endforeach
                                        @endif
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
        function toggleSubAccount(isInit = false) {
            const unbudgetedSelect = document.getElementById('unbudgeted');
            const subAccountSelect = document.getElementById('sub_account_id');
            
            if (unbudgetedSelect.value === '1') {
                subAccountSelect.value = '';
                subAccountSelect.disabled = true;
                subAccountSelect.required = false;
            } else {
                const accountId = document.getElementById('chargeable_account_id').value;
                if (accountId) {
                    subAccountSelect.disabled = false;
                    if (!isInit) {
                        fetchSubAccounts(accountId);
                    }
                }
                subAccountSelect.required = true;
            }
        }

        async function fetchSubAccounts(accountId, selectedId = null) {
            const subAccountSelect = document.getElementById('sub_account_id');
            const unbudgetedSelect = document.getElementById('unbudgeted');
            
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
                    option.textContent = sub.display_name;
                    if (selectedId && sub.id == selectedId) {
                        option.selected = true;
                    }
                    subAccountSelect.appendChild(option);
                });
                
                if (unbudgetedSelect.value !== '1') {
                    subAccountSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error fetching sub-accounts:', error);
                subAccountSelect.innerHTML = '<option value="">Error loading sub-accounts</option>';
            }
        }

        function parseLocalDate(dateStr) {
            if (!dateStr) return null;
            const parts = dateStr.split('-');
            if (parts.length !== 3) return null;
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
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

            if (classification && classification.toLowerCase() === 'scoped') {
                const selectedDate = parseLocalDate(dateInput.value);
                if (!selectedDate) return;
                
                let isInvalid = false;
                let message = '';

                if (startDateStr) {
                    const startDate = parseLocalDate(startDateStr);
                    if (startDate && selectedDate < startDate) {
                        isInvalid = true;
                        message = `The date must be on or after ${formatDate(startDateStr)}.`;
                    }
                }

                if (endDateStr && !isInvalid) {
                    const endDate = parseLocalDate(endDateStr);
                    if (endDate && selectedDate > endDate) {
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
            const date = parseLocalDate(dateStr);
            if (!date) return '';
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function updateFieldStates() {
            const calculationType = document.getElementById('calculation_type').value;
            const startOdo = document.getElementById('start_kilometer_reading');
            const endOdo = document.getElementById('end_kilometer_reading');
            const startEngine = document.getElementById('start_hour_reading');
            const endEngine = document.getElementById('end_hour_reading');
            const actualHours = document.getElementById('actual_hours');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');

            const setRequired = (el, isRequired) => {
                if (el) {
                    el.required = isRequired;
                    const label = document.querySelector(`label[for="${el.id}"]`);
                    if (label) {
                        let ast = label.querySelector('.req-ast');
                        if (isRequired) {
                            if (!ast) {
                                label.insertAdjacentHTML('beforeend', ' <span class="text-danger req-ast">*</span>');
                            }
                        } else {
                            if (ast) {
                                ast.remove();
                            }
                        }
                    }
                }
            };

            // Reset all to not required first
            setRequired(startOdo, false);
            setRequired(endOdo, false);
            setRequired(startEngine, false);
            setRequired(endEngine, false);
            setRequired(actualHours, false);
            setRequired(startTime, false);
            setRequired(endTime, false);

            if (calculationType === 'Kilometer Reading') {
                setRequired(startOdo, true);
                setRequired(endOdo, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Hour Reading') {
                setRequired(startEngine, true);
                setRequired(endEngine, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Timeframe') {
                setRequired(startTime, true);
                setRequired(endTime, true);
            } else if (calculationType === 'Actual Hours') {
                setRequired(actualHours, true);
            } else {
                setRequired(startOdo, true);
                setRequired(endOdo, true);
                setRequired(startEngine, true);
                setRequired(endEngine, true);
                setRequired(actualHours, true);
                setRequired(startTime, true);
                setRequired(endTime, true);
            }
        }

        // Initialize sub-accounts on load
        document.addEventListener('DOMContentLoaded', function() {
            const accountId = document.getElementById('chargeable_account_id').value;
            const selectedSubId = "{{ old('sub_account_id', $utilizationEntry->sub_account_id) }}";
            
            toggleSubAccount(true);
            
            if (accountId) {
                fetchSubAccounts(accountId, selectedSubId);
            }

            const dateInput = document.getElementById('date');
            const accountSelect = document.getElementById('chargeable_account_id');
            const calculationTypeSelect = document.getElementById('calculation_type');
            const form = document.getElementById('utilization-entry-form');
            
            dateInput.addEventListener('input', validateDateScope);
            dateInput.addEventListener('change', validateDateScope);
            accountSelect.addEventListener('change', validateDateScope);
            calculationTypeSelect.addEventListener('change', updateFieldStates);

            validateDateScope();
            updateFieldStates();

            if (form) {
                form.addEventListener('submit', function() {
                    const disabledInputs = this.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
                    disabledInputs.forEach(el => el.disabled = false);
                });
            }
        });
    </script>
</x-app-layout>
