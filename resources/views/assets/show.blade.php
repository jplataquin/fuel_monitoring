<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest text-[#CAC4D0]">
                        <li class="inline-flex items-center">
                            <a href="{{ route('assets.index') }}" class="hover:text-[#D0BCFF]">Fleet</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-[#49454F]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 md:ml-2 text-[#D0BCFF]">{{ $asset->fleet_no }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="text-3xl md:text-4xl font-bold text-[#E6E1E5] tracking-tight">
                    {{ $asset->fleet_no }}
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <x-button-link :href="route('assets.edit', $asset)" color="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        {{ __('Edit Asset') }}
                    </x-button-link>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-12">
            <!-- Asset Specs Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2 bg-[#2D2930] rounded-[28px] p-10 border border-[#49454F]/50 shadow-xl">
                    <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-10 flex items-center">
                        <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                        Technical Specifications
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-12 gap-x-12">
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Equipment Type</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ $asset->assetType->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Plate Number</p>
                                <p class="text-lg font-bold text-[#E6E1E5] font-mono">{{ $asset->plate_no ?? 'UNASSIGNED' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Tank Capacity</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ number_format($asset->tank_capacity, 2) }} <span class="text-[10px] text-[#CAC4D0] font-medium ml-1">LITERS</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Fuel Type</p>
                                <p class="text-lg font-bold text-[#E6E1E5] uppercase">{{ $asset->fuel_type ?? 'Diesel' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Factor (KM)</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ $asset->fuel_factor_km ?? '0.00' }} <span class="text-[10px] text-[#CAC4D0] font-medium ml-1">KM/L</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Factor (HR)</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ $asset->fuel_factor_hr ?? '0.00' }} <span class="text-[10px] text-[#CAC4D0] font-medium ml-1">L/HR</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.487V4.513a2 2 0 011.553-1.943L9 1.5l5.447 2.724A2 2 0 0116 6.164v10.973a2 2 0 01-1.553 1.943L9 21.5z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Last Odometer</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ number_format($asset->last_kilometer_reading, 2) }} <span class="text-[10px] text-[#CAC4D0] font-medium ml-1">KM</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Last Engine Hours</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ number_format($asset->last_engine_hours, 2) }} <span class="text-[10px] text-[#CAC4D0] font-medium ml-1">HRS</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Last Time</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ $asset->last_time ? date('H:i', strtotime($asset->last_time)) : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="mt-1 bg-[#D0BCFF]/10 p-2 rounded-lg shrink-0">
                                <svg class="w-5 h-5 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Last Date</p>
                                <p class="text-lg font-bold text-[#E6E1E5]">{{ $asset->last_date ? date('M d, Y', strtotime($asset->last_date)) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-[#D0BCFF] rounded-[28px] p-10 flex flex-col justify-center text-center shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold text-[#381E72] uppercase tracking-[0.3em] mb-4">Lifecycle Stats</p>
                        <div class="space-y-2">
                            <p class="text-6xl font-bold text-[#1C1B1F] tracking-tighter" id="total-logs">0</p>
                            <p class="text-xs font-bold text-[#381E72] uppercase tracking-widest">Total Logs</p>
                        </div>
                    </div>
                    <!-- Decorative background element -->
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-[#1C1B1F]/10 rounded-full blur-2xl"></div>
                </div>
            </div>

            <!-- Add Utilization Entry Form -->
            <div class="bg-[#1C1B1F] rounded-[28px] p-10 border-2 border-[#D0BCFF]/30 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
                
                <h3 class="text-xl font-bold text-[#E6E1E5] mb-8 flex items-center tracking-tight">
                    <span class="bg-[#D0BCFF] !p-2 rounded-lg !mr-4">
                        <svg class="w-5 h-5 text-[#381E72]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </span>
                    Register Utilization
                </h3>
                
                <form id="utilization-form" class="space-y-8 relative z-10">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-x-8 gap-y-8">
                        
                        <div class="space-y-2 md:col-span-2">
                            <x-input-label for="reference" :value="__('Reference')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="reference" name="reference" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required placeholder="e.g. REF-001" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-reference"></p>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <x-input-label for="driver_operator_name" :value="__('Personnel In-Charge')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="driver_operator_name" name="driver_operator_name" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required placeholder="Driver or Operator" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-driver_operator_name"></p>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <x-input-label for="date" :value="__('Date')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="date" name="date" type="date" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" :value="date('Y-m-d')" required />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-date"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="start_time" :value="__('Start Time')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="start_time" name="start_time" type="time" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" :value="date('H:i')" required />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-start_time"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="end_time" :value="__('End Time')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="end_time" name="end_time" type="time" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" :value="date('H:i', strtotime('+1 hour'))" required />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-end_time"></p>
                        </div>
                        
                        <div class="space-y-2 md:col-span-1 lg:col-span-2">
                            <x-input-label for="chargeable_account_id" :value="__('Charged To')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <select id="chargeable_account_id" name="chargeable_account_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required onchange="fetchSubAccounts(this.value)">
                                <option value="">-- Select Account --</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-chargeable_account_id"></p>
                        </div>
                        <div class="space-y-2 md:col-span-1 lg:col-span-2">
                            <x-input-label for="sub_account_id" :value="__('Sub Account')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <select id="sub_account_id" name="sub_account_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required disabled>
                                <option value="">-- Select Sub Account --</option>
                            </select>
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-sub_account_id"></p>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <x-input-label for="calculation_type" :value="__('Calculation Type')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <select id="calculation_type" name="calculation_type" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required>
                                <option value="">-- Select Calculation Type --</option>
                                <option value="Kilometer Reading">Kilometer Reading</option>
                                <option value="Hour Reading">Hour Reading</option>
                                <option value="Actual Operation Hours">Actual Operation Hours</option>
                            </select>
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-calculation_type"></p>
                        </div>
                        <div class="space-y-2 flex flex-col justify-end pb-3 md:col-span-2">
                             <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="unbudgeted" value="1" class="w-5 h-5 !rounded !border-2 !border-[#D0BCFF] !bg-[#2D2930] text-[#D0BCFF] focus:ring-[#D0BCFF] transition-colors group-hover:border-[#EADDFF]">
                                <span class="!ml-3 text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] group-hover:text-[#E6E1E5] transition-colors">{{ __('Unbudgeted') }}</span>
                            </label>
                        </div>
                        <div class="space-y-2 md:col-span-4">
                            <x-input-label for="particulars" :value="__('Particulars / Mission')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="particulars" name="particulars" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3" required placeholder="Describe the activity..." />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-particulars"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="start_kilometer_reading" :value="__('Start Odo (KM)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="start_kilometer_reading" name="start_kilometer_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3 font-mono" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-start_kilometer_reading"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="end_kilometer_reading" :value="__('End Odo (KM)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="end_kilometer_reading" name="end_kilometer_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3 font-mono" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-end_kilometer_reading"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="start_hour_reading" :value="__('Start Engine (HR)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="start_hour_reading" name="start_hour_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3 font-mono" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-start_hour_reading"></p>
                        </div>
                        <div class="space-y-2">
                            <x-input-label for="end_hour_reading" :value="__('End Engine (HR)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <x-text-input id="end_hour_reading" name="end_hour_reading" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl text-sm p-3 font-mono" value="0" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" />
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-end_hour_reading"></p>
                        </div>
                        <div class="space-y-2 md:col-span-4">
                            <x-input-label for="remarks" :value="__('Remarks')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1" />
                            <textarea id="remarks" name="remarks" rows="2" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] focus:border-[#D0BCFF] rounded-xl p-3 text-sm" placeholder="Any additional notes..."></textarea>
                            <p class="text-rose-400 text-[10px] font-bold mt-1 hidden" id="error-remarks"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-6 border-t border-[#49454F]/30">
                        <button type="submit" id="submit-btn" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20 disabled:opacity-50">
                            <span id="btn-text">Submit Entry</span>
                            <div id="btn-spinner" class="ml-3 hidden w-4 h-4 border-2 border-[#381E72]/20 border-t-[#381E72] rounded-full animate-spin"></div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Utilization Logs -->
            <div class="bg-[#1C1B1F] rounded-[28px] border border-[#49454F]/50 overflow-hidden shadow-xl mt-12">
                <div class="px-8 py-6 border-b border-[#49454F]/50 flex flex-col md:flex-row md:items-center justify-between bg-[#49454F]/10 gap-4">
                    <h3 class="text-lg font-bold text-[#E6E1E5] tracking-tight whitespace-nowrap">{{ __('Utilization Logs') }}</h3>
                    
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em]">From</span>
                            <x-text-input id="filter_start_date" type="date" class="bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-lg text-xs p-2 h-9 w-full sm:w-auto" />
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em]">To</span>
                            <x-text-input id="filter_end_date" type="date" class="bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-lg text-xs p-2 h-9 w-full sm:w-auto" />
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em]">Account</span>
                            <select id="filter_chargeable_account_id" class="bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-lg text-xs p-2 h-9 w-full sm:w-auto">
                                <option value="">All</option>
                                @foreach($chargeableAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em]">Order ID</span>
                            <x-text-input id="filter_fuel_order_id" type="number" class="bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-lg text-xs p-2 h-9 w-full sm:w-24" placeholder="ID" />
                        </div>
                        <button onclick="applyFilter()" class="w-full sm:w-auto px-4 h-9 bg-[#D0BCFF] text-[#381E72] rounded-lg font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none transition">
                            Filter
                        </button>
                        <button onclick="printFilteredLogs()" class="w-full sm:w-auto px-4 h-9 bg-[#49454F]/50 text-[#E6E1E5] border border-[#49454F] rounded-lg font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-[#49454F] focus:outline-none transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Print
                        </button>
                    </div>
                </div>
                
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead>
                            <tr class="bg-[#49454F]/5">
                                <th class="px-8 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Date & Time</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Particulars</th>
                                <th class="px-8 py-5 text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Unbudgeted</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Charged To</th>
                                <th class="px-8 py-5 text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">KM</th>
                                <th class="px-8 py-5 text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">HRS</th>
                                <th class="px-8 py-5 text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] w-32">Order ID</th>
                            </tr>
                        </thead>
                        <tbody id="logs-body" class="divide-y divide-[#49454F]/30 bg-[#1C1B1F]">
                            <!-- JS Loaded -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div id="logs-body-mobile" class="md:hidden divide-y divide-[#49454F]/30">
                    <!-- JS Loaded -->
                </div>

                <div id="loading" class="p-12 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-[#D0BCFF]/20 border-t-[#D0BCFF] rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm font-bold text-[#CAC4D0] uppercase tracking-widest">Syncing Data...</p>
                </div>

                <div id="no-more-logs" class="p-8 text-center hidden bg-[#49454F]/10">
                    <p class="text-xs font-bold text-[#CAC4D0] uppercase tracking-widest">End of History</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let page = 1;
        let loading = false;
        let hasMore = true;

        async function fetchSubAccounts(accountId) {
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
                    subAccountSelect.appendChild(option);
                });
                
                subAccountSelect.disabled = false;
            } catch (error) {
                console.error('Error fetching sub-accounts:', error);
                subAccountSelect.innerHTML = '<option value="">Error loading sub-accounts</option>';
            }
        }

        const form = document.getElementById('utilization-form');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        form.onsubmit = async (e) => {
            e.preventDefault();
            
            // Reset errors
            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.innerText = '';
                el.classList.add('hidden');
            });

            // Loading state
            submitBtn.disabled = true;
            btnText.innerText = 'Saving...';
            btnSpinner.classList.remove('hidden');

            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route('utilization-entries.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Save values to persist
                    const dateVal = document.getElementById('date').value;
                    const startTimeVal = document.getElementById('start_time').value;
                    const endTimeVal = document.getElementById('end_time').value;
                    const driverVal = document.getElementById('driver_operator_name').value;
                    const accountVal = document.getElementById('chargeable_account_id').value;
                    const subAccountVal = document.getElementById('sub_account_id').value;
                    const referenceVal = document.getElementById('reference').value;
                    const calcVal = document.getElementById('calculation_type').value;

                    // Reset form
                    form.reset();

                    // Restore persisted values
                    document.getElementById('date').value = dateVal;
                    document.getElementById('start_time').value = startTimeVal;
                    document.getElementById('end_time').value = endTimeVal;
                    document.getElementById('driver_operator_name').value = driverVal;
                    document.getElementById('chargeable_account_id').value = accountVal;
                    
                    // For sub-account, we need to re-populate if it was cleared by reset
                    if (accountVal) {
                        await fetchSubAccounts(accountVal);
                        document.getElementById('sub_account_id').value = subAccountVal;
                    }

                    document.getElementById('reference').value = referenceVal;
                    document.getElementById('calculation_type').value = calcVal;

                    // Reload table (easiest way to maintain sort and pagination consistency)
                    // Or just prepend the new entry
                    page = 1;
                    hasMore = true;
                    document.getElementById('logs-body').innerHTML = '';
                    document.getElementById('logs-body-mobile').innerHTML = '';
                    loadLogs();
                    
                    // Success feedback (simple alert for now)
                    alert(data.message);
                } else {
                    // Show errors
                    if (data.errors) {
                        for (const [key, messages] of Object.entries(data.errors)) {
                            const errorEl = document.getElementById(`error-${key}`);
                            if (errorEl) {
                                errorEl.innerText = messages[0];
                                errorEl.classList.remove('hidden');
                            }
                        }
                    } else {
                        alert('An unexpected error occurred.');
                    }
                }
            } catch (error) {
                console.error('Error saving entry:', error);
                alert('Network error. Please try again.');
            } finally {
                submitBtn.disabled = false;
                btnText.innerText = 'Save Entry';
                btnSpinner.classList.add('hidden');
            }
        };

        async function loadLogs() {
            if (loading || !hasMore) return;
            loading = true;
            document.getElementById('loading').classList.remove('hidden');
            
            const startDate = document.getElementById('filter_start_date').value;
            const endDate = document.getElementById('filter_end_date').value;
            const accountId = document.getElementById('filter_chargeable_account_id').value;
            const orderId = document.getElementById('filter_fuel_order_id').value;

            try {
                let url = `{{ route('assets.logs', $asset) }}?page=${page}`;
                if (startDate) url += `&start_date=${startDate}`;
                if (endDate) url += `&end_date=${endDate}`;
                if (accountId) url += `&chargeable_account_id=${accountId}`;
                if (orderId) url += `&fuel_order_id=${orderId}`;

                const response = await fetch(url);
                const data = await response.json();

                if (page === 1) {
                    document.getElementById('total-logs').innerText = data.total;
                }

                if (data.data.length === 0) {
                    hasMore = false;
                    if (page === 1) {
                        const emptyHtml = '<div class="p-16 text-center text-[#CAC4D0] font-bold uppercase tracking-widest text-xs">No activity found</div>';
                        document.getElementById('logs-body').innerHTML = `<tr><td colspan="6">${emptyHtml}</td></tr>`;
                        document.getElementById('logs-body-mobile').innerHTML = emptyHtml;
                    } else {
                        document.getElementById('no-more-logs').classList.remove('hidden');
                    }
                } else {
                    const body = document.getElementById('logs-body');
                    const bodyMobile = document.getElementById('logs-body-mobile');
                    
                    data.data.forEach(entry => {
                        const dateObj = new Date(entry.date);
                        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        
                        let operationHoursHtml = '';
                        if (entry.start_time && entry.end_time) {
                            const startParts = entry.start_time.split(':');
                            const endParts = entry.end_time.split(':');
                            if (startParts.length >= 2 && endParts.length >= 2) {
                                const start = new Date();
                                start.setHours(parseInt(startParts[0], 10), parseInt(startParts[1], 10), 0, 0);
                                const end = new Date();
                                end.setHours(parseInt(endParts[0], 10), parseInt(endParts[1], 10), 0, 0);
                                
                                if (end < start) {
                                    end.setDate(end.getDate() + 1);
                                }
                                
                                const diffHrs = (end - start) / (1000 * 60 * 60);
                                operationHoursHtml = `<div class="mt-1 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-[#49454F] text-[#CAC4D0] uppercase tracking-widest">${diffHrs.toFixed(2)} hrs</div>`;
                            }
                        }
                        
                        // Desktop Row
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-[#49454F]/20 transition-colors cursor-pointer group';
                        row.onclick = () => window.location.href = `/utilization-entries/${entry.id}`;
                        row.innerHTML = `
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-[#E6E1E5]">${formattedDate}</div>
                                <div class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-widest">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                ${operationHoursHtml}
                            </td>
                            
                            <td class="px-8 py-5 text-sm font-medium text-[#CAC4D0] group-hover:text-white transition-colors">${entry.particulars}</td>
                            
                            <td class="px-8 py-5 text-center text-sm font-medium">
                                ${entry.unbudgeted ? '<span class="px-2 py-1 bg-rose-500/10 text-rose-400 rounded-lg text-[9px] font-black uppercase tracking-widest border border-rose-500/20">Yes</span>' : '<span class="text-[#49454F] font-bold">—</span>'}
                            </td>

                            <td class="px-8 py-5 text-sm font-medium text-[#CAC4D0]">
                                ${entry.chargeable_account ? entry.chargeable_account.name : '—'}
                                <span class="mx-1 opacity-50">-</span>
                                ${entry.sub_account ? entry.sub_account.name : '—'}
                            </td>
                            <td class="px-8 py-5 text-center font-mono text-sm font-black text-white">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</td>
                            <td class="px-8 py-5 text-center font-mono text-sm font-black text-white">${parseFloat(entry.start_hour_reading).toLocaleString()} - ${parseFloat(entry.end_hour_reading).toLocaleString()}</td>
                            <td class="px-8 py-5 text-center">
                                ${entry.fuel_order_id ? `<span class="px-2 py-0.5 bg-[#D0BCFF]/10 text-[#D0BCFF] border border-[#D0BCFF]/20 rounded text-[10px] font-black">#${entry.fuel_order_id}</span>` : '—'}
                            </td>
                        `;
                        body.appendChild(row);

                        // Mobile Card
                        const card = document.createElement('div');
                        card.className = 'p-6 active:bg-[#49454F]/20 transition-colors';
                        card.onclick = () => window.location.href = `/utilization-entries/${entry.id}`;
                        card.innerHTML = `
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="text-sm font-black text-[#E6E1E5]">${formattedDate}</div>
                                    <div class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-widest">${entry.start_time || '—'} - ${entry.end_time || '—'}</div>
                                    ${operationHoursHtml}
                                </div>
                                ${entry.fuel_order_id ? `<span class="px-2 py-0.5 bg-[#D0BCFF]/10 text-[#D0BCFF] border border-[#D0BCFF]/20 rounded text-[10px] font-black">#${entry.fuel_order_id}</span>` : ''}
                            </div>
                            <p class="text-sm text-[#CAC4D0] mb-2">${entry.particulars} ${entry.unbudgeted ? '<span class="ml-2 px-2 py-0.5 bg-rose-500/10 text-rose-400 rounded-lg text-[9px] font-black uppercase tracking-widest border border-rose-500/20">Unbudgeted</span>' : ''}</p>
                            <p class="text-[10px] font-bold text-[#D0BCFF] mb-4 tracking-widest uppercase">
                                <span class="text-[#CAC4D0]">ACCOUNT:</span> 
                                ${entry.chargeable_account ? entry.chargeable_account.name : '—'} 
                                - 
                                ${entry.sub_account ? entry.sub_account.name : '—'}
                            </p>
                            <div class="flex justify-between border-t border-[#49454F]/30 pt-4">
                                <div>
                                    <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-widest">Kilometers</p>
                                    <p class="text-lg font-black text-white">${parseFloat(entry.start_kilometer_reading).toLocaleString()} - ${parseFloat(entry.end_kilometer_reading).toLocaleString()}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-widest">Hours</p>
                                    <p class="text-lg font-black text-white">${parseFloat(entry.start_hour_reading).toLocaleString()} - ${parseFloat(entry.end_hour_reading).toLocaleString()}</p>
                                </div>
                            </div>
                        `;
                        bodyMobile.appendChild(card);
                    });
                    page++;
                    if (!data.next_page_url) {
                        hasMore = false;
                        document.getElementById('no-more-logs').classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Error Syncing:', error);
            } finally {
                loading = false;
                document.getElementById('loading').classList.add('hidden');
            }
        }

        function applyFilter() {
            page = 1;
            hasMore = true;
            document.getElementById('logs-body').innerHTML = '';
            document.getElementById('logs-body-mobile').innerHTML = '';
            document.getElementById('no-more-logs').classList.add('hidden');
            loadLogs();
        }

        function printFilteredLogs() {
            const startDate = document.getElementById('filter_start_date').value;
            const endDate = document.getElementById('filter_end_date').value;
            const accountId = document.getElementById('filter_chargeable_account_id').value;
            const orderId = document.getElementById('filter_fuel_order_id').value;

            let url = `{{ route('assets.logs.print', $asset) }}?`;
            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (accountId) params.append('chargeable_account_id', accountId);
            if (orderId) params.append('fuel_order_id', orderId);

            window.open(url + params.toString(), '_blank');
        }

        window.onscroll = function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                loadLogs();
            }
        };

        loadLogs();
    </script>
</x-app-layout>
