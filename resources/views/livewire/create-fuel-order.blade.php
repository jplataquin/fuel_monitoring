<div x-data="{ showEntriesModal: false }" class="relative">
    <form wire:submit="submit" class="space-y-6">
        <div>
            <label for="asset_id" class="block text-sm font-bold text-[#CAC4D0] uppercase tracking-wider">Select Asset</label>
            <select wire:model.live="asset_id" id="asset_id" class="mt-2 block w-full pl-3 pr-10 py-3 text-base bg-[#1C1B1F] text-[#E6E1E5] border border-[#49454F] focus:outline-none focus:ring-[#D0BCFF] focus:border-[#D0BCFF] sm:text-sm rounded-xl transition-colors">
                <option value="">-- Choose an Asset --</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->fleet_no }} - {{ $asset->assetType->name ?? 'Unknown Type' }}</option>
                @endforeach
            </select>
            @error('asset_id') <span class="text-[#F2B8B5] text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="date_from" class="block text-sm font-bold text-[#CAC4D0] uppercase tracking-wider">Date From</label>
                <input type="date" wire:model.live="date_from" id="date_from" class="mt-2 block w-full pl-3 pr-10 py-3 text-base bg-[#1C1B1F] text-[#E6E1E5] border border-[#49454F] focus:outline-none focus:ring-[#D0BCFF] focus:border-[#D0BCFF] sm:text-sm rounded-xl transition-colors">
                @error('date_from') <span class="text-[#F2B8B5] text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="date_to" class="block text-sm font-bold text-[#CAC4D0] uppercase tracking-wider">Date To</label>
                <input type="date" wire:model.live="date_to" id="date_to" class="mt-2 block w-full pl-3 pr-10 py-3 text-base bg-[#1C1B1F] text-[#E6E1E5] border border-[#49454F] focus:outline-none focus:ring-[#D0BCFF] focus:border-[#D0BCFF] sm:text-sm rounded-xl transition-colors">
                @error('date_to') <span class="text-[#F2B8B5] text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        @if($asset_id && $date_from && $date_to)
            <div class="mt-6 p-6 bg-[#49454F]/10 rounded-[20px] shadow-inner border border-[#49454F]/30">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4 border-b border-[#49454F]/50 pb-4">
                        <p class="text-sm text-[#CAC4D0] font-medium tracking-wide">
                            Entries in Range: 
                            @if($unprocessed_entries_count > 0)
                                <button type="button" @click="showEntriesModal = true" class="text-[#D0BCFF] text-lg font-bold hover:underline decoration-dashed underline-offset-4 focus:outline-none transition-all ml-1" title="Click to view entries">
                                    <strong class="text-[#D0BCFF] text-lg">{{ number_format($unprocessed_entries_count,2) }} 📁</strong>
                                </button>
                            @else
                               <strong class="text-[#D0BCFF] text-lg">{{ $unprocessed_entries_count }}</strong>
                            @endif
                        </p>
                        <div class="flex !space-x-4">
                            <p class="text-sm text-[#CAC4D0] font-medium tracking-wide">KM Factor: <strong class="text-[#D0BCFF] text-lg">{{ number_format($fuel_factor_km, 2) }} KM/L</strong></p>
                            <p class="text-sm text-[#CAC4D0] font-medium tracking-wide">HR Factor: <strong class="text-[#D0BCFF] text-lg">{{ number_format($fuel_factor_hr, 2) }} L/HR</strong></p>
                        </div>
                    </div>
                    
                    
                    @if(count($grouped_totals) > 0)
                        <div class="mt-8 border-t border-[#49454F]/30 pt-6">
                            <h4 class="text-xs font-bold text-[#CAC4D0] uppercase tracking-wider mb-4">Breakdown by Charged To</h4>
                            <div class="overflow-hidden rounded-xl border border-[#49454F]/50">
                                <table class="w-full text-left">
                                    <thead class="bg-[#1C1B1F]">
                                        <tr>
                                            <th class="px-4 py-3 text-xs font-bold text-[#CAC4D0] uppercase">Account</th>
                                            <th class="px-4 py-3 text-xs font-bold text-[#CAC4D0] uppercase text-right">Total KM</th>
                                            <th class="px-4 py-3 text-xs font-bold text-[#CAC4D0] uppercase text-right">Total Hours</th>
                                            <th class="px-4 py-3 text-xs font-bold text-[#CAC4D0] uppercase text-right">Fuel (L)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#49454F]/30">
                                        @foreach($grouped_totals as $account => $totals)
                                            <tr class="bg-[#1C1B1F]/30">
                                                <td class="px-4 py-3 text-sm text-[#E6E1E5] font-bold">{{ $account }}</td>
                                                <td class="px-4 py-3 text-sm text-[#D0BCFF] text-right font-mono">{{ number_format($totals['kilometers'], 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-[#D0BCFF] text-right font-mono">{{ number_format($totals['hours'], 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-[#10B981] text-right font-mono font-bold">{{ number_format($totals['quantity'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-[#CAC4D0] uppercase tracking-wider mb-2">Total Calculated KM</label>
                            <input type="text" readonly value="{{ number_format($calculated_kilometers, 2) }}" class="w-full px-4 py-3 bg-[#1C1B1F] text-[#E6E1E5] text-lg font-black border border-[#49454F] rounded-xl focus:ring-0" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#CAC4D0] uppercase tracking-wider mb-2">Total Calculated Hours</label>
                            <input type="text" readonly value="{{ number_format($calculated_hours, 2) }}" class="w-full px-4 py-3 bg-[#1C1B1F] text-[#E6E1E5] text-lg font-black border border-[#49454F] rounded-xl focus:ring-0" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#CAC4D0] uppercase tracking-wider mb-2">Calculated Fuel (Liters)</label>
                            <input type="text" readonly value="{{ number_format($calculated_quantity, 2) }}" class="w-full px-4 py-3 bg-[#D0BCFF]/10 text-[#D0BCFF] text-lg font-black border border-[#D0BCFF]/30 rounded-xl focus:ring-0" />
                        </div>
                    </div>

                </div>

                @if($unprocessed_entries_count > 0)
                    <div class="mt-6">
                        <label for="say_quantity" class="block text-sm font-bold text-[#CAC4D0] uppercase tracking-wider mb-2">Say Fuel Quantity (Liters)</label>
                        <div class="relative rounded-xl shadow-sm border border-[#49454F] overflow-hidden focus-within:ring-1 focus-within:ring-[#D0BCFF] focus-within:border-[#D0BCFF]">
                            <input type="number" step="0.01" wire:model="say_quantity" id="say_quantity" class="block w-full pl-4 pr-12 py-3 bg-[#1C1B1F] text-[#E6E1E5] text-lg font-black border-none focus:ring-0 placeholder-[#49454F]" placeholder="0.00" />
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-[#CAC4D0] font-bold">
                                    L
                                </span>
                            </div>
                        </div>
                        @error('say_quantity') <span class="text-[#F2B8B5] text-xs font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="pt-8">
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 bg-[#D0BCFF] border border-transparent rounded-full font-black text-sm text-[#381E72] uppercase tracking-widest hover:bg-[#EADDFF] focus:bg-[#EADDFF] active:bg-[#D0BCFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] focus:ring-offset-2 focus:ring-offset-[#141218] transition ease-in-out duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                                Create Fuel Order
                            </button>
                        </div>
                    </div>
                @endif
            @endif
    </form>

    <!-- Modal for Unprocessed Entries -->
    <div x-show="showEntriesModal" 
         style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
         
        <!-- Backdrop -->
        <div @click="showEntriesModal = false" class="fixed inset-0 bg-[#1C1B1F]/80 backdrop-blur-sm"></div>

        <!-- Modal Panel -->
        <div class="relative bg-[#2D2930] rounded-[28px] shadow-2xl border border-[#49454F] w-full max-w-7xl max-h-[85vh] flex flex-col transform overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-[#49454F]/50 bg-[#1C1B1F]">
                <div>
                    <h3 class="text-xl font-black text-[#E6E1E5]">Unprocessed Utilization Entries</h3>
                    <p class="text-sm text-[#CAC4D0] mt-1 font-medium">These logs will be covered by the new fuel order.</p>
                </div>
                <button type="button" @click="showEntriesModal = false" class="text-[#CAC4D0] hover:text-[#E6E1E5] bg-[#49454F]/20 hover:bg-[#49454F]/50 rounded-full p-2 transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto overflow-x-auto flex-1 p-8 custom-scrollbar">
                @if(count($unprocessed_entries) > 0)
                    <div class="min-w-max rounded-xl border border-[#49454F]/50">
                        <table class="w-full divide-y divide-[#49454F]/50">
                            <thead class="bg-[#1C1B1F]">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Unbudgeted</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Particulars</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Charged To</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider">Calc Type</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">Start KM</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">End KM</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">Start HR</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">End HR</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">Calc KM</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">Calc HR</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-wider text-right">Calc Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#49454F]/30 bg-[#2D2930]">
                                @foreach($unprocessed_entries as $entry)
                                    <tr class="hover:bg-[#49454F]/10 transition-colors cursor-pointer" onclick="window.open('{{ route('utilization-entries.show', $entry['id']) }}', '_blank')">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-[#E6E1E5]">{{ $entry['date'] }}</div>
                                            <div class="text-xs text-[#CAC4D0]">{{ $entry['start_time'] }} - {{ $entry['end_time'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm max-w-xs truncate">
                                            @if($entry['unbudgeted'])
                                                <span class="px-2 py-1 bg-[#F2B8B5]/20 text-[#F2B8B5] rounded-full text-[10px] font-black uppercase">Yes</span>
                                            @else
                                                <span class="px-2 py-1 bg-[#10B981]/20 text-[#10B981] rounded-full text-[10px] font-black uppercase">No</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-[#E6E1E5] max-w-xs truncate">{{ $entry['particulars'] }}</td>
                                        <td class="px-6 py-4 text-sm text-[#D0BCFF] font-bold max-w-xs truncate">{{ $entry['charged_to'] }}</td>
                                        <td class="px-6 py-4 text-sm text-[#E6E1E5] max-w-xs truncate">{{ $entry['calculation_type'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#D0BCFF] font-mono font-bold text-right">{{ number_format($entry['start_kilometer_reading'], 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#D0BCFF] font-mono font-bold text-right">{{ number_format($entry['end_kilometer_reading'], 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#D0BCFF] font-mono font-bold text-right">{{ number_format($entry['start_hour_reading'], 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#D0BCFF] font-mono font-bold text-right">{{ number_format($entry['end_hour_reading'], 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#10B981] font-mono font-bold text-right">{{ number_format($entry['calculated_kilometers'] ?? 0, 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#10B981] font-mono font-bold text-right">{{ number_format($entry['calculated_hours'] ?? 0, 1) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#10B981] font-mono font-bold text-right">{{ number_format($entry['calculated_quantity'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-[#49454F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-[#E6E1E5]">No Entries</h3>
                        <p class="mt-1 text-sm text-[#CAC4D0]">No unprocessed utilization entries found for this asset.</p>
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="px-8 py-4 bg-[#1C1B1F] border-t border-[#49454F]/50 flex justify-end rounded-b-[28px]">
                <button type="button" @click="showEntriesModal = false" class="inline-flex items-center px-6 py-2 bg-[#49454F]/50 border border-transparent rounded-full font-bold text-xs text-[#E6E1E5] uppercase tracking-widest hover:bg-[#49454F] focus:outline-none transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
        if (component.id === $wire.$id) {
            window.showLoadingIndicator();
            
            succeed(() => {
                window.hideLoadingIndicator();
            });
            
            fail(() => {
                window.hideLoadingIndicator();
            });
        }
    });
</script>
@endscript
