<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Update Asset') }}: {{ $asset->fleet_no }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <form method="POST" action="{{ route('assets.update', $asset) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <x-input-label for="fleet_no" :value="__('Fleet Number')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="fleet_no" name="fleet_no" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('fleet_no', $asset->fleet_no)" required autofocus />
                            <x-input-error :messages="$errors->get('fleet_no')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="asset_type_id" :value="__('Equipment Category')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="asset_type_id" name="asset_type_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] focus:border-[#D0BCFF] rounded-xl shadow-sm p-3 text-sm" required>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('asset_type_id', $asset->asset_type_id) == $type->id ? 'selected' : '' }} class="bg-[#2D2930]">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('asset_type_id')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="plate_no" :value="__('Plate Number')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="plate_no" name="plate_no" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('plate_no', $asset->plate_no)" />
                            <x-input-error :messages="$errors->get('plate_no')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="fuel_type" :value="__('Fuel Type')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="fuel_type" name="fuel_type" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] focus:border-[#D0BCFF] rounded-xl shadow-sm p-3 text-sm" required>
                                <option value="Diesel" {{ old('fuel_type', $asset->fuel_type) == 'Diesel' ? 'selected' : '' }} class="bg-[#2D2930]">Diesel</option>
                                <option value="Gasoline" {{ old('fuel_type', $asset->fuel_type) == 'Gasoline' ? 'selected' : '' }} class="bg-[#2D2930]">Gasoline</option>
                            </select>
                            <x-input-error :messages="$errors->get('fuel_type')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="tank_capacity" :value="__('Tank Capacity (Liters)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="tank_capacity" name="tank_capacity" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('tank_capacity', $asset->tank_capacity)" required />
                            <x-input-error :messages="$errors->get('tank_capacity')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="last_kilometer_reading" :value="__('Last Odo (KM)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="last_kilometer_reading" name="last_kilometer_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('last_kilometer_reading', $asset->last_kilometer_reading)" required />
                            <x-input-error :messages="$errors->get('last_kilometer_reading')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="last_engine_hours" :value="__('Last Engine (HR)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="last_engine_hours" name="last_engine_hours" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('last_engine_hours', $asset->last_engine_hours)" required />
                            <x-input-error :messages="$errors->get('last_engine_hours')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="last_time" :value="__('Last Time')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="last_time" name="last_time" type="time" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('last_time', $asset->last_time)" />
                            <x-input-error :messages="$errors->get('last_time')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div>
                            <x-input-label for="last_date" :value="__('Last Date')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <x-text-input id="last_date" name="last_date" type="date" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('last_date', $asset->last_date)" />
                            <x-input-error :messages="$errors->get('last_date')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>

                        <div class="pt-4 border-t border-[#49454F]/30 md:col-span-2">
                            <h4 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-6">Consumption Factors</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <x-input-label for="fuel_factor_km" :value="__('Kilometer / Liter')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                    <x-text-input id="fuel_factor_km" name="fuel_factor_km" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('fuel_factor_km', $asset->fuel_factor_km)" />
                                    <x-input-error :messages="$errors->get('fuel_factor_km')" class="mt-2 text-rose-400 text-xs font-bold" />
                                </div>

                                <div>
                                    <x-input-label for="fuel_factor_hr" :value="__('Liter / Hour')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                    <x-text-input id="fuel_factor_hr" name="fuel_factor_hr" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('fuel_factor_hr', $asset->fuel_factor_hr)" />
                                    <x-input-error :messages="$errors->get('fuel_factor_hr')" class="mt-2 text-rose-400 text-xs font-bold" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30 gap-x-4">
                        <a href="{{ route('assets.index') }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest mr-8 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('Update Asset') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
