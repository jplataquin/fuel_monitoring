<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Update Chargeable Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <form method="POST" action="{{ route('chargeable-accounts.update', $chargeableAccount) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Account Name')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="name" name="name" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('name', $chargeableAccount->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <select id="status" name="status" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3">
                            <option value="Active" {{ old('status', $chargeableAccount->status) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $chargeableAccount->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30 gap-x-4">
                        <a href="{{ route('chargeable-accounts.index') }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest mr-8 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
