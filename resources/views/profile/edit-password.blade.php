<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 fw-bold text-light mb-0">
            {{ __('Change Password') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-sm overflow-hidden">
                <div class="card-body p-4 p-md-5 text-light">
                    <form method="POST" action="{{ route('profile.update_password') }}" style="max-width: 600px;">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="password" :value="__('New Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 w-100" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-100" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="d-flex align-items-center justify-content-end">
                            <x-primary-button>
                                {{ __('Update Password') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
