<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Asset Data Report') }}
            </h2>
            <div class="flex items-center space-x-3 print:hidden">
                <button onclick="window.print()" class="inline-flex items-center px-6 py-2.5 bg-[#CCC2DC] border border-transparent rounded-full font-bold text-xs text-black uppercase tracking-widest hover:bg-[#E6E1E5] focus:outline-none focus:ring-2 focus:ring-[#CCC2DC] transition shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 print:max-w-full print:px-0">
            
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl print:bg-white print:border-none print:shadow-none print:rounded-none">
                
                <!-- Report Filter Form -->
                <div class="p-8 border-b border-[#49454F]/50 bg-[#2D2930] print:hidden">
                    <form action="{{ route('reports.asset-utilization') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="w-full md:w-1/3">
                            <label for="asset_id" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Asset</label>
                            <select name="asset_id" id="asset_id" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]" required>
                                <option value="">Select Asset...</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ $assetId == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->fleet_no }} ({{ $asset->assetType->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/4">
                            <label for="date_from" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]">
                        </div>
                        <div class="w-full md:w-1/4">
                            <label for="date_to" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]">
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-[#D0BCFF] border border-transparent rounded-full font-bold text-xs text-black uppercase tracking-widest hover:bg-[#EADDFF] transition ease-in-out duration-150 shadow-md">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="p-0 text-gray-100 print:text-black">
                    <div class="hidden print:block p-8 pb-4 text-center">
                        <h2 class="text-2xl font-black uppercase tracking-widest">Asset Data Report</h2>
                        @if($assetId)
                            <p class="text-sm font-bold mt-2">Asset: {{ $assets->firstWhere('id', $assetId)?->fleet_no }}</p>
                        @endif
                        @if($dateFrom || $dateTo)
                            <p class="text-sm font-bold">Date: {{ $dateFrom ?? 'Any' }} - {{ $dateTo ?? 'Any' }}</p>
                        @endif
                    </div>
                    
                    @if($assetId && $selectedAsset = $assets->firstWhere('id', $assetId))
                        <div class="p-8 border-b border-[#49454F]/50 bg-[#2D2930] print:bg-white print:border-gray-200">
                            <h3 class="text-[10px] font-bold text-[#D0BCFF] print:text-indigo-600 uppercase tracking-[0.3em] mb-6 flex items-center">
                                <span class="w-8 h-px bg-[#D0BCFF]/30 print:bg-indigo-300 mr-4"></span>
                                Technical Specifications
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-8 gap-x-8">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 bg-[#D0BCFF]/10 print:bg-indigo-50 p-2 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-[#D0BCFF] print:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Equipment Type</p>
                                        <p class="text-sm md:text-base font-bold text-[#E6E1E5] print:text-black">{{ $selectedAsset->assetType->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 bg-[#D0BCFF]/10 print:bg-indigo-50 p-2 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-[#D0BCFF] print:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Fleet / Plate No.</p>
                                        <p class="text-sm md:text-base font-bold text-[#E6E1E5] print:text-black">{{ $selectedAsset->fleet_no }} <span class="text-xs text-[#CAC4D0] print:text-gray-500 font-normal">({{ $selectedAsset->plate_no ?? 'N/A' }})</span></p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 bg-emerald-500/10 print:bg-emerald-50 p-2 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-emerald-400 print:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Fuel Type & Cap.</p>
                                        <p class="text-sm md:text-base font-bold text-emerald-400 print:text-emerald-700">{{ $selectedAsset->fuel_type ?? 'N/A' }} <span class="text-xs font-normal">({{ $selectedAsset->tank_capacity ? number_format($selectedAsset->tank_capacity, 2) . ' L' : 'N/A' }})</span></p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 bg-[#A8EFF2]/10 print:bg-cyan-50 p-2 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-[#A8EFF2] print:text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Factor (KM)</p>
                                        <p class="text-sm md:text-base font-mono font-bold text-[#A8EFF2] print:text-cyan-700">{{ number_format($selectedAsset->fuel_factor_km, 2) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 bg-[#A8EFF2]/10 print:bg-cyan-50 p-2 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-[#A8EFF2] print:text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Factor (HR)</p>
                                        <p class="text-sm md:text-base font-mono font-bold text-[#A8EFF2] print:text-cyan-700">{{ number_format($selectedAsset->fuel_factor_hr, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="min-w-full divide-y divide-[#49454F]/50 print:divide-black print:border-collapse print:border print:border-black">
                            <thead class="bg-[#49454F]/10 print:bg-gray-100 print:text-black">
                                <tr>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Date</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Particulars</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Account / Sub Account</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Calculation Type</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Calculated KM</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Calculated Hours</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Computed Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F] print:bg-white print:divide-black">
                                @php
                                    $grandTotalKm = 0;
                                    $grandTotalHours = 0;
                                    $grandTotalActual = 0;
                                @endphp
                                @forelse($entries as $fuelOrderId => $group)
                                    @php
                                        $fuelOrder = $group->first()->fuelOrder;
                                        $groupTotalKm = 0;
                                        $groupTotalHours = 0;
                                        $groupTotalQty = 0;
                                        $grandTotalActual += $fuelOrder->actual_quantity;
                                    @endphp
                                    <!-- Group Header -->
                                    <tr class="bg-[#49454F]/20 print:bg-gray-50 border-t-2 border-[#49454F]/50 print:border-black">
                                        <td colspan="7" class="px-6 py-4 print:px-2 print:py-2 print:border print:border-black">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <span class="text-sm font-black text-[#D0BCFF] print:text-indigo-700 font-mono">FUEL ORDER #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                    <span class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-widest">Released: {{ $fuelOrder->created_at->format('M d, Y') }}</span>
                                                </div>
                                                <div class="flex items-center space-x-6">
                                                    <div class="text-right">
                                                        <span class="text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-widest block">Approved (Say)</span>
                                                        <span class="text-sm font-black text-[#E6E1E5] print:text-black font-mono">{{ number_format($fuelOrder->say_quantity, 2) }} L</span>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-[10px] font-bold text-emerald-400/80 print:text-emerald-700 uppercase tracking-widest block">Actual Dispensed</span>
                                                        <span class="text-sm font-black text-emerald-400 print:text-emerald-800 font-mono">{{ number_format($fuelOrder->actual_quantity, 2) }} L</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($group as $entry)
                                        @php
                                            $qty = 0;
                                            $calcKm = 0;
                                            $calcHours = 0;
                                            $calcType = strtolower($entry->calculation_type ?? '');
                                            
                                            if (str_contains($calcType, 'kilometer')) {
                                                $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                                                $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
                                            } elseif (str_contains($calcType, 'actual')) {
                                                if ($entry->end_time && $entry->start_time) {
                                                    $start = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                                                    $end = \Carbon\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                                                    $calcHours = max(0, $start->diffInMinutes($end) / 60);
                                                    $qty = $calcHours * $entry->fuel_factor_hr;
                                                }
                                            } elseif (str_contains($calcType, 'hour')) {
                                                $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                                                $qty = $calcHours * $entry->fuel_factor_hr;
                                            }
                                            
                                            $groupTotalKm += $calcKm;
                                            $groupTotalHours += $calcHours;
                                            $groupTotalQty += $qty;
                                            $grandTotalKm += $calcKm;
                                            $grandTotalHours += $calcHours;
                                        @endphp
                                        <tr class="hover:bg-[#49454F]/10 transition-colors print:hover:bg-transparent">
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs text-[#E6E1E5] print:text-black">
                                                {{ $entry->date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] text-xs text-[#E6E1E5] print:text-black max-w-xs truncate" title="{{ $entry->particulars }}">
                                                {{ $entry->particulars ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs font-bold text-[#D0BCFF] print:text-indigo-700">
                                                {{ $entry->chargeableAccount->name ?? 'Unassigned' }} - {{ $entry->subAccount->name ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap">
                                                <span class="px-2 py-0.5 text-[9px] font-bold text-[#A8EFF2] print:text-indigo-600 print:bg-transparent print:border-none print:px-0 bg-[#A8EFF2]/10 border border-[#A8EFF2]/20 rounded-full uppercase tracking-widest">
                                                    {{ $entry->calculation_type ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono text-[#E6E1E5] print:text-black">
                                                {{ $calcKm > 0 ? number_format($calcKm, 2) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono text-[#E6E1E5] print:text-black">
                                                {{ $calcHours > 0 ? number_format($calcHours, 2) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#CAC4D0] print:text-gray-600">
                                                {{ number_format($qty, 2) }} L
                                            </td>
                                        </tr>
                                    @endforeach
                                    <!-- Group Footer -->
                                    <tr class="bg-[#1C1B1F] print:bg-white">
                                        <td colspan="4" class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black text-right text-[10px] font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-widest">
                                            Sub-Total (Order #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}):
                                        </td>
                                        <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black text-right text-xs font-mono font-bold text-[#A8EFF2] print:text-indigo-600 border-t border-[#49454F]/50 print:border-black">
                                            {{ $groupTotalKm > 0 ? number_format($groupTotalKm, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black text-right text-xs font-mono font-bold text-[#A8EFF2] print:text-indigo-600 border-t border-[#49454F]/50 print:border-black">
                                            {{ $groupTotalHours > 0 ? number_format($groupTotalHours, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black text-right text-sm font-black text-emerald-400 print:text-black border-t border-[#49454F]/50 print:border-black">
                                            {{ number_format($groupTotalQty, 2) }} L
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center print:hidden">
                                                <div class="bg-[#49454F]/20 w-16 h-16 rounded-2xl flex items-center justify-center mb-4">
                                                    <svg class="w-8 h-8 text-[#CAC4D0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="text-sm font-bold text-[#E6E1E5]">No report data to display.</p>
                                                <p class="text-xs text-[#CAC4D0] mt-1">Please select an asset and date range to generate the report.</p>
                                            </div>
                                            <div class="hidden print:block text-black">
                                                No records found for the selected parameters.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if($entries->count() > 0)
                                    <!-- Grand Total Row -->
                                    <tr class="bg-[#D0BCFF]/10 print:bg-gray-100 border-t-2 border-[#D0BCFF]/30 print:border-gray-800">
                                        <td colspan="4" class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-black text-[#E6E1E5] print:text-black uppercase tracking-widest">
                                            Grand Total (Actual Dispensed):
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#A8EFF2] print:text-indigo-700">
                                            {{ $grandTotalKm > 0 ? number_format($grandTotalKm, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#A8EFF2] print:text-indigo-700">
                                            {{ $grandTotalHours > 0 ? number_format($grandTotalHours, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-base font-mono font-black text-emerald-400 print:text-emerald-800" title="Total Actual Quantity Dispensed">
                                            {{ number_format($grandTotalActual, 2) }} L
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if($entries->count() > 0)
                        <!-- Card Footer / Final Summary -->
                        <div class="p-8 border-t border-[#49454F]/50 bg-[#2D2930] print:bg-white print:border-gray-200 mt-6 lg:rounded-b-[28px] print:rounded-none">
                            <h3 class="text-[10px] font-bold text-emerald-400 print:text-emerald-700 uppercase tracking-[0.3em] mb-6 flex items-center">
                                <span class="w-8 h-px bg-emerald-400/30 print:bg-emerald-300 mr-4"></span>
                                Performance Metrics (Actualized)
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                <div class="bg-[#1C1B1F] print:bg-gray-50 border border-[#49454F]/50 print:border-gray-200 rounded-2xl p-6">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Actual KM Factor</p>
                                            <p class="text-sm text-[#CAC4D0]/70 print:text-gray-400 mb-3">(Total Dispensed / Total Calc. KM)</p>
                                            <p class="text-3xl font-black text-emerald-400 print:text-emerald-700 font-mono">
                                                @if($grandTotalKm > 0)
                                                    {{ number_format($grandTotalActual / $grandTotalKm, 4) }}
                                                @else
                                                    -
                                                @endif
                                            </p>
                                            @if($grandTotalKm > 0 && $selectedAsset->fuel_factor_km > 0)
                                                @php
                                                    $targetKmFactor = $selectedAsset->fuel_factor_km > 0 ? 1 / $selectedAsset->fuel_factor_km : 0;
                                                    $actualKmFactor = $grandTotalKm > 0 ? $grandTotalActual / $grandTotalKm : 0;
                                                    $kmVariance = $targetKmFactor > 0 ? (($actualKmFactor - $targetKmFactor) / $targetKmFactor) * 100 : 0;
                                                @endphp
                                                <div class="mt-2 flex items-center {{ $kmVariance > 0 ? 'text-rose-400' : 'text-sky-400' }}">
                                                    @if($kmVariance > 0)
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                                    @else
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
                                                    @endif
                                                    <span class="text-xs font-bold mr-1">{{ $kmVariance > 0 ? '+' : '' }}{{ number_format($kmVariance, 2) }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="bg-emerald-400/10 print:bg-emerald-100 p-3 rounded-xl shrink-0">
                                            <svg class="w-6 h-6 text-emerald-400 print:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-[#1C1B1F] print:bg-gray-50 border border-[#49454F]/50 print:border-gray-200 rounded-2xl p-6">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-[#CAC4D0] print:text-gray-500 uppercase tracking-[0.2em] mb-1">Actual Hour Factor</p>
                                            <p class="text-sm text-[#CAC4D0]/70 print:text-gray-400 mb-3">(Total Dispensed / Total Calc. Hours)</p>
                                            <p class="text-3xl font-black text-emerald-400 print:text-emerald-700 font-mono">
                                                @if($grandTotalHours > 0)
                                                    {{ number_format($grandTotalActual / $grandTotalHours, 4) }}
                                                @else
                                                    -
                                                @endif
                                            </p>
                                            @if($grandTotalHours > 0 && $selectedAsset->fuel_factor_hr > 0)
                                                @php
                                                    $actualHrFactor = $grandTotalActual / $grandTotalHours;
                                                    $hrVariance = (($actualHrFactor - $selectedAsset->fuel_factor_hr) / $selectedAsset->fuel_factor_hr) * 100;
                                                @endphp
                                                <div class="mt-2 flex items-center {{ $hrVariance > 0 ? 'text-rose-400' : 'text-sky-400' }}">
                                                    @if($hrVariance > 0)
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                                    @else
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
                                                    @endif
                                                    <span class="text-xs font-bold mr-1">{{ $hrVariance > 0 ? '+' : '' }}{{ number_format($hrVariance, 2) }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="bg-emerald-400/10 print:bg-emerald-100 p-3 rounded-xl shrink-0">
                                            <svg class="w-6 h-6 text-emerald-400 print:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>