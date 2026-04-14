<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Entry Summary') }}
            </h2>
            <div class="flex items-center !space-x-2">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                    <form action="{{ route('utilization-entries.destroy', $utilizationEntry) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2.5 bg-[#49454F]/50 text-[#F2B8B5] hover:bg-[#F2B8B5]/10 rounded-full transition-colors" onclick="return confirm('Are you sure you want to soft delete this entry?')" title="Delete Entry">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                @endif
                
                @php
                    $canEdit = in_array(Auth::user()->role, ['administrator', 'moderator']) || 
                               (Auth::user()->role === 'data_logger' && $utilizationEntry->created_at->diffInMinutes(now()) <= 5);
                @endphp

                @if($canEdit)
                    <a href="{{ route('utilization-entries.edit', $utilizationEntry) }}" class="p-2.5 bg-[#49454F]/50 text-[#D0BCFF] hover:bg-[#D0BCFF]/10 rounded-full transition-colors" title="Edit Entry">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </a>
                @endif

                <a href="{{ route('assets.show', $utilizationEntry->asset_id) }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest transition-colors">
                    {{ __('Exit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl">
                <div class="p-10 text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div class="space-y-8">
                            <div>
                                <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-3">Asset Identity</p>
                                
                            </div>
                            
                            <div class="grid grid-cols-2 gap-6 pt-4">
                                <div class="col-span-2">
                                    <p class="text-2xl font-bold text-white tracking-tight">{{ $utilizationEntry->asset->fleet_no }}</p>
                                    <p class="text-xs font-bold text-[#CAC4D0] uppercase tracking-widest">{{ $utilizationEntry->asset->plate_no ?? 'No Plate' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Utilization Date</p>
                                    <p class="text-base font-bold text-white">{{ $utilizationEntry->date->format('M d, Y') }}</p>
                                </div>
                                <div class="col-span-1">
                                    <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Start Time</p>
                                    <p class="text-base font-bold text-white uppercase">{{ $utilizationEntry->start_time ? $utilizationEntry->start_time->format('H:i') : '' }}</p>
                                </div>
                                <div class="col-span-1">
                                    <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">End Time</p>
                                    <p class="text-base font-bold text-white uppercase">{{ $utilizationEntry->end_time ? $utilizationEntry->end_time->format('H:i') : 'N/A' }}</p>
                                </div>
                                
                                @php
                                    $operationHours = null;
                                    if ($utilizationEntry->end_time && $utilizationEntry->start_time) {
                                        $start = \Carbon\Carbon::parse($utilizationEntry->start_time);
                                        $end = \Carbon\Carbon::parse($utilizationEntry->end_time);
                                        if ($end->lessThan($start)) {
                                            $end->addDay();
                                        }
                                        $operationHours = $start->diffInMinutes($end) / 60;
                                    }
                                @endphp
                                
                                @if($operationHours !== null)
                                <div class="col-span-2">
                                    <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-1">Actual Operation Hours</p>
                                    <p class="text-base font-bold text-[#D0BCFF]">{{ number_format($operationHours, 2) }} <span class="text-xs uppercase tracking-widest text-[#49454F] ml-1">HRS</span></p>
                                </div>
                                @endif
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Reference</p>
                                <div class="bg-[#2D2930] p-5 rounded-2xl border border-[#49454F]/50">
                                    <p class="text-base text-white leading-relaxed font-medium">{{ $utilizationEntry->reference }}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Activity / Mission</p>
                                <div class="bg-[#2D2930] p-5 rounded-2xl border border-[#49454F]/50">
                                    <p class="text-base text-white leading-relaxed font-medium">{{ $utilizationEntry->particulars }}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Personnel In-Charge</p>
                                <p class="text-base font-bold text-white flex items-center">
                                    <span class="w-8 h-8 rounded-full bg-[#D0BCFF]/10 flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </span>
                                    {{ $utilizationEntry->driver_operator_name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Charged To</p>
                                <p class="text-base font-bold text-white flex items-center">
                                    <span class="w-8 h-8 rounded-full bg-[#D0BCFF]/10 flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                    </span>
                                    {{ $utilizationEntry->chargeableAccount->name ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Sub Account</p>
                                <p class="text-base font-bold text-white flex items-center">
                                    <span class="w-8 h-8 rounded-full bg-[#D0BCFF]/10 flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-[#D0BCFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </span>
                                    {{ $utilizationEntry->subAccount->name ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Budget Status</p>
                                <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold uppercase tracking-widest border 
                                    {{ $utilizationEntry->unbudgeted ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }}">
                                    {{ $utilizationEntry->unbudgeted ? 'Unbudgeted' : 'Budgeted' }}
                                </div>
                            </div>

                            @if($utilizationEntry->remarks)
                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Remarks</p>
                                <div class="bg-[#2D2930] p-5 rounded-2xl border border-[#49454F]/50">
                                    <p class="text-base text-white leading-relaxed font-medium whitespace-pre-line">{{ $utilizationEntry->remarks }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="space-y-8 bg-[#2D2930]/30 p-8 rounded-[32px] border border-[#49454F]/30">
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">Start KM Reading</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">{{ number_format($utilizationEntry->start_kilometer_reading, 2) }} <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">KM</span></p>
                                    </div>
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">End KM Reading</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">{{ number_format($utilizationEntry->end_kilometer_reading, 2) }} <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">KM</span></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">Start Engine Hours</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">{{ number_format($utilizationEntry->start_hour_reading, 2) }} <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">HR</span></p>
                                    </div>
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">End Engine Hours</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">{{ number_format($utilizationEntry->end_hour_reading, 2) }} <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">HR</span></p>
                                    </div>
                                </div>
                                 <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">Total KM</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">
                                            {{ number_format( ($utilizationEntry->end_kilometer_reading - $utilizationEntry->start_kilometer_reading), 2) }} 
                                            <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">KM</span></p>
                                    </div>
                                    <div class="p-6 bg-[#1C1B1F] rounded-2xl border border-[#49454F]/50 shadow-inner">
                                        <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-2">Total Engine Hours</p>
                                        <p class="text-3xl font-bold text-white tracking-tighter">
                                            {{ number_format( ($utilizationEntry->end_hour_reading - $utilizationEntry->start_hour_reading), 2)}} 
                                            <span class="text-xs font-bold text-[#49454F] ml-1 uppercase">HR</span></p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Fuel Order Reference</p>
                                @if($utilizationEntry->fuel_order_id)
                                    <a href="{{ route('fuel-orders.show', $utilizationEntry->fuel_order_id) }}" class="inline-block px-4 py-2 bg-[#D0BCFF]/10 text-[#D0BCFF] hover:bg-[#D0BCFF]/20 hover:text-white rounded-xl border border-[#D0BCFF]/20 font-bold text-sm tracking-widest transition-colors cursor-pointer">#{{ $utilizationEntry->fuel_order_id }}</a>
                                @else
                                    <span class="text-[#49454F] font-bold uppercase text-[10px] tracking-widest italic">No reference provided</span>
                                @endif
                            </div>

                            <div>
                                <p class="text-[10px] font-bold text-[#CAC4D0] uppercase tracking-[0.2em] mb-2">Log Metadata</p>
                                <div class="space-y-3">
                                    <div class="flex items-center text-[10px] text-[#CAC4D0] font-bold uppercase tracking-widest">
                                        <svg class="w-3.5 h-3.5 mr-2 text-[#49454F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Created: {{ $utilizationEntry->created_at->format('M d, Y @ H:i') }}
                                    </div>
                                    <div class="flex items-center text-[10px] text-[#CAC4D0] font-bold uppercase tracking-widest">
                                        <svg class="w-3.5 h-3.5 mr-2 text-[#49454F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        By {{ $utilizationEntry->creator->name ?? 'System' }}
                                    </div>
                                    
                                    @if($utilizationEntry->updated_at && $utilizationEntry->updated_at->ne($utilizationEntry->created_at) && $utilizationEntry->updated_by)
                                    <div class="pt-2 border-t border-[#49454F]/30"></div>
                                    <div class="flex items-center text-[10px] text-[#D0BCFF] font-bold uppercase tracking-widest">
                                        <svg class="w-3.5 h-3.5 mr-2 text-[#49454F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        Updated: {{ $utilizationEntry->updated_at->format('M d, Y @ H:i') }}
                                    </div>
                                    <div class="flex items-center text-[10px] text-[#D0BCFF] font-bold uppercase tracking-widest">
                                        <svg class="w-3.5 h-3.5 mr-2 text-[#49454F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        By {{ $utilizationEntry->updater->name ?? 'System' }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
