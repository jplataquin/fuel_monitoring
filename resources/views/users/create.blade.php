<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Add New Access') }}: {{ ucfirst($role) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-2xl p-10">
                <form method="POST" action="{{ route('users.store') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">

                    <div>
                        <x-input-label for="name" :value="__('Full Name')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="name" name="name" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email Address')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="email" name="email" type="email" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Temporary Password')" class="text-[#CAC4D0] text-[10px] font-bold uppercase tracking-[0.2em] ml-1 mb-2" />
                        <x-text-input id="password" name="password" type="text" class="block w-full bg-[#2D2930] border-[#49454F] text-[#E6E1E5] focus:ring-[#D0BCFF] rounded-xl p-3 font-mono" required placeholder="Generate a secure password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400 text-xs font-bold" />
                    </div>

                    <div class="flex items-center justify-end pt-8 border-t border-[#49454F]/30 gap-x-4">
                        <a href="{{ route('users.index') }}" class="text-[#CAC4D0] hover:text-[#E6E1E5] text-xs font-bold uppercase tracking-widest mr-8 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-[#D0BCFF] text-[#381E72] rounded-full font-bold text-xs uppercase tracking-[0.2em] hover:bg-[#EADDFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] transition shadow-lg shadow-[#D0BCFF]/20">
                            {{ __('CREATE') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
