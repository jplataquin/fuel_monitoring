<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-light mb-0">
            {{ __('Add New Access') }}: {{ ucfirst($role) }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card bg-dark border-secondary shadow-lg rounded-4 p-4">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">

                    <div class="mb-4">
                        <label for="name" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('Full Name') }}</label>
                        <input id="name" name="name" type="text" class="form-control bg-dark text-light border-secondary p-3 rounded-3" value="{{ old('name') }}" required autofocus>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('Email Address') }}</label>
                        <input id="email" name="email" type="email" class="form-control bg-dark text-light border-secondary p-3 rounded-3 font-monospace" value="{{ old('email') }}" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold text-secondary text-uppercase tracking-widest">{{ __('Temporary Password') }}</label>
                        <input id="password" name="password" type="text" class="form-control bg-dark text-light border-secondary p-3 rounded-3 font-monospace" required placeholder="Generate a secure password">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="d-flex align-items-center justify-content-end pt-4 border-top border-secondary gap-3">
                        <a href="{{ route('users.index') }}" class="btn btn-link text-secondary text-decoration-none small fw-bold text-uppercase tracking-widest">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold small text-uppercase tracking-widest shadow-sm">
                            {{ __('CREATE') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
