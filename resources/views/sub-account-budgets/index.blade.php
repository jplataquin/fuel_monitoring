<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight print:text-black">
                {{ __('Budget') }}
            </h2>
            <div class="print:hidden">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 print:py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:px-0">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl print:bg-white print:shadow-none print:rounded-none print:border-black print:border-2">
                <!-- Filter -->
                <div class="p-8 border-b border-[#49454F]/50 bg-[#2D2930] print:hidden">
                    <form method="GET" action="{{ route('account-budgets.index') }}" class="flex flex-wrap gap-4 items-end">
                        <div class="w-full md:w-1/3">
                            <label class="block text-[10px] font-bold text-[#E6E1E5] mb-2 uppercase tracking-widest">Search by Chargeable Account</label>
                            <select name="chargeable_account_id" onchange="this.form.submit()" class="block w-full bg-[#1C1B1F] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-2.5 text-sm">
                                <option value="">Select Chargeable Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ request('chargeable_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="p-0 text-gray-100 print:text-black">
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="min-w-full divide-y divide-[#49454F]/50 print:divide-black print:border-collapse print:border print:border-black">
                            <thead class="print:border-b print:border-black">
                                <tr class="bg-[#49454F]/10 print:bg-gray-100">
                                    <th class="px-8 py-5 print:px-4 print:py-2 print:border print:border-black text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] print:text-black">Hierarchy / Reference</th>
                                    <th class="px-8 py-5 print:px-4 print:py-2 print:border print:border-black text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] print:text-black">Budget Qty</th>
                                    <th class="px-8 py-5 print:px-4 print:py-2 print:border print:border-black text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] print:text-black">Status</th>
                                    <th class="px-8 py-5 print:px-4 print:py-2 print:border print:border-black text-center text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] print:text-black">Date Allocated</th>
                                    <th class="px-8 py-5 print:px-4 print:py-2 print:border print:border-black text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] print:text-black">Remarks</th>
                                    <th class="px-8 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] w-32 print:hidden">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F] print:bg-white print:divide-black">
                                @php 
                                    $lastChargeableId = null; 
                                    $lastSubId = null;
                                @endphp



                                @forelse($budgets as $budget)
                                    @php 
                                        $currentChargeableId = $budget->chargeable_account_id;
                                        $currentSubId = $budget->sub_account_id;
                                    @endphp

                                    {{-- Level 1: Chargeable Account Header --}}
                                    @if($lastChargeableId !== $currentChargeableId)
                                        <tr class="bg-[#211F24] border-t-4 border-[#D0BCFF] print:bg-gray-200 print:border-black print:border">
                                            <td colspan="6" class="px-8 py-4 print:px-4 print:py-2 print:border print:border-black">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 rounded-xl bg-[#D0BCFF] flex items-center justify-center mr-4 shadow-lg shadow-[#D0BCFF]/10 print:hidden">
                                                            <svg class="w-6 h-6 text-[#381E72]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.4em] mb-0.5 print:text-black">Primary Account</h3>
                                                            <span class="text-xl font-black text-[#E6E1E5] uppercase tracking-wider print:text-black">{{ $budget->subAccount?->chargeableAccount?->name ?? 'Unknown' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.2em] mb-0.5 opacity-60 print:text-black print:opacity-100">Account Total</h3>
                                                        <p class="text-xl font-black text-[#E6E1E5] font-mono tracking-tighter print:text-black">
                                                            {{ number_format($budget->subAccount?->chargeableAccount?->subAccounts->flatMap(function($s) { return $s->budgets; })->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @php $lastChargeableId = $currentChargeableId; $lastSubId = null; @endphp
                                    @endif

                                    {{-- Level 2: Sub-Account Header --}}
                                    @if($lastSubId !== $currentSubId)
                                        <tr class="bg-[#2D2930]/50 border-t border-[#49454F]/30 print:bg-gray-100 print:border-black print:border">
                                            <td colspan="6" class="!px-8 !py-2.5 !pl-16 print:px-4 print:py-2 print:pl-8 print:border print:border-black">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-1.5 h-6 bg-[#D0BCFF]/30 rounded-full !mr-4 print:hidden"></div>
                                                        <span class="text-sm font-bold text-[#D0BCFF] uppercase tracking-widest print:text-black">└ {{ $budget->subAccount?->name ?? 'Unknown Sub' }}</span>
                                                    </div>
                                                    <div class="text-right pr-4">
                                                        <span class="text-[10px] font-bold text-[#CAC4D0] uppercase mr-2 opacity-50 text-right print:text-black print:opacity-100">Sub Total:</span>
                                                        <span class="text-sm font-bold text-[#E6E1E5] font-mono print:text-black">{{ number_format($budget->subAccount?->budgets->where('status', 'Approved')->sum('budget_quantity') ?? 0, 2) }} L</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        @php 
                                            $count = 1;
                                            $lastSubId = $currentSubId; 
                                        @endphp
                                    @endif

                                    {{-- Level 3: Individual Budget Records --}}
                                    <tr class="hover:bg-[#49454F]/10 transition-colors group print:hover:bg-transparent {{ $budget->status === 'Rejected' ? 'print:hidden' : '' }}">
                                        <td class="!px-8 !py-5 whitespace-nowrap !pl-24 print:px-4 print:py-2 print:pl-12 print:border print:border-black">
                                            <div class="flex items-center">
                                                <div class="w-2 h-px bg-[#49454F] group-hover:bg-[#D0BCFF] !mr-3 transition-colors print:hidden"></div>
                                                <a href="{{ route('account-budgets.show', $budget) }}" class="text-sm font-medium text-[#CAC4D0] group-hover:text-[#E6E1E5] transition-colors cursor-pointer print:text-black print:no-underline">
                                                    <span class="print:hidden">Allocation #{{ $count }}</span>
                                                    <span class="hidden print:inline">- {{ $budget->id }}</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap text-right text-[#D0BCFF] text-base font-mono font-bold tracking-tighter print:text-black print:px-4 print:py-2 print:border print:border-black">
                                            {{ number_format($budget->budget_quantity, 2) }} L
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap text-center print:text-black print:px-4 print:py-2 print:border print:border-black">
                                            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border print:border-none print:px-0 print:py-0 print:font-bold
                                                {{ $budget->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 print:text-emerald-700' : 
                                                   ($budget->status === 'Rejected' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20 print:text-rose-700' : 
                                                   'bg-amber-500/10 text-amber-400 border-amber-500/20 print:text-amber-700') }}">
                                                {{ $budget->status }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap text-center text-[#CAC4D0] text-sm print:text-black print:px-4 print:py-2 print:border print:border-black">
                                            <span class="px-3 py-1 bg-[#49454F]/50 rounded text-xs font-bold print:bg-transparent print:px-0 print:py-0">{{ $budget->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="px-8 py-5 text-[#CAC4D0] text-sm italic opacity-60 print:text-black print:opacity-100 print:not-italic print:px-4 print:py-2 print:border print:border-black">
                                            {{ Str::limit($budget->remarks, 40) ?: '—' }}
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap text-right space-x-2 print:hidden">
                                            @php $userRole = auth()->user()->role; @endphp
                                            @if(in_array($userRole, ['administrator', 'moderator']) && $budget->status === 'Pending')
                                                <form action="{{ route('account-budgets.approve', $budget) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 p-2 rounded-xl transition-all border border-emerald-500/30 inline-flex items-center shadow-sm" onclick="return confirm('Are you sure you want to APPROVE this budget?')" title="Approve Budget">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    </button>
                                                </form>
                                                <form action="{{ route('account-budgets.reject', $budget) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="bg-rose-500/20 text-rose-400 hover:bg-rose-500/30 p-2 rounded-xl transition-all border border-rose-500/30 inline-flex items-center shadow-sm" onclick="return confirm('Are you sure you want to REJECT this budget?')" title="Reject Budget">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('account-budgets.show', $budget) }}" class="text-[#CAC4D0] hover:bg-[#CAC4D0]/10 p-2 rounded-full transition-colors inline-flex items-center" title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                            <a href="{{ route('account-budgets.edit', $budget) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2 rounded-full transition-colors inline-flex items-center" title="Edit Budget">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form action="{{ route('account-budgets.destroy', $budget) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[#F2B8B5] hover:bg-[#F2B8B5]/10 p-2 rounded-full transition-colors inline-flex items-center" onclick="return confirm('Are you sure you want to delete this budget?')" title="Delete Budget">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    @php $count++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-8 py-16 text-center text-[#CAC4D0]">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-[#49454F] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="text-sm font-bold uppercase tracking-widest">
                                                    @if(request('chargeable_account_id'))
                                                        No budgets found for this account
                                                    @else
                                                        Please select a Chargeable Account to view budget allocations
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 print:hidden">
                {{ $budgets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
