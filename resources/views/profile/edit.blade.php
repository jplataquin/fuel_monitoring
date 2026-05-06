<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 fw-bold text-light mb-0">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container-xl">
            <div class="vstack gap-4">
                <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div style="max-width: 600px;">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div style="max-width: 600px;">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary border-opacity-25 rounded-4 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div style="max-width: 600px;">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
