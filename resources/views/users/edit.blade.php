<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Update Access') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- User Information Section -->
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <h3 class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-[0.3em] mb-10 flex items-center">
                    <span class="w-8 h-px bg-[#D0BCFF]/30 mr-4"></span>
                    Identity Information
                </h3>
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Full Name')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="name" name="name" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email Address')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="email" name="email" type="email" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30">
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Reset Section -->
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#F2B8B5]/30 shadow-2xl p-10">
                <h3 class="text-[10px] font-bold text-[#F2B8B5] uppercase tracking-[0.3em] mb-10 flex items-center">
                    <span class="w-8 h-px bg-[#F2B8B5]/30 mr-4"></span>
                    Security Override
                </h3>
                <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-8">
                    @csrf

                    <div>
                        <x-input-label for="password" :value="__('New Temporary Password')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="password" name="password" type="text" class="block w-full bg-[#2D2930] border-[#F2B8B5]/30 text-[#E6E1E5] focus:ring-[#F2B8B5] rounded-xl p-3 font-mono" required placeholder="Issue a new password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30">
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#F2B8B5] text-[#601410] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#F9DEDC] focus:outline-none focus:ring-2 focus:ring-[#F2B8B5] transition shadow-lg shadow-[#F2B8B5]/20">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
