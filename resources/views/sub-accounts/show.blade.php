<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-bold uppercase tracking-widest text-[#CAC4D0]">
                        <li class="inline-flex items-center">
                            <a href="{{ route('chargeable-accounts.index') }}" class="hover:text-[#D0BCFF]">Accounts</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-[#49454F]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="ml-1 md:ml-2 hover:text-[#D0BCFF]">{{ $subAccount->chargeableAccount->name }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-[#49454F]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 md:ml-2 text-[#D0BCFF]">{{ $subAccount->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <!-- Sub-Account Info -->
            <div class="bg-[#2D2930] rounded-[28px] p-10 border border-[#49454F]/50 shadow-xl flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-2 flex items-center">
                        <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                        Sub-Account Name
                    </h3>
                    <span class="text-2xl font-bold text-[#E6E1E5]">
                        {{ $subAccount->name }}
                    </span>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-2 flex items-center">
                        <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                        Parent Account
                    </h3>
                    <a href="{{ route('chargeable-accounts.show', $subAccount->chargeableAccount) }}" class="text-xl font-bold text-[#D0BCFF] hover:underline">
                        {{ $subAccount->chargeableAccount->name }}
                    </a>
                </div>
                <div class="text-right">
                    <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-2 flex items-center justify-end">
                        Total Approved Budget
                        <span class="w-8 h-px bg-[#D0BCFF]/30 ml-4"></span>
                    </h3>
                    <p class="text-4xl font-black text-[#E6E1E5] font-mono tracking-tighter">
                        {{ number_format($subAccount->budgets()->where('status', 'Approved')->sum('budget_quantity'), 2) }} <span class="text-sm font-bold text-[#CAC4D0] ml-1 uppercase">L</span>
                    </p>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['administrator', 'budgeteer']))
                <!-- Allocate Budget Form Card -->
                <div class="bg-[#1C1B1F] rounded-[28px] !p-8 md:p-10 border border-[#49454F]/50 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-[#D0BCFF]"></div>
                    <div class="mb-6 flex items-center justify-between">
                        <h4 class="text-xl font-bold text-[#E6E1E5] tracking-tight">Allocate New Budget</h4>
                        <span class="px-3 py-1 bg-[#D0BCFF]/10 text-[#D0BCFF] text-[10px] font-bold uppercase tracking-widest rounded-full border border-[#D0BCFF]/20">Pending Approval</span>
                    </div>
                    <form action="{{ route('account-budgets.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="sub_account_id" value="{{ $subAccount->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="budget_quantity" :value="__('Budget Quantity (Liters)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                <input type="number" name="budget_quantity" id="budget_quantity" step="0.01" required placeholder="0.00" class="block w-full px-5 py-3 bg-[#2D2930] text-[#E6E1E5] border border-[#49454F]/50 focus:outline-none focus:ring-2 focus:ring-[#D0BCFF]/50 focus:border-[#D0BCFF] text-base rounded-2xl transition-all shadow-inner placeholder-[#49454F]">
                                @error('budget_quantity')
                                    <p class="text-rose-400 text-[10px] font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-input-label for="remarks" :value="__('Remarks')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                                <input type="text" name="remarks" id="remarks" placeholder="Optional notes..." class="block w-full px-5 py-3 bg-[#2D2930] text-[#E6E1E5] border border-[#49454F]/50 focus:outline-none focus:ring-2 focus:ring-[#D0BCFF]/50 focus:border-[#D0BCFF] text-base rounded-2xl transition-all shadow-inner placeholder-[#49454F]">
                                @error('remarks')
                                    <p class="text-rose-400 text-[10px] font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end pt-4 border-t border-[#49454F]/30">
                            <button type="submit" class="bg-[#D0BCFF] text-[#381E72] px-8 py-2.5 rounded-full text-sm font-black uppercase tracking-widest hover:bg-[#EADDFF] hover:scale-105 transition-all shadow-[0_0_20px_rgba(208,188,255,0.3)]">
                                Submit for Approval
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Budget History -->
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl">
                <div class="p-8 border-b border-[#49454F]/50 bg-[#2D2930] flex justify-between items-center">
                    <h3 class="text-xl font-bold text-[#E6E1E5]">Budget Allocation History</h3>
                  
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead>
                            <tr class="bg-[#49454F]/10">
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Date</th>
                                <th class="px-8 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Quantity</th>
                                <th class="px-8 py-5 text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Remarks</th>
                                <th class="px-8 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#49454F]/30">
                            @forelse($subAccount->budgets()->orderBy('created_at', 'desc')->get() as $budget)
                                <tr class="hover:bg-[#49454F]/10 transition-colors">
                                    <td class="px-8 py-5 whitespace-nowrap text-sm text-[#E6E1E5]">
                                        {{ $budget->created_at->format('M d, Y') }}
                                        <span class="block text-[10px] text-[#CAC4D0] uppercase font-bold">{{ $budget->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-right font-mono font-bold text-[#D0BCFF]">
                                        {{ number_format($budget->budget_quantity, 2) }} L
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border
                                            {{ $budget->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                                               ($budget->status === 'Rejected' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 
                                               'bg-amber-500/10 text-amber-400 border-amber-500/20') }}">
                                            {{ $budget->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-sm text-[#CAC4D0]">
                                        {{ Str::limit($budget->remarks, 50) ?: '—' }}
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-right">
                                        <a href="{{ route('account-budgets.show', $budget) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="View Budget Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-16 text-center">
                                        <svg class="mx-auto h-12 w-12 text-[#49454F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-[#E6E1E5]">No budget history found</h3>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
