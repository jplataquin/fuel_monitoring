<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Edit Sub Account Budget') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <form method="POST" action="{{ route('account-budgets.update', $accountBudget) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="sub_account_id" :value="__('Sub Account')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <select id="sub_account_id" name="sub_account_id" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" required>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('sub_account_id', $accountBudget->sub_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->chargeableAccount->name }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('sub_account_id')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div>
                        <x-input-label for="budget_quantity" :value="__('Budget Quantity (Liters)')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="budget_quantity" name="budget_quantity" type="number" step="0.01" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('budget_quantity', $accountBudget->budget_quantity)" required placeholder="0.00" />
                        <x-input-error :messages="$errors->get('budget_quantity')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    @if(in_array(Auth::user()->role, ['administrator', 'moderator']))
                        <div>
                            <x-input-label for="status" :value="__('Status')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                            <select id="status" name="status" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3">
                                <option value="Pending" {{ old('status', $accountBudget->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ old('status', $accountBudget->status) == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ old('status', $accountBudget->status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2 text-rose-400 text-xs font-bold" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="remarks" :value="__('Remarks')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <textarea id="remarks" name="remarks" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 h-32" placeholder="Optional notes...">{{ old('remarks', $accountBudget->remarks) }}</textarea>
                        <x-input-error :messages="$errors->get('remarks')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30 gap-x-4">
                        <a href="{{ route('account-budgets.index') }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest mr-8 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('UPDATE') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
