<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Refine Log Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <form method="POST" action="{{ route('utilization-entries.update', $utilizationEntry) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8">
                        <div>
                            <x-input-label for="date" :value="__('Utilization Date')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="date" name="date" type="date" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('date', $utilizationEntry->date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                <x-text-input id="start_time" name="start_time" type="time" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('start_time', $utilizationEntry->start_time ? $utilizationEntry->start_time->format('H:i') : null)" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2 text-rose-400 text-xs font-bold" />
                            </div>

                            <div>
                                <x-input-label for="end_time" :value="__('End Time')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                <x-text-input id="end_time" name="end_time" type="time" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('end_time', $utilizationEntry->end_time ? $utilizationEntry->end_time->format('H:i') : null)" required />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2 text-rose-400 text-xs font-bold" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="reference" :value="__('Reference')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="reference" name="reference" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('reference', $utilizationEntry->reference)" required />
                            <x-input-error :messages="$errors->get('reference')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="calculation_type" :value="__('Calculation Type')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="calculation_type" name="calculation_type" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" required>
                                <option value="">-- Select Calculation Type --</option>
                                <option value="Kilometer Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Kilometer Reading' ? 'selected' : '' }}>Kilometer Reading</option>
                                <option value="Hour Reading" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Hour Reading' ? 'selected' : '' }}>Hour Reading</option>
                                <option value="Actual Operation Hours" {{ old('calculation_type', $utilizationEntry->calculation_type) == 'Actual Operation Hours' ? 'selected' : '' }}>Actual Operation Hours</option>
                            </select>
                            <x-input-error :messages="$errors->get('calculation_type')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="unbudgeted" value="1" {{ old('unbudgeted', $utilizationEntry->unbudgeted) ? 'checked' : '' }} class="w-5 h-5 !rounded !border-2 !border-[#D0BCFF] !bg-[#2D2930] text-[#D0BCFF] focus:ring-[#D0BCFF] transition-colors group-hover:border-[#EADDFF]">
                                <span class="!ml-3 text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] group-hover:text-[#E6E1E5] transition-colors">{{ __('Unbudgeted') }}</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="particulars" :value="__('Particulars / Mission')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="particulars" name="particulars" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('particulars', $utilizationEntry->particulars)" required />
                            <x-input-error :messages="$errors->get('particulars')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="start_kilometer_reading" :value="__('Start Odometer (KM)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="start_kilometer_reading" name="start_kilometer_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('start_kilometer_reading', $utilizationEntry->start_kilometer_reading)" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <x-input-error :messages="$errors->get('start_kilometer_reading')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="end_kilometer_reading" :value="__('End Odometer (KM)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="end_kilometer_reading" name="end_kilometer_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('end_kilometer_reading', $utilizationEntry->end_kilometer_reading)" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <x-input-error :messages="$errors->get('end_kilometer_reading')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="start_hour_reading" :value="__('Start Engine Hours')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="start_hour_reading" name="start_hour_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('start_hour_reading', $utilizationEntry->start_hour_reading)" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <x-input-error :messages="$errors->get('start_hour_reading')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="end_hour_reading" :value="__('End Engine Hours')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="end_hour_reading" name="end_hour_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('end_hour_reading', $utilizationEntry->end_hour_reading)" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <x-input-error :messages="$errors->get('end_hour_reading')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="driver_operator_name" :value="__('Personnel In-Charge')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="driver_operator_name" name="driver_operator_name" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('driver_operator_name', $utilizationEntry->driver_operator_name)" required />
                            <x-input-error :messages="$errors->get('driver_operator_name')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="chargeable_account_id" :value="__('Charged To')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="chargeable_account_id" name="chargeable_account_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" required onchange="fetchSubAccounts(this.value)">
                                <option value="">-- Select Account --</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('chargeable_account_id', $utilizationEntry->chargeable_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('chargeable_account_id')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="sub_account_id" :value="__('Sub Account')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="sub_account_id" name="sub_account_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" required>
                                <option value="">-- Select Sub Account --</option>
                            </select>
                            <x-input-error :messages="$errors->get('sub_account_id')" class="mt-2 text-rose-400 text-xs font-bold" />
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

                            // Initialize sub-accounts on load
                            document.addEventListener('DOMContentLoaded', function() {
                                const accountId = document.getElementById('chargeable_account_id').value;
                                const selectedSubId = "{{ old('sub_account_id', $utilizationEntry->sub_account_id) }}";
                                if (accountId) {
                                    fetchSubAccounts(accountId, selectedSubId);
                                }
                            });
                        </script>

                        <div>
                            <x-input-label for="fuel_order_id" :value="__('Fuel Order Ref #')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <div class="block w-full bg-[#2D2930]/50 border border-[#49454F]/50 text-[#E6E1E5] rounded-xl p-3 text-sm font-bold tracking-widest">
                                @if($utilizationEntry->fuel_order_id)
                                    #{{ $utilizationEntry->fuel_order_id }}
                                @else
                                    <span class="text-[#49454F] italic">SYSTEM DEFINED</span>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="remarks" :value="__('Additional Remarks')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <textarea id="remarks" name="remarks" rows="3" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] focus:border-[#D0BCFF] rounded-xl p-3 text-sm">{{ old('remarks', $utilizationEntry->remarks) }}</textarea>
                            <x-input-error :messages="$errors->get('remarks')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30 gap-x-4">
                        <a href="{{ route('assets.show', $utilizationEntry->asset_id) }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest mr-8 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
