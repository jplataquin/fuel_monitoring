<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Chargeable Accounts') }}
            </h2>
            @if(Auth::user()->role === 'administrator')
                <x-button-link :href="route('chargeable-accounts.create')" color="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('Add Account') }}
                </x-button-link>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl">
                <div class="p-0 text-gray-100">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead>
                            <tr class="bg-[#49454F]/10">
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Account / Sub-Account Name</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] w-48">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F]">
                            @foreach($chargeableAccounts as $account)
                                <tr class="hover:bg-[#49454F]/10 transition-colors">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <span class="text-base font-bold text-[#E6E1E5] tracking-tight">{{ $account->name }}</span>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <span class="inline-flex items-center !px-2.5 !py-0.5 rounded-full text-xs font-medium {{ $account->status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $account->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-right space-x-1">
                                        <a href="{{ route('chargeable-accounts.show', $account) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @if(Auth::user()->role === 'administrator')
                                            <a href="{{ route('chargeable-accounts.edit', $account) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <form action="{{ route('chargeable-accounts.destroy', $account) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[#F2B8B5] hover:bg-[#F2B8B5]/10 p-2.5 rounded-full transition-colors inline-flex items-center" onclick="return confirm('Are you sure?')" title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($chargeableAccounts->isEmpty())
                <div class="mt-8 text-center bg-[#1C1B1F] rounded-[28px] p-12 border border-[#49454F]/50">
                    <svg class="mx-auto h-12 w-12 text-[#49454F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[#E6E1E5]">No accounts found</h3>
                    <p class="mt-1 text-sm text-[#CAC4D0]">Get started by creating a new chargeable account.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
