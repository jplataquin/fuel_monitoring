<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Fuel Orders') }}
            </h2>
            <div class="flex items-center gap-3">
                <form action="{{ route('fuel-orders.index') }}" method="GET" class="flex">
                    <input type="text" name="fleet_no" value="{{ request('fleet_no') }}" placeholder="Search Fleet No..." class="bg-[#1C1B1F] text-[#E6E1E5] border-[#49454F] rounded-l-full focus:ring-[#D0BCFF] focus:border-[#D0BCFF] text-sm px-4 py-2 w-48 placeholder-[#49454F]">
                    <button type="submit" class="bg-[#49454F] hover:bg-[#CAC4D0] text-[#E6E1E5] hover:text-[#1C1B1F] rounded-r-full px-4 py-2 transition-colors border border-[#49454F]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>

                @if(in_array(Auth::user()->role, ['data_logger', 'administrator']))
                    <x-button-link :href="route('fuel-orders.create')" class="whitespace-nowrap">
                        {{ __('Create Order') }}
                    </x-button-link>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl p-8">
                
                @if (session('message'))
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-xl flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-emerald-400 font-bold">
                                {{ session('message') }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto rounded-xl border border-[#49454F]/50">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead class="bg-[#49454F]/10">
                            <tr>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">ID</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Asset</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Calculated</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Say Qty</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Status</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Actual Qty</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Date Created</th>
                                <th scope="col" class="px-6 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F]">
                            @forelse($fuelOrders as $order)
                                <tr class="hover:bg-[#49454F]/10 transition-colors">
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-bold text-[#D0BCFF] font-mono">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-bold text-[#E6E1E5] tracking-tight">{{ $order->asset->fleet_no }}</div>
                                        <div class="text-[10px] text-[#CAC4D0] uppercase tracking-widest">{{ $order->asset->plate_no ?? 'No Plate' }}</div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-mono text-[#E6E1E5]">{{ number_format($order->calculated_quantity, 2) }} L</td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-black text-[#D0BCFF] font-mono">{{ number_format($order->say_quantity, 2) }} L</td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full border
                                            {{ $order->status === 'DONE' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' :
                                               ($order->status === 'VOID' ? 'bg-red-500/10 text-red-400 border-red-500/20' :
                                               'bg-amber-500/10 text-amber-400 border-amber-500/20') }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-black text-[#A8EFF2] font-mono">
                                        @if($order->status === 'DONE')
                                            {{ number_format($order->actual_quantity, 2) }} L
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-[10px] font-bold text-[#CAC4D0] uppercase tracking-widest">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-5 whitespace-nowrap text-right space-x-1">
                                        @if(Auth::user()->role === 'administrator')
                                            <a href="{{ route('fuel-orders.edit', $order) }}" class="text-[#A8EFF2] hover:bg-[#A8EFF2]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="Edit Order">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        @endif
                                        @if(in_array(Auth::user()->role, ['fuel_man', 'data_logger', 'data logger', 'administrator']) && $order->status === 'PEND')
                                            <a href="{{ route('fuel-orders.actualize', $order) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="Actualize Quantity">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('fuel-orders.show', $order) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="View / Print Order">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-[#49454F]/20 w-16 h-16 rounded-2xl flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-[#CAC4D0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="text-sm font-bold text-[#E6E1E5]">No fuel orders found.</p>
                                            @if(request('fleet_no'))
                                                <p class="text-xs text-[#CAC4D0] mt-1">Try adjusting your search filter.</p>
                                                <a href="{{ route('fuel-orders.index') }}" class="text-[#D0BCFF] font-bold text-xs uppercase tracking-widest mt-4 hover:underline underline-offset-4">Clear Filter</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6">
                    {{ $fuelOrders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
