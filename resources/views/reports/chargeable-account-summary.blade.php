<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Chargeable Account Summary Report') }}
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
                    <form action="{{ route('reports.chargeable-accounts') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="w-full md:w-1/4">
                            <label for="account_id" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Chargeable Account</label>
                            <select name="account_id" id="account_id" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]">
                                <option value="">All Accounts</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/4">
                            <label for="date_from" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]" required>
                        </div>
                        <div class="w-full md:w-1/4">
                            <label for="date_to" class="block text-sm font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="block w-full rounded-xl border-[#49454F] bg-[#1C1B1F] text-[#E6E1E5] shadow-sm focus:border-[#D0BCFF] focus:ring-[#D0BCFF] sm:text-sm h-[42px]" required>
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
                        <h2 class="text-2xl font-black uppercase tracking-widest">Chargeable Account Summary Report</h2>
                        @if($dateFrom || $dateTo)
                            <p class="text-sm font-bold mt-2">Date: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'Any' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'Any' }}</p>
                        @endif
                    </div>
                    
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="min-w-full divide-y divide-[#49454F]/50 print:divide-black print:border-collapse print:border print:border-black">
                            <thead class="bg-[#49454F]/10 print:bg-gray-100 print:text-black">
                                <tr>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-left text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Account Name</th>
                                    
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Total KM</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Total Hours</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Budgeted Fuel</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Unbudgeted Fuel</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Total Calc. Fuel</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Total Approved Budget</th>
                                    <th class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black print:text-[9px] text-right text-xs font-bold text-[#CAC4D0] print:text-gray-800 uppercase tracking-[0.2em]">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F] print:bg-white print:divide-black">
                                @php
                                    $grandTotalKm = 0;
                                    $grandTotalHours = 0;
                                    $grandTotalBudgeted = 0;
                                    $grandTotalUnbudgeted = 0;
                                    $grandTotalTotalCalc = 0;
                                    $grandTotalTotalBudget = 0;
                                    $grandTotalRemaining = 0;
                                @endphp
                                @forelse($accountSummaries as $account)
                                    @php
                                        $grandTotalKm += $account['total_km'];
                                        $grandTotalHours += $account['total_hours'];
                                        $grandTotalBudgeted += $account['budgeted_fuel'];
                                        $grandTotalUnbudgeted += $account['unbudgeted_fuel'];
                                        $grandTotalTotalCalc += $account['total_calculated_fuel'];
                                        $grandTotalTotalBudget += $account['total_budget'];
                                    @endphp
                                    <tr class="hover:bg-[#49454F]/10 transition-colors print:hover:bg-transparent bg-[#49454F]/20">
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-xs font-black text-[#D0BCFF] print:text-black">
                                            {{ $account['name'] }}
                                        </td>
                                        
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#E6E1E5] print:text-black">
                                            {{ number_format($account['total_km'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#E6E1E5] print:text-black">
                                            {{ number_format($account['total_hours'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-black text-emerald-400 print:text-black">
                                            {{ number_format($account['budgeted_fuel'], 2) }} L
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-black text-rose-400 print:text-black">
                                            {{ number_format($account['unbudgeted_fuel'], 2) }} L
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-black text-[#E6E1E5] print:text-black">
                                            {{ number_format($account['total_calculated_fuel'], 2) }} L
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#D0BCFF] print:text-black">
                                            {{ number_format($account['total_budget'], 2) }} L
                                        </td>
                                        <td class="px-6 py-4 print:px-2 print:py-1 print:border print:border-black print:text-[10px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#D0BCFF] print:text-black">
                                            
                                            @php 
                                                $remaining = ($account['total_budget'] - $account['total_calculated_fuel']);
                                            @endphp

                                            @if($account['total_budget'] > 0)
                                                
                                                {{ number_format($remaining, 2) }} L

                                                @php 
                                                
                                                    $grandTotalRemaining += $remaining;

                                                @endphp

                                            @else

                                                0 L

                                            @endif
                                        </td>
                                    </tr>

                                    @if(isset($account['sub_accounts']) && count($account['sub_accounts']) > 0)
                                        @foreach($account['sub_accounts'] as $subAccount)
                                            <tr class="hover:bg-[#49454F]/20 transition-colors print:hover:bg-transparent">
                                                <td class="px-6 py-3 pl-10 print:px-4 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-xs font-medium text-[#CAC4D0] print:text-black">
                                                    └ {{ $subAccount['name'] }}
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono text-[#E6E1E5] print:text-black">
                                                    {{ number_format($subAccount['total_km'], 2) }}
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono text-[#E6E1E5] print:text-black">
                                                    {{ number_format($subAccount['total_hours'], 2) }}
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono font-bold text-emerald-400 print:text-black">
                                                    {{ number_format($subAccount['budgeted_fuel'], 2) }} L
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono font-bold text-rose-400 print:text-black">
                                                    {{ number_format($subAccount['unbudgeted_fuel'], 2) }} L
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono font-bold text-[#E6E1E5] print:text-black">
                                                    {{ number_format($subAccount['total_calculated_fuel'], 2) }} L
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono text-[#D0BCFF] print:text-black">
                                                    {{ number_format($subAccount['total_budget'], 2) }} L
                                                </td>
                                                <td class="px-6 py-3 print:px-2 print:py-1 print:border print:border-black print:text-[9px] whitespace-nowrap text-right text-xs font-mono text-[#D0BCFF] print:text-black">
                                                    @php 
                                                        $subRemaining = ($subAccount['total_budget'] - $subAccount['total_calculated_fuel']);
                                                    @endphp
                                                    @if($subAccount['total_budget'] > 0)
                                                        {{ number_format($subRemaining, 2) }} L
                                                    @else
                                                        0 L
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-16 text-center print:px-2 print:py-2 print:border print:border-black">
                                            <div class="flex flex-col items-center justify-center print:hidden">
                                                <div class="bg-[#49454F]/20 w-16 h-16 rounded-2xl flex items-center justify-center mb-4">
                                                    <svg class="w-8 h-8 text-[#CAC4D0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <p class="text-sm font-bold text-[#E6E1E5]">No report data to display.</p>
                                                <p class="text-xs text-[#CAC4D0] mt-1">Please select parameters to generate the report.</p>
                                            </div>
                                            <div class="hidden print:block text-black">
                                                No records found for the selected parameters.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if(count($accountSummaries) > 0)
                                    <tr class="bg-[#D0BCFF]/10 print:bg-gray-100 border-t-2 border-[#D0BCFF]/30 print:border-black">
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-black text-[#E6E1E5] print:text-black uppercase tracking-widest">
                                            Grand Total:
                                        </td>
                                     
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#CAC4D0] print:text-black">
                                            {{ number_format($grandTotalKm, 2) }}
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#CAC4D0] print:text-black">
                                            {{ number_format($grandTotalHours, 2) }}
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-emerald-400 print:text-black">
                                            {{ number_format($grandTotalBudgeted, 2) }} L
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-rose-400 print:text-black">
                                            {{ number_format($grandTotalUnbudgeted, 2) }} L
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#CAC4D0] print:text-black">
                                            {{ number_format($grandTotalTotalCalc, 2) }} L
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#D0BCFF] print:text-black">
                                             {{ number_format($grandTotalTotalBudget, 2) }} L
                                        </td>
                                        <td class="px-6 py-5 print:px-2 print:py-2 print:border print:border-black text-right text-sm font-mono font-black text-[#D0BCFF] print:text-black">
                                            {{ number_format($grandTotalRemaining ,2) }} L
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
