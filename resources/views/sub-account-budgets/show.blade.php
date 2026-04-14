<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Budget Details') }}
            </h2>
            <div class="flex items-center !space-x-4">
                @if(in_array(Auth::user()->role, ['administrator', 'moderator']) && $accountBudget->status === 'Pending')
                    <form action="{{ route('account-budgets.approve', $accountBudget) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-emerald-900/20 hover:scale-105 active:scale-95" onclick="return confirm('Are you sure you want to APPROVE this budget?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            {{ __('Approve') }}
                        </button>
                    </form>
                    <form action="{{ route('account-budgets.reject', $accountBudget) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-rose-900/20 hover:scale-105 active:scale-95" onclick="return confirm('Are you sure you want to REJECT this budget?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            {{ __('Reject') }}
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('account-budgets.edit', $accountBudget) }}" class="p-2.5 bg-[#49454F]/50 text-[#D0BCFF] hover:bg-[#D0BCFF]/10 rounded-full transition-colors" title="Edit Budget">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </a>

                <a href="{{ route('account-budgets.index') }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest transition-colors ml-2">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-10 border-b md:border-b-0 md:border-r border-[#49454F]/50">
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-4 flex items-center">
                                    <span class="w-8 h-px bg-[#D0BCFF]/30 !mr-4"></span>
                                    Sub Account
                                </h3>
                                <p class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                                    {{ $accountBudget->subAccount->name ?? '—' }}
                                </p>
                                <p class="text-sm text-[#CAC4D0] mt-1 uppercase tracking-widest font-medium">
                                    Parent: {{ $accountBudget->subAccount->chargeableAccount->name ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-4 flex items-center">
                                    <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                                    Budget Quantity
                                </h3>
                                <p class="text-4xl font-black text-[#D0BCFF] font-mono tracking-tighter">
                                    {{ number_format($accountBudget->budget_quantity, 2) }} <span class="text-lg font-bold ml-1 text-[#CAC4D0]">Liters</span>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-4 flex items-center">
                                    <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                                    Status
                                </h3>
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest border
                                    {{ $accountBudget->status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-[0_0_15px_rgba(52,211,153,0.1)]' : 
                                       ($accountBudget->status === 'Rejected' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 
                                       'bg-amber-500/10 text-amber-400 border-amber-500/20') }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $accountBudget->status === 'Approved' ? 'bg-emerald-400' : ($accountBudget->status === 'Rejected' ? 'bg-rose-400' : 'bg-amber-400') }}"></span>
                                    {{ $accountBudget->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-10 bg-[#2D2930]/30">
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-4 flex items-center">
                                    <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                                    Remarks
                                </h3>
                                <p class="text-[#E6E1E5] text-sm leading-relaxed italic">
                                    {{ $accountBudget->remarks ?: 'No remarks provided.' }}
                                </p>
                            </div>

                            <div class="pt-8 border-t border-[#49454F]/30 space-y-4">
                                <div class="flex justify-between text-xs">
                                    <span class="text-[#CAC4D0] font-bold uppercase tracking-widest">Allocated By:</span>
                                    <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->creator->name ?? 'System' }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-[#CAC4D0] font-bold uppercase tracking-widest">Date:</span>
                                    <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                
                                @if($accountBudget->status === 'Approved')
                                    <div class="flex justify-between text-xs pt-4 border-t border-[#49454F]/20">
                                        <span class="text-emerald-400 font-bold uppercase tracking-widest">Approved By:</span>
                                        <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->approver->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-emerald-400 font-bold uppercase tracking-widest">Approved At:</span>
                                        <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->approved_at ? $accountBudget->approved_at->format('M d, Y h:i A') : '—' }}</span>
                                    </div>
                                @elseif($accountBudget->status === 'Rejected')
                                    <div class="flex justify-between text-xs pt-4 border-t border-[#49454F]/20">
                                        <span class="text-rose-400 font-bold uppercase tracking-widest">Rejected By:</span>
                                        <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->rejecter->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-rose-400 font-bold uppercase tracking-widest">Rejected At:</span>
                                        <span class="text-[#E6E1E5] font-medium">{{ $accountBudget->rejected_at ? $accountBudget->rejected_at->format('M d, Y h:i A') : '—' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
