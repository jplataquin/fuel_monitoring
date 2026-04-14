<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('User Management') }}
            </h2>
            <div class="space-x-3">
                @if(Auth::user()->role === 'administrator')
                    <x-button-link :href="route('users.create-moderator')" color="primary" class="shadow-md">
                        {{ __('Add Moderator') }}
                    </x-button-link>
                @endif
                <x-button-link :href="route('users.create-data-logger')" color="info" class="shadow-md">
                    {{ __('Add Data Logger') }}
                </x-button-link>
                <x-button-link :href="route('users.create-fuel-man')" color="indigo-light" class="shadow-md">
                    {{ __('Add Fuel Man') }}
                </x-button-link>
                <x-button-link :href="route('users.create-budgeteer')" color="success" class="shadow-md text-emerald-400 border-emerald-400">
                    {{ __('Add Budgeteer') }}
                </x-button-link>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl">
                <div class="p-0 text-gray-100">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead>
                            <tr class="bg-[#49454F]/10">
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Name</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Email Address</th>
                                <th class="px-8 py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Role</th>
                                <th class="px-8 py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F]">
                            @foreach($users as $user)
                                <tr class="hover:bg-[#49454F]/10 transition-colors">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <span class="text-base font-bold text-[#E6E1E5] tracking-tight">{{ $user->name }}</span>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-[#CAC4D0] text-sm font-mono tracking-tighter">{{ $user->email }}</td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        @php
                                            $roleClass = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                                            if ($user->role === 'administrator') $roleClass = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
                                            elseif ($user->role === 'moderator') $roleClass = 'bg-[#D0BCFF]/10 text-[#D0BCFF] border-[#D0BCFF]/20';
                                            elseif ($user->role === 'budgeteer') $roleClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                                            elseif ($user->role === 'fuel_man') $roleClass = 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20';
                                        @endphp
                                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg border {{ $roleClass }}">
                                            {{ str_replace('_', ' ', $user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-right space-x-1">
                                        <a href="{{ route('users.edit', $user) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="Edit / Reset Password">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#F2B8B5] hover:bg-[#F2B8B5]/10 p-2.5 rounded-full transition-colors inline-flex items-center" onclick="return confirm('Are you sure you want to soft delete this user?')" title="Delete User">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
