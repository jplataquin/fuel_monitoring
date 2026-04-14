<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Fleet Inventory') }}
            </h2>
            @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                <x-button-link :href="route('assets.create')" color="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('New Asset') }}
                </x-button-link>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Action Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                @foreach($assets as $asset)
                    <!-- Individual Asset Card -->
                    <a href="{{ route('assets.show', $asset) }}" class="group bg-[#2D2930] hover:bg-[#49454F]/50 rounded-[28px] p-8 transition-all duration-300 shadow-xl border border-[#49454F]/50 flex flex-col justify-between min-h-[80px]">
                        <div>
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h4 class="text-2xl font-black text-[#E6E1E5] tracking-tight group-hover:text-[#D0BCFF] transition-colors">{{ $asset->fleet_no }}</h4>
                                    <p class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mt-1">{{ $asset->assetType->name }}</p>
                                </div>
                                <span class="px-3 py-1 bg-[#49454F]/30 text-[#CAC4D0] text-[10px] font-bold rounded-lg border border-[#49454F]/50 uppercase tracking-widest">{{ $asset->plate_no ?? '—' }}</span>
                            </div>
                        </div>
                        
                        
                    </a>
                @endforeach
            </div>

            @if($assets->isEmpty())
                <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl mt-8">
                    <div class="p-16 text-center">
                        <div class="bg-[#49454F]/20 w-20 h-20 rounded-[28px] flex items-center justify-center mx-auto mb-6">
                            <svg class="h-10 w-10 text-[#CAC4D0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#E6E1E5]">No assets registered</h3>
                        <p class="mt-2 text-[#CAC4D0] max-w-xs mx-auto">Start building your fleet inventory to begin monitoring utilization.</p>
                        @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                            <div class="mt-8">
                                <a href="{{ route('assets.create') }}" class="text-[#D0BCFF] font-bold uppercase tracking-widest text-sm hover:underline underline-offset-8 decoration-2">Add your first asset</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
