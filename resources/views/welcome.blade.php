<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Fuel Monitoring') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#1C1B1F] text-[#E6E1E5] flex items-center justify-center min-h-screen p-4">
        <div class="max-w-md w-full">
            <div class="flex flex-col items-center mb-10">
                <div class="bg-[#D0BCFF] w-20 h-20 rounded-[22px] flex items-center justify-center mb-6 shadow-lg shadow-[#D0BCFF]/20">
                    <x-application-logo class="w-12 h-12 fill-current text-[#381E72]" />
                </div>
                <h1 class="text-4xl font-black text-[#E6E1E5] mb-2 tracking-tight">Fuel Monitoring</h1>
            </div>

            <div class="bg-[#2D2930] shadow-2xl rounded-[28px] p-8 md:p-10 border border-[#49454F]/50">
                @auth
                    <div class="text-center py-6">
                        <p class="text-xl text-[#E6E1E5] mb-8 font-bold">Welcome back, <span class="text-[#D0BCFF]">{{ Auth::user()->name }}</span></p>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center p-1 px-8 py-4 border border-transparent text-sm font-black uppercase tracking-[0.2em] rounded-full text-[#1C1B1F] bg-[#D0BCFF] hover:bg-[#EADDFF] transition duration-150 w-full shadow-lg">
                            Go to Console
                        </a>
                    </div>
                @else
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-8">
                        @csrf

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <x-input-label for="email" :value="__('Email')" class="text-[#CAC4D0] text-xs font-bold uppercase tracking-widest ml-1" />
                            <x-text-input id="email" class="block mt-1 w-full p-4 bg-[#1C1B1F] border-[#49454F] text-[#E6E1E5] focus:ring-2 focus:ring-[#D0BCFF] focus:border-transparent rounded-xl transition-all" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="user@fleet.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <x-input-label for="password" :value="__('Password')" class="text-[#CAC4D0] text-xs font-bold uppercase tracking-widest" />
                            <x-text-input id="password" class="block mt-1 w-full p-4 bg-[#1C1B1F] border-[#49454F] text-[#E6E1E5] focus:ring-2 focus:ring-[#D0BCFF] focus:border-transparent rounded-xl transition-all"
                                            type="password"
                                            name="password"
                                            required autocomplete="current-password" placeholder="-----" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <!--
                        <div class="flex items-center ml-1">
                            <input id="remember_me" type="checkbox" class="w-5 h-5 rounded border-[#49454F] bg-[#1C1B1F] text-[#D0BCFF] focus:ring-[#D0BCFF] transition duration-150" name="remember">
                            <label for="remember_me" class="ms-3 text-xs font-bold text-[#CAC4D0] uppercase tracking-widest cursor-pointer select-none">{{ __('Keep me signed in') }}</label>
                        </div>
                        -->

                        <div class="pt-2 text-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Authenticate') }}
                            </button>
                        </div>

                        
                    @if (Route::has('password.request'))
                    <!--
                        <div class="pt-2">
                            <a class="text-[10px] font-bold text-[#D0BCFF] uppercase tracking-widest hover:underline" href="{{ route('password.request') }}">
                                {{ __('Recover') }}
                            </a>
                        </div>
                    -->
                    @endif
                    </form>
                @endauth
            </div>

            <div class="mt-12 text-center">
                <p class="text-[10px] font-bold text-[#49454F] uppercase tracking-[0.3em]">
                    &copy; {{ date('Y') }} Enterprise Systems
                </p>
            </div>
        </div>
    </body>
</html>
