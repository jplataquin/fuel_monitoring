<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>{{ config('app.name', 'Fuel Monitoring') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-dark text-light d-flex align-items-center justify-content-center min-vh-100 p-4">
        <div class="w-100" style="max-width: 400px;">
            <div class="d-flex flex-column align-items-center mb-5">
                <div class="rounded-4 d-flex align-items-center justify-content-center mb-4 shadow-lg" style="width: 80px; height: 80px; background-color: #D0BCFF;">
                    <x-application-logo class="fill-current" style="width: 48px; height: 48px; color: #381E72;" />
                </div>
                <h1 class="h2 fw-black text-light mb-0 tracking-tight">Fuel Monitoring</h1>
            </div>

            <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-lg overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    @auth
                        <div class="text-center py-4">
                            <p class="h5 text-light mb-4 fw-bold">Welcome back, <span style="color: #D0BCFF;">{{ Auth::user()->name }}</span></p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-sm" style="background-color: #D0BCFF !important; color: #1C1B1F !important; border: none !important;">
                                Go to Console
                            </a>
                        </div>
                    @else
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="vstack gap-4">
                            @csrf

                            <!-- Email Address -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                                <x-text-input id="email" class="w-100" style="padding: 0.75rem 1rem;" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="user@fleet.com" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div>
                                <x-input-label for="password" :value="__('Password')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                                <x-text-input id="password" class="w-100" style="padding: 0.75rem 1rem;" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="pt-2">
                                <x-primary-button class="w-100 py-3">
                                    {{ __('Authenticate') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @endauth
                </div>
            </div>

            <div class="mt-5 text-center">
                <p class="small fw-bold text-secondary text-uppercase tracking-widest opacity-50" style="font-size: 0.65rem;">
                    &copy; {{ date('Y') }} Enterprise Systems
                </p>
            </div>
        </div>
    </body>
</html>
