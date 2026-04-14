<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4 gap-x-4">
                <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                    {{ __('Fuel Order #') }}{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}
                </h2>
                <a href="{{ route('fuel-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-[#49454F] border border-transparent rounded-full font-bold text-xs text-[#E6E1E5] uppercase tracking-widest hover:bg-[#CAC4D0] hover:text-[#1C1B1F] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg print:hidden">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Orders
                </a>
            </div>
            <div class="flex items-center space-x-3 gap-4 print:hidden">
                @if(Auth::user()->role === 'administrator' && $fuelOrder->status !== 'VOID')
                    <form action="{{ route('fuel-orders.void', $fuelOrder) }}" method="POST" onsubmit="return confirm('Are you sure you want to void this fuel order? This will release all associated utilization entries and mark this order as VOID.')" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Void Order
                        </button>
                    </form>
                @endif
                @if(Auth::user()->role === 'administrator')
                    <x-button-link :href="route('fuel-orders.edit', $fuelOrder)" color="primary" class="shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Order
                    </x-button-link>
                @endif
                @if(in_array(Auth::user()->role, ['fuel_man', 'administrator', 'data_logger', 'data logger']) && $fuelOrder->status === 'PEND')
                    <x-button-link :href="route('fuel-orders.actualize', $fuelOrder)" color="info" class="shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Actualize
                    </x-button-link>
                @endif
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="bg-white rounded-[28px] print:rounded-none shadow-2xl print:shadow-none border border-gray-100 print:border-none p-10 md:p-14">
                
                <!-- Print Header -->
                <div class="text-center mb-10 pb-10 border-b-2 border-gray-200">
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Fuel Order Form</h1>
                    <p class="text-gray-500 mt-2 font-medium">Issue Date: {{ $fuelOrder->created_at->format('F d, Y') }}</p>
                    <p class="text-gray-500 font-medium">Order Number: #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-8 mb-12">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Asset Details</h4>
                        <p class="text-xl font-bold text-gray-900">{{ $fuelOrder->asset->fleet_no }}</p>
                        <p class="text-sm text-gray-600">{{ $fuelOrder->asset->assetType->name ?? 'N/A' }} | {{ $fuelOrder->asset->plate_no ?? 'No Plate' }}</p>
                    </div>
                    <div class="flex space-x-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Date Range</h4>
                            <p class="text-lg font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($fuelOrder->date_from)->format('M d, Y') }} 
                                - 
                                {{ \Carbon\Carbon::parse($fuelOrder->date_to)->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-8 mb-12">
                    <div class="flex space-x-8 gap-4">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</h4>
                              <p class="text-lg font-bold px-4 py-1 rounded-full
                                    {{ $fuelOrder->status === 'DONE' ? 'bg-emerald-600/10 text-emerald-600' :
                                    ($fuelOrder->status === 'VOID' ? 'bg-red-600/10 text-red-600' :
                                    'bg-amber-600/10 text-amber-600') }}">
                                    {{ $fuelOrder->status }}
                              </p>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">KM Factor</h4>
                            <p class="text-lg font-bold text-indigo-600">{{ number_format($fuelOrder->fuel_factor_km, 2) }} KM/L</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">HR Factor</h4>
                            <p class="text-lg font-bold text-indigo-600">{{ number_format($fuelOrder->fuel_factor_hr, 2) }} L/HR</p>
                        </div>
                    </div>
                </div>

                @php
                    $groupedTotals = [];
                    foreach ($fuelOrder->utilizationEntries as $entry) {
                        $accountName = $entry->chargeableAccount->name ?? 'Unassigned';
                        if ($entry->subAccount) {
                            $accountName .= ' - ' . $entry->subAccount->name;
                        }
                        if (!isset($groupedTotals[$accountName])) {
                            $groupedTotals[$accountName] = ['km' => 0, 'hr' => 0, 'qty' => 0];
                        }
                        
                        $calcType = strtolower($entry->calculation_type ?? '');
                        if (str_contains($calcType, 'kilometer')) {
                            $diff = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                            $groupedTotals[$accountName]['km'] += $diff;
                            $groupedTotals[$accountName]['qty'] += $fuelOrder->fuel_factor_km > 0 ? $diff / $fuelOrder->fuel_factor_km : 0;
                        } elseif (str_contains($calcType, 'actual')) {
                            if ($entry->end_time && $entry->start_time) {
                                $start = \Illuminate\Support\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                                $end = \Illuminate\Support\Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                                $diffInHours = max(0, $start->diffInMinutes($end) / 60);
                                $groupedTotals[$accountName]['hr'] += $diffInHours;
                                $groupedTotals[$accountName]['qty'] += $diffInHours * $fuelOrder->fuel_factor_hr;
                            }
                        } elseif (str_contains($calcType, 'hour')) {
                            $diff = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                            $groupedTotals[$accountName]['hr'] += $diff;
                            $groupedTotals[$accountName]['qty'] += $diff * $fuelOrder->fuel_factor_hr;
                        }
                    }
                @endphp

                @if(count($groupedTotals) > 0)
                    <div class="mb-12">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Breakdown by Charged To</h4>
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Account</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total KM</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total Hours</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Fuel (L)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($groupedTotals as $account => $totals)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-bold">{{ $account }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right font-mono">{{ number_format($totals['km'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right font-mono">{{ number_format($totals['hr'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-indigo-600 text-right font-mono font-bold">{{ number_format($totals['qty'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-3 gap-6 mb-12">
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Calculated KM</h4>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($fuelOrder->calculated_kilometers, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Calculated Hours</h4>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($fuelOrder->calculated_hours, 2) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2">Calculated Fuel (Liters)</h4>
                        <p class="text-2xl font-black text-indigo-700">{{ number_format($fuelOrder->calculated_quantity, 2) }}</p>
                    </div>
                </div>

                <div class="bg-gray-900 rounded-2xl p-8 mb-12 print:bg-white print:border print:border-gray-300">
                    <div class="flex justify-between items-center {{ $fuelOrder->status === 'DONE' ? 'mb-6 pb-6 border-b border-gray-800 print:border-gray-200' : '' }}">
                        <span class="text-gray-300 font-bold text-lg print:text-gray-900">Say Fuel Quantity:</span>
                        <span class="text-4xl font-black text-indigo-300 print:text-black">{{ number_format($fuelOrder->say_quantity, 2) }} L</span>
                    </div>
                    @if($fuelOrder->status === 'DONE')
                        <div class="flex justify-between items-center">
                            <span class="text-gray-300 font-bold text-lg print:text-gray-900">Actual Quantity:</span>
                            <span class="text-4xl font-black text-emerald-400 print:text-black">{{ number_format($fuelOrder->actual_quantity, 2) }} L</span>
                        </div>
                    @endif
                </div>

                @if($fuelOrder->status === 'PEND')
                <div class="grid grid-cols-2 gap-12 pt-12 border-t-2 border-gray-200 mt-16 print:mt-32">
                    <div class="text-center">
                        <div class="border-b border-gray-400 pb-2 mb-2 px-8 min-h-[3rem] flex items-end justify-center">
                            <span class="font-bold text-gray-900">{{ $fuelOrder->creator->name ?? 'Unknown' }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Prepared By (Data Logger)</p>
                    </div>
                     <div class="text-center">
                        <div class="border-b border-gray-400 pb-2 mb-2 px-8 min-h-[3rem] flex items-end justify-center">
                            <span class="text-gray-400 font-mono">________________ L</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Actual Quantity</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-12 pt-12 mt-12">
                    <div class="text-center">
                        <div class="border-b border-gray-400 pb-2 mb-2 px-8 min-h-[3rem]"></div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Fuel Man (Signature)</p>
                    </div>
                </div>
                @endif

                <!-- Audit Logs Section -->
                <div class="mt-12 pt-8 border-t border-gray-100 print:hidden">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Audit Logs</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Created By</p>
                            <p class="text-sm font-bold text-gray-900">{{ $fuelOrder->creator->name ?? 'System' }}</p>
                            <p class="text-[10px] text-gray-500 font-mono mt-1">{{ $fuelOrder->created_at->format('M d, Y H:i:s') }}</p>
                        </div>

                        @if($fuelOrder->updater && ($fuelOrder->updated_at != $fuelOrder->actualized_at) && ($fuelOrder->void_at != $fuelOrder->updated_at) )
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Last Updated By</p>
                                <p class="text-sm font-bold text-gray-900">{{ $fuelOrder->updater->name }}</p>
                                <p class="text-[10px] text-gray-500 font-mono mt-1">{{ $fuelOrder->updated_at->format('M d, Y H:i:s') }}</p>
                            </div>
                        @endif

                        @if($fuelOrder->actualizer)
                            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider mb-2">Actualized By</p>
                                <p class="text-sm font-bold text-emerald-900">{{ $fuelOrder->actualizer->name }}</p>
                                <p class="text-[10px] text-emerald-600 font-mono mt-1">{{ $fuelOrder->actualized_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                            </div>
                        @endif

                        @if($fuelOrder->status === 'VOID' && $fuelOrder->voider)
                            <div class="bg-rose-50 rounded-xl p-4 border border-rose-100">
                                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-wider mb-2">Voided By</p>
                                <p class="text-sm font-bold text-rose-900">{{ $fuelOrder->voider->name }}</p>
                                <p class="text-[10px] text-rose-600 font-mono mt-1">{{ $fuelOrder->void_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
