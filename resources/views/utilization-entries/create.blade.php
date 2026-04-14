<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Log Utilization') }}: {{ $asset->fleet_no }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-800">
                <div class="p-8 text-gray-100">
                    <form method="POST" action="{{ route('utilization-entries.store') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="asset_id" value="{{ $asset->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="date" :value="__('Date')" class="text-gray-400" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="start_time" :value="__('Start Time')" class="text-gray-400" />
                                    <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('start_time', date('H:i'))" required />
                                    <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_time" :value="__('End Time')" class="text-gray-400" />
                                    <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('end_time')" required />
                                    <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="reference" :value="__('Reference')" class="text-gray-400" />
                                <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('reference')" required placeholder="e.g. REF-001" />
                                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="calculation_type" :value="__('Calculation Type')" class="text-gray-400" />
                                <select id="calculation_type" name="calculation_type" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Select Calculation Type --</option>
                                    <option value="Kilometer Reading" {{ old('calculation_type') == 'Kilometer Reading' ? 'selected' : '' }}>Kilometer Reading</option>
                                    <option value="Hour Reading" {{ old('calculation_type') == 'Hour Reading' ? 'selected' : '' }}>Hour Reading</option>
                                    <option value="Actual Operation Hours" {{ old('calculation_type') == 'Actual Operation Hours' ? 'selected' : '' }}>Actual Operation Hours</option>
                                </select>
                                <x-input-error :messages="$errors->get('calculation_type')" class="mt-2" />
                            </div>

                            <!-- Unbudgeted Checkbox -->
                            <div class="mt-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="unbudgeted" value="1" {{ old('unbudgeted') ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-800 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-300">{{ __('Unbudgeted') }}</span>
                                </label>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="particulars" :value="__('Particulars')" class="text-gray-400" />
                                <x-text-input id="particulars" name="particulars" type="text" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('particulars')" required placeholder="e.g. Daily Operation, Maintenance, etc." />
                                <x-input-error :messages="$errors->get('particulars')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="start_kilometer_reading" :value="__('Start Odometer (KM)')" class="text-gray-400" />
                                <div class="relative">
                                    <x-text-input id="start_kilometer_reading" name="start_kilometer_reading" type="number" step="0.01" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 pr-12" :value="old('start_kilometer_reading')" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">KM</span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('start_kilometer_reading')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="end_kilometer_reading" :value="__('End Odometer (KM)')" class="text-gray-400" />
                                <div class="relative">
                                    <x-text-input id="end_kilometer_reading" name="end_kilometer_reading" type="number" step="0.01" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 pr-12" :value="old('end_kilometer_reading')" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">KM</span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('end_kilometer_reading')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="start_hour_reading" :value="__('Start Engine Hours')" class="text-gray-400" />
                                <div class="relative">
                                    <x-text-input id="start_hour_reading" name="start_hour_reading" type="number" step="0.01" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 pr-12" :value="old('start_hour_reading')" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">HR</span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('start_hour_reading')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="end_hour_reading" :value="__('End Engine Hours')" class="text-gray-400" />
                                <div class="relative">
                                    <x-text-input id="end_hour_reading" name="end_hour_reading" type="number" step="0.01" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 pr-12" :value="old('end_hour_reading')" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">HR</span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('end_hour_reading')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="driver_operator_name" :value="__('Driver / Operator Name')" class="text-gray-400" />
                                <x-text-input id="driver_operator_name" name="driver_operator_name" type="text" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500" :value="old('driver_operator_name')" required />
                                <x-input-error :messages="$errors->get('driver_operator_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="chargeable_account_id" :value="__('Charged To')" class="text-gray-400" />
                                <select id="chargeable_account_id" name="chargeable_account_id" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 rounded-md shadow-sm" required onchange="fetchSubAccounts(this.value)">
                                    <option value="">-- Select Account --</option>
                                    @foreach($chargeableAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('chargeable_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('chargeable_account_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="sub_account_id" :value="__('Sub Account')" class="text-gray-400" />
                                <select id="sub_account_id" name="sub_account_id" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Select Sub Account --</option>
                                </select>
                                <x-input-error :messages="$errors->get('sub_account_id')" class="mt-2" />
                            </div>

                            <script>
                                async function fetchSubAccounts(accountId, selectedId = null) {
                                    const subAccountSelect = document.getElementById('sub_account_id');
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
                                        
                                        subAccountSelect.disabled = false;
                                    } catch (error) {
                                        console.error('Error fetching sub-accounts:', error);
                                        subAccountSelect.innerHTML = '<option value="">Error loading sub-accounts</option>';
                                    }
                                }

                                // Initialize sub-accounts on load (in case of validation error)
                                document.addEventListener('DOMContentLoaded', function() {
                                    const accountId = document.getElementById('chargeable_account_id').value;
                                    const selectedSubId = "{{ old('sub_account_id') }}";
                                    if (accountId) {
                                        fetchSubAccounts(accountId, selectedSubId);
                                    }
                                });
                            </script>

                            <div class="md:col-span-2">
                                <x-input-label for="remarks" :value="__('Remarks')" class="text-gray-400" />
                                <textarea id="remarks" name="remarks" rows="3" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white focus:ring-indigo-500 rounded-lg shadow-sm border">{{ old('remarks') }}</textarea>
                                <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-800 gap-x-4">
                            <a href="{{ route('assets.show', $asset) }}" class="text-sm text-gray-400 hover:text-white transition-colors">Cancel</a>
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/20">
                                {{ __('Save Entry') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
